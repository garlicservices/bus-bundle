<?php

namespace Garlic\Service\Producer;

use Garlic\Service\Abstracts\RpcProducerAbstract;
use Garlic\Service\Interfaces\ProducerInterface;

/**
 * Class RequestProducer
 * @package MessageBusBundle\Producer\RequestProducer
 */
class RequestProducer extends RpcProducerAbstract implements ProducerInterface
{
    /**
     * Type of message
     */
    public static $type = 'request';

    /**
     * Sent request and get response from a service
     *
     * @param $message
     * @return object
     */
    public function send($message)
    {
        return $this->response->hydrate(
            $this->sendCommand($this->getTargetServiceName(), $message, true)
                ->receive()
                ->getBody()
        );
    }
}