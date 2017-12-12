<?php

namespace Garlic\Service\Processor;

use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrProcessor;
use Enqueue\Client\CommandSubscriberInterface;
use Garlic\Service\Abstracts\ProcessorConfigAbstract;

class CommandProcessor extends ProcessorConfigAbstract  implements PsrProcessor, CommandSubscriberInterface
{
    /**
     * Type of message
     */
    public static $type = 'command';

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $message, PsrContext $context)
    {
        $response = parent::process($message, $context);
        if (!$this->check($response->getStatusCode())) {
            return self::REJECT;
        }

        return self::ACK;
    }
}
