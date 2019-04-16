<?php
namespace Garlic\Bus\Service\Processor;

use Enqueue\Client\TopicSubscriberInterface;
use Garlic\Bus\Event\BusEvent;
use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;

class MulticastEventProcessor extends ProcessorConfigAbstract implements Processor
{
    protected $prefix = 'bus.';

    /**
     * Emit bus multicast messages to symfony kernel instance
     *
     * @param Message $message
     * @param Context $context
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function process(Message $message, Context $context)
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