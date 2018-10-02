<?php

namespace Garlic\Bus\Service;

use Dflydev\DotAccessData\Data;
use Garlic\Bus\Service\GraphQL\Exceptions\GraphQLQueryException;
use Garlic\Bus\Service\GraphQL\QueryBuilder;
use Garlic\Bus\Service\GraphQL\QueryHelper;
use Garlic\Bus\Service\GraphQL\QueryRelation;

class GraphQLService extends QueryHelper
{
    /**
     * @var array
     */
    private $queries = [];
    
    /**
     * @var array
     */
    private $requests = [];
    
    /**
     * @var array
     */
    private $result = [];
    
    /**
     * @var CommunicatorService CommunicatorService
     */
    private $communicatorService;
    
    /**
     * GraphQLService constructor.
     * @param CommunicatorService $communicatorService
     */
    public function __construct(CommunicatorService $communicatorService)
    {
        $this->communicatorService = $communicatorService;
    }
    
    /**
     * Add query name that will be used on requested service
     *
     * @return QueryBuilder
     */
    public function createQuery(string $from): QueryBuilder
    {
        $meta = $this->parsQueryName($from);
        $this->requests[$meta['service']][$meta['query']] = new QueryBuilder($meta['query']);
        
        return $this->requests[$meta['service']][$meta['query']];
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
        if($this->ckeckResultIsObject($queryArrayResults)) {
            $queryArrayResults = [$queryArrayResults];
        }
        
        /** @var QueryRelation $relation */
        foreach ($query->getStitched() as $relation) {
            $relationDataResults = $relation->getQuery()->getResult();
            $relationArrayResults = $relation->getQuery()->getArrayResult();
            if($this->ckeckResultIsObject($relationArrayResults)) {
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
    
    /**
     * Check result is object
     *
     * @param $result
     * @return bool
     */
    private function ckeckResultIsObject($result)
    {
        return array_keys($result) !== range(0, count($result) - 1);
    }
}