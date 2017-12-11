<?php

namespace MessageBusBundle\Service\Interfaces;


interface ProducerInterface
{
    /**
     * Send content to current service
     *
     * @param $content
     * @return mixed
     */
    public function send($content);
}