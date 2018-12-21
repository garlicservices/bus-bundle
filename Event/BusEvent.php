<?php
namespace Garlic\Bus\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Universal Bus Event to handle multicast
 */
class BusEvent extends Event
{
    protected $payload;

    /**
     * BusEvent constructor.
     * @param $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get event payload
     *
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}