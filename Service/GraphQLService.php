<?php

namespace Garlic\Bus\Service;

use Dflydev\DotAccessData\Data;
use Enqueue\Rpc\Promise;
use Garlic\Bus\Service\GraphQL\Exceptions\GraphQLQueryException;
use Garlic\Bus\Service\GraphQL\Mutation\CreateMutationBuilder;
use Garlic\Bus\Service\GraphQL\Mutation\DeleteMutationBuilder;
use Garlic\Bus\Service\GraphQL\Mutation\MutationBuilder;
use Garlic\Bus\Service\GraphQL\Mutation\UpdateMutationBuilder;
use Garlic\Bus\Service\GraphQL\Query\QueryBuilder;
use Garlic\Bus\Service\GraphQL\QueryHelper;
use Garlic\Bus\Service\GraphQL\QueryRelation;
use Interop\Amqp\Impl\AmqpMessage;

class GraphQLService extends QueryHelper
{
    /** @var array */
    private $requests = [];
    
    /** @var CommunicatorService CommunicatorService */
    private $communicatorService;

    /** @var QueryPoolService QueryPoolService */
    private $queryPoolService;
    
    /**
     * GraphQLService constructor.
     * @param CommunicatorService $communicatorService
     * @param QueryPoolService $queryPoolService
     */
    public function __construct(
        CommunicatorService $communicatorService,
        QueryPoolService $queryPoolService
    ) {
        $this->communicatorService = $communicatorService;
        $this->queryPoolService = $queryPoolService;
    }
    
    /**
     * Create query builder
     *
     * @param string $from
     * @return QueryBuilder
     * @throws GraphQLQueryException
     */
    public function createQuery(string $from): QueryBuilder
    {
        $meta = $this->parsQueryName($from);
        $this->requests[$meta['service']][$meta['query']] = new QueryBuilder($meta['query']);
        
        return $this->requests[$meta['service']][$meta['query']];
    }
    
    /**
     * Create mutation builder
     *
     * @param string $from
     * @return MutationBuilder
     * @throws GraphQLQueryException
     */
    public function createMutation(string $from): MutationBuilder
    {
        $meta = $this->parsQueryName($from);
        $this->requests[$meta['service']][$meta['query']] = new MutationBuilder($meta['query']);
    
        return $this->requests[$meta['service']][$meta['query']];
    }
    
    /**
     * Create mutation builder for inserting new row
     *
     * @param string $from
     * @return CreateMutationBuilder
     * @throws GraphQLQueryException
     */
    public function createNewMutation(string $from): CreateMutationBuilder
    {
        $meta = $this->parsQueryName($from);
        $this->requests[$meta['service']][$meta['query']] = new CreateMutationBuilder($meta['query']);
    
        return $this->requests[$meta['service']][$meta['query']];
    }
    
    /**
     * Create mutation builder for updating rows
     *
     * @param string $from
     * @return UpdateMutationBuilder
     * @throws GraphQLQueryException
     */
    public function createUpdateMutation(string $from): UpdateMutationBuilder
    {
        $meta = $this->parsQueryName($from);
        $this->requests[$meta['service']][$meta['query']] = new UpdateMutationBuilder($meta['query']);
    
        return $this->requests[$meta['service']][$meta['query']];
    }
    
    /**
     * Create mutation builder for deleting rows
     *
     * @param string $from
     * @return DeleteMutationBuilder
     * @throws GraphQLQueryException
     */
    public function createDeleteMutation(string $from): DeleteMutationBuilder
    {
        $meta = $this->parsQueryName($from);
        $this->requests[$meta['service']][$meta['query']] = new DeleteMutationBuilder($meta['query']);
    
        return $this->requests[$meta['service']][$meta['query']];
    }

    /**
     * Sending queries into Bus and returning Promises for post-process
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function fetchAsync()
    {
        foreach ($this->requests as $serviceName => $request) {
            /** @var QueryBuilder $query */
            foreach ($request as $queryName => $query) {
                $service = $this->communicatorService->request($serviceName);

                $promise = $service->sendAsync('graphql', [], ['query' => (string)$query]);

                $this->queryPoolService->addAsyncQuery($query, $serviceName, $promise);
            }
        }

        $this->queryPoolService->resolve();

        return $this->stitchQueries();
    }
    
    /**
     * Execute queries and returns received data
     *
     * @return array
     */
    public function fetch(): array
    {
        foreach ($this->requests as $serviceName => $request) {
            $result = $this->communicatorService
                ->request($serviceName)
                /** @var CommunicatorService::__call('graphql'), ... */
                ->graphql([], ['query' => implode("\n", $request)])
                ->getData();
            
            /** @var QueryBuilder $query */
            foreach ($request as $queryName => $query) {
                $query->setResult((!empty($result['data'])) ? $result['data'][$queryName] : null);
            }
        }
        
        return $this->stitchQueries();
    }
    
    /**
     * Stitch queries to each other by stitch rules
     *
     * @return mixed
     */
    private function stitchQueries()
    {
        $result = [];
        foreach ($this->requests as $serviceName => $request) {
            /** @var QueryBuilder $query */
            foreach ($request as $queryName => $query) {
                if (count($query->getStitched()) > 0) {
                    $this->bindRelations($query);
                }
                
                $result[$serviceName]['data'][$queryName] = $query->getArrayResult();
            }
        }
        
        return $result;
    }
    
    /**
     * Bind relation value to the query
     *
     * @param QueryBuilder $query
     * @return mixed
     */
    private function bindRelations(QueryBuilder $query)
    {
        $queryDataResults = $query->getResult();
        $queryArrayResults = $query->getArrayResult();
        if($this->checkResultIsObject($queryArrayResults)) {
            $queryArrayResults = [$queryArrayResults];
        }
        
        /** @var QueryRelation $relation */
        foreach ($query->getStitched() as $relation) {
            $relationDataResults = $relation->getQuery()->getResult();
            $relationArrayResults = $relation->getQuery()->getArrayResult();
            if($this->checkResultIsObject($relationArrayResults)) {
                $relationArrayResults = [$relationArrayResults];
            }
            
            foreach ($queryArrayResults as $queryKey => $queryArrayResult) {
                $queryRelationValue = $queryDataResults->get($queryKey.'.'.$relation->getCurrent());

                foreach ($relationArrayResults as $relationKey => $relationArrayResult) {
                    $relationValue = $relationDataResults->get($relationKey.'.'.$relation->getTarget());
                    
                    $type = ($relation->getType() == QueryRelation::TYPE_ONE) ? "set": "append";
                    if($queryRelationValue == $relationValue) {
                        $queryDataResults->{$type}(
                            $queryKey.'.'.$relation->getAlias(),
                            $relationArrayResult
                        );
                    }
                }
                
            }
        }
    
        $query->setResult($queryDataResults->export());
        
        return $query->getResult()->export();
    }
}
