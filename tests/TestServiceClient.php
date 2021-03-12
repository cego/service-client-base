<?php

namespace Tests;

use Cego\ServiceClientBase\AbstractServiceClient;
use Cego\ServiceClientBase\RequestDrivers\Response;
use Cego\ServiceClientBase\Exceptions\ServiceRequestFailedException;

class TestServiceClient extends AbstractServiceClient
{
    /**
     * Test GET request method for a service client
     *
     * @param string $endpoint
     * @param array $queryParameters
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    public function testGetRequest(string $endpoint, array $queryParameters = [], array $options = []): Response
    {
        return $this->getRequest($endpoint, $queryParameters, $options);
    }

    /**
     * Test POST request method for a service client
     *
     * @param string $endpoint
     * @param array $data
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    public function testPostRequest(string $endpoint, array $data = [], array $options = []): Response
    {
        return $this->postRequest($endpoint, $data, $options);
    }

    /**
     * Test PUT request method for a service client
     *
     * @param string $endpoint
     * @param array $data
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    public function testPutRequest(string $endpoint, array $data = [], array $options = []): Response
    {
        return $this->putRequest($endpoint, $data, $options);
    }
}
