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
     * @param Throwable|null $previous
     */
    public function __construct(Response $response, Throwable $previous = null)
    {
        $message = sprintf("Seamless Wallet Service [%s]: \n %s", $response->status(), $response->body());

        parent::__construct($message, 500, $previous);
    }
}
