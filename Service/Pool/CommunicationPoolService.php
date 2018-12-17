<?php

namespace Garlic\Bus\Service\Pool;

use Garlic\Bus\Service\CommunicatorService;
use Garlic\Bus\Service\Request\RequestService;

class CommunicationPoolService
{
    private $pool = [];
    /** @var QueryPoolService QueryPoolService */
    private $queryPoolService;

    /** @var CommunicatorService */
    private $communicatorService;

    /** @var RequestService */
    private $requestService;

    /**
     * CommunicationPoolService constructor.
     * @param QueryPoolService $queryPoolService
     * @param CommunicatorService $communicatorService
     * @param RequestService $requestService
     */
    public function __construct(QueryPoolService $queryPoolService, CommunicatorService $communicatorService, RequestService $requestService)
    {
        $this->queryPoolService = $queryPoolService;
        $this->communicatorService = $communicatorService;
        $this->requestService = $requestService;
    }

    /**
     * @param string $service
     * @param string $route
     * @param array $path
     * @param array $query
     * @param array $headers
     */
    public function send(
        string $service,
        string $route,
        array $path = [],
        array $query = [],
        array $headers = []
    ) {
        $request = $this->requestService->getCurrentRequest();
        $headers = array_merge(null === $request ? [] : $request->headers->all(), $headers);

        $this->communicatorService->request($service);
        $promise = $this->communicatorService->getProducer()->getPromise(
            $this->requestService->create(
                ($route != 'root') ? $this->convertToPath($route) : '/',
                $path,
                $query,
                $headers,
                $this->method
            )
        );

        $this->pool[$service] = $promise;
    }

    /**
     * Fetch response data from query pool promises
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function fetch()
    {
        foreach ($this->pool as $serviceName => $promise) {
            $this->queryPoolService->addAsyncQuery($serviceName, $promise);
        }

        return $this->queryPoolService->resolve();
    }

}