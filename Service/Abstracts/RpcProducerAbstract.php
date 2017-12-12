<?php

namespace Garlic\Service\Abstracts;

/**
 * Class ProducerAbstract
 * @package MessageBusBundle\Service\Abstracts
 */

use Enqueue\Client\Config;
use Enqueue\Client\Message;
use Enqueue\Client\Producer;
use Enqueue\Rpc\RpcFactory;
use Garlic\Service\Request\ResponseService;
use Enqueue\Util\UUID;
use Interop\Queue\PsrContext;

abstract class RpcProducerAbstract extends ProducerAbstract
{
    /** @var PsrContext */
    protected $context;

    /** @var RpcFactory */
    protected $rpcFactory;

    /** @var  ResponseService */
    protected $response;

    /** @var int */
    protected $timeout;

    /**
     * RequestProducer constructor.
     * @param PsrContext $context
     * @param RpcFactory $promiseFactory
     * @param ResponseService $response
     * @param string $serviceName
     * @param string $nameSpace
     * @param int $timeout
     */
    public function __construct(PsrContext $context, RpcFactory $promiseFactory, ResponseService $response, string $serviceName, string $nameSpace, int $timeout = 5000)
    {
        $this->context = $context;
        $this->rpcFactory = $promiseFactory;
        $this->response = $response;
        $this->timeout = $timeout;

        parent::__construct($serviceName, $nameSpace);
    }

    /**
     * Send a message
     *
     * @param string $service
     * @param string $message
     * @param bool $reply
     * @return bool|\Enqueue\Rpc\Promise
     */
    public function sendCommand(string $service, $message, bool $reply = true)
    {
        if (false == $message instanceof Message) {
            $message = $this->context->createMessage($message);
        }

        $queue = $this->context->createQueue($service);

        $deleteReplyQueue = false;
        $replyTo = $message->getReplyTo();

        if($reply) {
            if (false == $replyTo) {
                $message->setReplyTo($replyTo = $this->rpcFactory->createReplyTo());
                $deleteReplyQueue = true;
            }

            if (false == $message->getCorrelationId()) {
                $message->setCorrelationId(UUID::generate());
            }
        }

        $this->context->createProducer()->send($queue, $message);

        if($reply) {
            $promise = $this->rpcFactory->createPromise($replyTo, $message->getCorrelationId(), $this->timeout);
            $promise->setDeleteReplyQueue($deleteReplyQueue);

            return $promise;
        }

        return false;
    }
}
