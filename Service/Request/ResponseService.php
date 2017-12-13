<?php

namespace Garlic\Bus\Service\Request;

use Garlic\Bus\Entity\Request;
use Garlic\Bus\Entity\Response;
use JMS\Serializer\Serializer;

class ResponseService
{
    /** @var Serializer $serializer */
    private $serializer;

    /**
     * RequestService constructor.
     * @param $serializer
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Create Response JSON
     *
     * @param $content
     * @param int $code
     * @param array $headers
     * @param null $charset
     * @return Request
     */
    public function create($content, $code = 200, $headers = [], $charset = null)
    {
        return $this->serializer->serialize(
            new Response($content, $code, $headers, $charset),
            'json'
        );
    }

    /**
     * Dehydrate content to Response
     *
     * @param $content
     * @return object
     */
    public function hydrate($content)
    {
        return $this->serializer->deserialize(
            $content,
            'GarlicBusBundle\Entity\Response',
            'json'
        );
    }
}