<?php

namespace Cego\ServiceClientBase\RequestDrivers;

/**
 * Class Request
 *
 * @package Cego\ServiceClientBase\RequestDrivers
 */
class Request
{
    protected string $method;
    protected string $endpoint;
    protected array $data;
    protected array $headers;
    protected array $options;

    public function __construct(string $method, string $endpoint, array $data = [], array $headers = [], array $options = [])
    {
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->data = $data;
        $this->headers = $headers;
        $this->options = $options;
    }

    /**
     * Returns the request verb
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the request endpoint
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Returns the request payload
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the request headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns any request options used
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
