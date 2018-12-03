<?php

namespace Garlic\Bus\Service\Processor;

use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\PsrProcessor;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;

class ServiceDiscoveryProcessor implements PsrProcessor, TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return ['serviceDiscovery']; //, 'anotherTopic' and any other
    }

    /**
     * {@inheritdoc}
     * @todo:: implement
     */
    public function process(PsrMessage $message, PsrContext $context)
    {
        var_dump('ServiceDiscoveryProcessor->process');

        return self::ACK;
    }
}
