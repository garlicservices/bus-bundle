<?php

namespace MessageBusBundle\Entity;

use JMS\Serializer\Annotation\Type;

/**
 * Request format for communication bundle
 *
 * Class Request
 * @package MessageBusBundle\Entity
 */
class Request
{
    const URI_GLUE = '/';

    /**
     * @Type("string")
     */
    private $uri;

    /**
     * @Type("string")
     */
    private $method = 'GET';

    /**
     * @Type("array")
     */
    private $path = [];

    /**
     * @Type("array")
     */
    private $query = [];

    /**
     * @Type("array")
     */
    private $headers = [];

    /**
     * Request constructor.
     * @param string $uri
     * @param string $method
     * @param array $path
     * @param array $query
     * @param array $headers
     */
    public function __construct(string $uri, string $method, array $path, array $query, array $headers)
    {
        $this->uri = $uri;
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
        $this->headers = $headers;
    }

    /**
     * Get URI with path
     *
     * @return string
     */
    public function getFullUri()
    {
        return implode(
            self::URI_GLUE,
            array_merge(
                [$this->uri],
                $this->path
            )
        );
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Set uri
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get method
     *
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set method
     *
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }


    /**
     * Get path
     *
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * Set path
     *
     * @param array $path
     */
    public function setPath(array $path)
    {
        $this->path = $path;
    }

    /**
     * Get query
     *
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * Set query
     *
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    /**
     * Get headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set headers
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

}