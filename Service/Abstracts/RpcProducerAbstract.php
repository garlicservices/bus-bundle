<?php

namespace Garlic\Bus\Service\Abstracts;

/**
 * Class ProducerAbstract
 * @package GarlicBusBundle\Service\Abstracts
 */

use Enqueue\Client\Config;
use Enqueue\Client\Message;
use Enqueue\Client\Producer;
use Enqueue\Rpc\RpcFactory;
use Garlic\Bus\Service\Request\ResponseService;
use Enqueue\Util\UUID;
use Interop\Amqp\Impl\AmqpMessage;
use Interop\Amqp\Impl\AmqpTopic;
use Interop\Queue\Context;
use Symfony\Component\DependencyInjection\Container;

abstract class RpcProducerAbstract extends ProducerAbstract
{
    /** @var Context\ */
    protected $context;

    /** @var RpcFactory */
    protected $rpcFactory;

    /** @var  ResponseService */
    protected $response;

    /** @var int */
    protected $timeout;

    /**
     * RequestProducer constructor.
     * @param Context $context
     * @param RpcFactory $promiseFactory
     * @param ResponseService $response
     * @param string $serviceName
     * @param string $nameSpace
     * @param int $timeout
     */
    public function __construct(
        Context $context,
        RpcFactory $promiseFactory,
        ResponseService $response,
        string $serviceName,
        string $nameSpace,
        int $timeout = 5000
    ) {
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
        $message->setProperty(Config::PROCESSOR, $reply ? 'service.request.processor' : 'service.command.processor');

        $queue = $this->context->createQueue($service);

        $deleteReplyQueue = false;
        $replyTo = $message->getReplyTo();

        if ($reply) {
            if (false == $replyTo) {
                $message->setReplyTo($replyTo = $this->rpcFactory->createReplyTo());
                $deleteReplyQueue = true;
            }

            if (false == $message->getCorrelationId()) {
                $message->setCorrelationId(UUID::generate());
            }
        }

        $this->context->createProducer()->send($queue, $message);

        if ($reply) {
            $promise = $this->rpcFactory->createPromise($replyTo, $message->getCorrelationId(), $this->timeout);
            $promise->setDeleteReplyQueue($deleteReplyQueue);

            return $promise;
        }

        return false;
    }

    /**
     * Send multicast event
     *
     * @param $name
     * @param AmqpMessage $message
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\InvalidDestinationException
     * @throws \Interop\Queue\InvalidMessageException
     */
    public function sendEvent($name, $message)
    {
        if (false == $message instanceof Message) {
            $message = $this->context->createMessage($message);
        }

        $message->setProperty(Config::TOPIC, $name);
        $message->setProperty(Config::PROCESSOR, 'service.event.processor');

        $topic = $this->context->createTopic('enqueue.default');
        $topic->setType(AmqpTopic::TYPE_FANOUT);
        $topic->addFlag(AmqpTopic::FLAG_DURABLE);

        $this->context->createProducer()->send($topic, $message);
    }
}
