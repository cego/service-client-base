<?php

namespace Cego\ServiceClientBase\Exceptions;

use Exception;
use Throwable;
use Illuminate\Http\Client\Response;

/**
 * Class ServiceClientBaseRequestFailedException
 */
class ServiceRequestFailedException extends Exception
{
    /**
     * ServiceClientBaseRequestFailedException constructor.
     *
     * @param Response $response
     * @param string $endpoint
     * @param Throwable|null $previous
     */
    public function __construct(Response $response, string $endpoint, Throwable $previous = null)
    {
        $message = sprintf("Failed Service request [%s] [%s]: \n %s", $response->status(), $endpoint, $response->body());

        parent::__construct($message, 500, $previous);
    }
}
