<?php

namespace Garlic\Bus\Service\Processor;

use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Enqueue\Client\CommandSubscriberInterface;
use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;

class CommandProcessor extends ProcessorConfigAbstract  implements Processor, CommandSubscriberInterface
{
    /**
     * Type of message
     */
    public static $type = 'command';

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, Context $context)
    {
        $response = parent::process($message, $context);
        if (!$this->check($response->getStatusCode())) {
            return self::REJECT;
        }

        return self::ACK;
    }
}
