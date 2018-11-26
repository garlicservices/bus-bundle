<?php

namespace Garlic\Bus\Service;

use Enqueue\Rpc\Promise;
use Garlic\Bus\Service\GraphQL\AbstractQueryBuilder;
use Interop\Amqp\Impl\AmqpMessage;

class QueryPoolService
{
    protected $promises = [];
    protected $services = [];
    protected $queryBuilders = [];

    /** @var CommunicatorService CommunicatorService */
    private $communicatorService;

    public function __construct(CommunicatorService $communicatorService)
    {
        $this->communicatorService = $communicatorService;
    }

    /**
     * Add query to queue
     *
     * @param AbstractQueryBuilder $queryBuilder
     * @param string $serviceName
     * @param Promise $promise
     * @throws \ReflectionException
     */
    public function addAsyncQuery(
        AbstractQueryBuilder $queryBuilder,
        string $serviceName,
        Promise $promise
    ) {
        $correlationId = $this->getPromiseCorrelationId($promise);

        $this->promises[$correlationId] = $promise;
        $this->services[$correlationId] = $serviceName;
        $this->queryBuilders[$correlationId] = $queryBuilder;
    }

    /**
     * Resolve queries from queue
     */
    public function resolve()
    {
        foreach ($this->promises as $key => $promise) {
            /** @var AmqpMessage $result */
            $result = $promise->receive();
            $correlationId = $result->getHeader('correlation_id');

            $response = $this->communicatorService->request($this->services[$correlationId])->getProducer()
                ->getResponse()
                ->hydrate($result->getBody())
                ->getData();

            $this->queryBuilders[$correlationId]->setResult(
                (!empty($response['data'])) ? $response['data'][$this->queryBuilders[$correlationId]->getQueryName()] : null
            );
        }
    }

    /**
     * Fetching enqueue correlationId from provided Promise
     *
     * @param Promise $promise
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getPromiseCorrelationId(Promise $promise)
    {
        $reflectionClass = new \ReflectionClass($promise);
        $reflectionProperty = $reflectionClass->getProperty('receiveCallback');
        $reflectionProperty->setAccessible(true);
        $reflect = new \ReflectionFunction($reflectionProperty->getValue($promise));

        return $reflect->getStaticVariables()['correlationId'];
    }
}
