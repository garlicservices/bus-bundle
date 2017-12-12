<?php

namespace Garlic\Bus\Service\Processor;

use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;
use Interop\Queue\PsrProcessor;
use Interop\Queue\PsrMessage;
use Interop\Queue\PsrContext;
use Enqueue\Consumption\Result;
use Enqueue\Client\CommandSubscriberInterface;


class RequestProcessor extends ProcessorConfigAbstract implements PsrProcessor, CommandSubscriberInterface
{
    /**
     * Type of message
     */
    public static $type = 'request';

    /**
     * {@inheritdoc}
     */
    public function process(PsrMessage $message, PsrContext $context)
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
