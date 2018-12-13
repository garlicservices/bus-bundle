<?php
namespace Garlic\Bus\Service\Processor;

use Enqueue\Client\TopicSubscriberInterface;
use Garlic\Bus\Event\BusEvent;
use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;
use Interop\Queue\PsrProcessor;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MulticastEventProcessor extends ProcessorConfigAbstract implements PsrProcessor, TopicSubscriberInterface
{
    protected $prefix = 'bus.';

    public static function getSubscribedTopics()
    {
        return ['multicast_event'];
    }

    /**
     * Emit bus multicast messages to symfony kernel instance
     *
     * @param PsrMessage $message
     * @param PsrContext $context
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function process(PsrMessage $message, PsrContext $context)
    {
        $container = $this->kernel->getContainer();

        $data = \json_decode($message->getBody());

        $eventName = $this->prefix . $data->uri;
        $payload = $data->path;

        $dispatcher = $container->get('event_dispatcher');
        $dispatcher->dispatch($eventName, new BusEvent($payload));

        return self::ACK;
    }
}