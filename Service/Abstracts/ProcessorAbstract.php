<?php

namespace Garlic\Bus\Service\Abstracts;

use App\Kernel;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Garlic\Bus\Entity\Request as Data;
use Symfony\Component\Routing\Router;
use Garlic\Bus\Service\Request\RequestService;
use Garlic\Bus\Service\Request\ResponseService;

/**
 * Class ProcessorAbstract
 * @package GarlicBusBundle\Service\Abstracts
 */
abstract class ProcessorAbstract
{
    /** @var  Router */
    protected $router;

    /** @var  RequestService */
    protected $request;

    /** @var  ResponseService */
    protected $response;

    /** @var Kernel */
    protected $kernel;

    /** @var LoggerInterface */
    protected $logger;

    /** @var  array */
    protected static $parameters;

    /**
     * Important variable to figure out type of message
     *
     * @var string
     */
    public static $type;

    /**
     * RequestProcessor constructor.
     * @param RequestService $request
     * @param ResponseService $response
     * @param Router $router
     */
    public function __construct(
        RequestService $request,
        ResponseService $response,
        Router $router,
        Kernel $kernel,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->router = $router;
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $message, PsrContext $context)
    {
        /** @var Data $data */
        $data = $this->request->hydrate($message->getBody());

        try {
            $response = $this->run($data);

            if ($response->getStatusCode() >= 400) {
                $this->logger->error($response->getContent());
            }
        } catch (ResourceNotFoundException $exception) {
            $response = new Response(
                'Route not found',
                Response::HTTP_NOT_FOUND
            );
        } catch (MethodNotAllowedException $exception) {
            $response = new Response(
                'Method not allowed',
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        } catch (\Exception $exception) {
            $response = new Response(
                ($exception->getMessage()) ? $exception->getMessage() : 'Internal server error',
                ($exception->getCode()) ? $exception->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $response;
    }

    /**
     * Execute request in Kernel
     *
     * @param Data $data
     * @return Response
     */
    protected function run(Data $data)
    {
        $this->router->getContext()->setMethod($data->getMethod());
        $fullUri = $data->getFullUri();

        try {
            $route = $this->router->match($fullUri);
        } catch (ResourceNotFoundException $exception) {
            $route['_route'] = $fullUri;
        }

        if (!empty($route['path'])) {
            $route = $this->router->match($route['path']);
        }

        $kernel = clone $this->kernel;
        $request = $this->request($data, $route);
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        unset(
            $kernel,
            $request,
            $route
        );

        return $response;
    }

    /**
     * Create request
     *
     * @param Data $data
     * @param $route
     * @return Request
     */
    protected function request(Data $data, $route)
    {
        $server = [];
        if (!empty($route['_route'])) {
            $server['REQUEST_URI'] =  $route['_route'];
        }

        $request = new Request(
            $data->getPath(),
            $data->getQuery(),
            $route,
            [],
            [],
            $server
        );

        $request->headers->replace($data->getHeaders());
        $request->setMethod($data->getMethod());

        return $request;
    }

    /**
     * Create response message
     *
     * @param Response $response
     * @return Data
     */
    protected function response(Response $response)
    {
        return $this->response->create(
            (string)$response->getContent(),
            $response->getStatusCode(),
            (array)$response->headers->all()
        );
    }

    /**
     * Check for success status
     *
     * @param int $status
     * @return bool
     */
    protected function check($status)
    {
        if (($status >= Response::HTTP_OK) && ($status < Response::HTTP_MULTIPLE_CHOICES)) {
            return true;
        }

        return false;
    }
}