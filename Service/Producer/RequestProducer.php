<?php

namespace Garlic\Bus\Service\Producer;

use Garlic\Bus\Service\Abstracts\RpcProducerAbstract;
use Garlic\Bus\Service\Interfaces\ProducerInterface;

/**
 * Class RequestProducer
 * @package GarlicBusBundle\Producer\RequestProducer
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