<?php

namespace MessageBusBundle\Service\Producer;

use MessageBusBundle\Entity\Response;
use MessageBusBundle\Service\Abstracts\RpcProducerAbstract;
use MessageBusBundle\Service\Interfaces\ProducerInterface;

/**
 * Class RequestProducer
 * @package MessageBusBundle\Producer\RequestProducer
 */
class CommandProducer extends RpcProducerAbstract implements ProducerInterface
{
    /**
     * Type of message
     */
    public static $type = 'command';

    /**
     * Sent request and get response from a service
     *
     * @param $message
     * @return object
     */
    public function send($message)
    {
        try {
            $this->sendCommand($this->getTargetServiceName(), $message, false);
            $message = 'Command has been sent';
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $code = $exception->getCode();
        }

        return new Response(
            json_encode(['message' => $message]),
            ($code) ?: 200
        );
    }
}