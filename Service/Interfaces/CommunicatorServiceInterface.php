<?php

namespace Garlic\Bus\Service\Interfaces;


interface CommunicatorServiceInterface
{
    /**
     * Create request producer to the service
     *
     * @param $service
     *
     * @return $this
     */
    public function request($service);

    /**
     * Send request (event/command)
     *
     * @param string $route
     * @param array $path
     * @param array $query
     * @param array $headers
     *
     * @return mixed
     */
    public function send(string $route, array $path = [], array $query = [], array $headers = []);

    /**
     * Create post request
     *
     * @return $this
     */
    public function post();

    /**
     * Create put request
     *
     * @return $this
     */
    public function put();

    /**
     * Create delete request
     *
     * @return $this
     */
    public function delete();

    /**
     * Add request to async pool
     *
     * @param string $service
     * @param string $route
     * @param array $path
     * @param array $query
     * @param array $headers
     * @return $this
     */
    public function pool(string $service, string $route, array $path = [], array $query = [], array $headers = []);
}