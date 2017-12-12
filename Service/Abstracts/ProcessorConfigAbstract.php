<?php

namespace Garlic\Service\Abstracts;

/**
 * Class CommandAbstract
 * @package MessageBusBundle\Service\Abstracts
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