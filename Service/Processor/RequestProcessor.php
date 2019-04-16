<?php

namespace Garlic\Bus\Service\Processor;

use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Enqueue\Consumption\Result;
use Enqueue\Client\CommandSubscriberInterface;


class RequestProcessor extends ProcessorConfigAbstract implements Processor, CommandSubscriberInterface
{
    /**
     * Type of message
     */
    public static $type = 'request';

    /**
     * {@inheritdoc}
     */
    public function process(Message $message, Context $context)
    {
        return Result::reply(
            $context->createMessage(
                $this->response(
                    parent::process($message, $context)
                )
            )
        );
    }
}
