<?php

namespace Cego\ServiceClientBase\RequestDrivers;

/**
 * Class Request
 *
 * @package Cego\ServiceClientBase\RequestDrivers
 */
class RequestLog
{
    protected Request $request;
    protected Response $response;

    /**
     * RequestLog constructor.
     *
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Returns the Request instance
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the Response instance
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
