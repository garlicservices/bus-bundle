<?php

namespace Garlic\Bus\Service\Abstracts;

/**
 * Class CommandAbstract
 * @package GarlicBusBundle\Service\Abstracts
 */
abstract class ProcessorConfigAbstract extends ProcessorAbstract
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedCommand()
    {
        $name = ProducerAbstract::generateServiceName(
            getenv('SERVICE_NAME'),
            getenv('SERVICE_NAMESPACE'),
            static::$type
        );

        return [
            'processorName' => $name,
            'queueName' => $name,
            'queueNameHardcoded' => true,
            'exclusive' => true,
        ];
    }
}