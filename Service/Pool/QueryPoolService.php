<?php

namespace Garlic\Bus\Service\Pool;

use Enqueue\Rpc\Promise;
use Garlic\Bus\Entity\Response;
use Garlic\Bus\Service\Interfaces\CommunicatorServiceInterface;
use Interop\Amqp\Impl\AmqpMessage;

class QueryPoolService
{
    protected $promises = [];
    protected $services = [];
    protected $queryBuilders = [];
    /** @var Response[] */
    protected $queryResults = [];

    /**
     * Add query to queue
     *
     * @param string $serviceName
     * @param Promise $promise
     * @throws \ReflectionException
     */
    public function add(
        string $serviceName,
        Promise $promise
    ) {
        $correlationId = $this->getPromiseCorrelationId($promise);

        $this->promises[$correlationId] = $promise;
        $this->services[$correlationId] = $serviceName;
    }

    /**
     * Resolve queries from queue
     * @param CommunicatorServiceInterface $communicatorService
     * @return Response[]
     */
    public function resolve(CommunicatorServiceInterface $communicatorService)
    {
        foreach ($this->promises as $key => $promise) {
            /** @var AmqpMessage $result */
            try {
                $result = $promise->receive();

                $response = $communicatorService->request($this->services[$key])->getProducer()
                    ->getResponse()
                    ->hydrate($result->getBody());

                $this->queryResults[$this->services[$key]] = $response;
            } catch (\Exception $e) {
                $this->queryResults[$this->services[$key]] = null;
            }
        }

        return $this->queryResults;
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
