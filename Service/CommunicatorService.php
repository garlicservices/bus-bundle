<?php

namespace Garlic\Bus\Service;

use Garlic\Bus\Service\Interfaces\CommunicatorServiceInterface;
use Garlic\Bus\Service\Interfaces\ProducerInterface;
use Garlic\Bus\Service\Producer\CommandProducer;
use Garlic\Bus\Service\Producer\RequestProducer;
use Garlic\Bus\Service\Request\RequestService;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunicatorService implements CommunicatorServiceInterface
{
    const NAME_SPACE_SEPARATOR = '.';

    /** @var  RequestProducer */
    private $requestProducer;

    /** @var  RequestService */
    private $requestService;

    /** @var  ProducerInterface */
    private $producer;

    /** @var  RequestStack */
    private $requestStack;

    /** @var  string */
    private $namespace;

    /** @var  string */
    private $method = 'GET';

    /** @var CommandProducer */
    private $commandProducer;

    /**
     * CommunicatorService constructor.
     * @param RequestProducer $requestProducer
     * @param CommandProducer $commandProducer
     * @param RequestService $request
     * @param RequestStack $requestStack
     * @param $namespace
     */
    public function __construct(RequestProducer $requestProducer, CommandProducer $commandProducer, RequestService $request, RequestStack $requestStack, $namespace)
    {
        $this->requestProducer = $requestProducer;
        $this->requestService = $request;
        $this->requestStack = $requestStack;
        $this->namespace = $namespace;
        $this->commandProducer = $commandProducer;
    }

    /**
     * Call send function with route generated from method name
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return call_user_func_array(
            [$this, 'send'],
            array_merge(
                [($name != 'root')?$this->convertToPath($name):'/'],
                $arguments
            )
        );
    }

    /**
     * Create request producer to the service
     *
     * @param $service
     * @return $this
     */
    public function request($service)
    {
        $this->producer = $this->requestProducer->setTargetServiceName($service);

        return $this;
    }

    /**
     * Create request producer to the service
     *
     * @param $service
     * @return $this
     */
    public function command($service)
    {
        $this->producer = $this->commandProducer->setTargetServiceName($service);

        return $this;
    }

    /**
     * Send request (event/command)
     *
     * @param string $route
     * @param array $path
     * @param array $query
     * @param array $headers
     * @return mixed
     */
    public function send(
        string $route,
        array $path = [],
        array $query = [],
        array $headers = []
    ) {
        $request = $this->requestStack->getCurrentRequest();
        $headers = array_merge(null === $request ? [] : $request->headers->all(), $headers);

        $response = $this->producer->send(
            $this->requestService->create($route, $path, $query, $headers, $this->method)
        );

        $this->method = 'GET';

        return $response;
    }

    /**
     * Create post request
     *
     * @return $this
     */
    public function post()
    {
        $this->method = 'POST';

        return $this;
    }

    /**
     * Create put request
     *
     * @return $this
     */
    public function put()
    {
        $this->method = 'PUT';

        return $this;
    }

    /**
     * Create delete request
     *
     * @return $this
     */
    public function delete()
    {
        $this->method = 'DELETE';

        return $this;
    }

    /**
     * Convert string to path
     *
     * @param $input
     * @return string
     */
    private function convertToPath($input)
    {
        return '/'.strtolower(preg_replace('/(?<!^)[A-Z]/', '/$0', $input));
    }
}