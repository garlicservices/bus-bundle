<?php

namespace Garlic\Bus\Service\Producer;

use Garlic\Bus\Entity\Response;
use Garlic\Bus\Service\Abstracts\RpcProducerAbstract;
use Garlic\Bus\Service\Interfaces\ProducerInterface;

/**
 * Class RequestProducer
 * @package GarlicBusBundle\Producer\RequestProducer
 */
class EventProducer extends RpcProducerAbstract implements ProducerInterface
{
    /**
     * Type of message
     */
    public static $type = 'event';

    /**
     * Sent request and get response from a service
     *
     * @param $message
     * @return object
     */
    public function send($message)
    {
        try {
            $code = 200;
            $this->sendEvent($this->targetServiceName, $message);
            $message = 'Event has been sent';
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $code = $exception->getCode();
        }

        return new Response(
            json_encode(['message' => $message]),
            (!empty($code)) ? $code : 500
        );
    }
}