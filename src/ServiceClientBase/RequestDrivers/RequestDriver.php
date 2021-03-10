<?php

namespace Cego\ServiceClientBase\RequestDrivers;

use Cego\ServiceClientBase\Exceptions\ServiceRequestFailedException;

interface RequestDriver
{
    /**
     * Makes a request to the service
     *
     * @param string $method get|post
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    public function makeRequest(string $method, string $endpoint, array $data = [], array $headers = [], array $options = []): Response;
}
