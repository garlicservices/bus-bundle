<?php

namespace Garlic\Entity;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Response format for communication bundle
 *
 * Class Request
 * @package MessageBusBundle\Entity
 */
class Response
{
    /**
     * @Type("string")
     */
    private $content;

    /**
     * @Type("integer")
     */
    private $code;

    /**
     * @Type("array")
     */
    private $headers = [];

    /**
     * @Type("string")
     */
    private $charset;

    /**
     * Request constructor.
     * @param string $content
     * @param string $code
     * @param array $headers
     * @param $charset
     */
    public function __construct(string $content, string $code, array $headers = [], $charset = null)
    {
        $this->content = $content;
        $this->code = $code;
        $this->headers = $headers;
        $this->charset = $charset;
    }

    /**
     * Get content
     *
     * @return mixed
     */
    public function getData()
    {
        return json_decode($this->content, true);
    }

    /**
     * Return JsonResponse object
     *
     * @return JsonResponse
     */
    public function getJsonResponse()
    {
        return new JsonResponse(
            $this->getData(),
            $this->getCode(),
            $this->getHeaders()
        );
    }

    /**
     * Get content
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param mixed $content
     * @return Response
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get code
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param mixed $code
     * @return Response
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get headers
     *
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set headers
     *
     * @param mixed $headers
     * @return Response
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }
}
