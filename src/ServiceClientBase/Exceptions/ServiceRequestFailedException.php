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
    /** @var Response The failed service request response */
    protected Response $response;

    /**
     * ServiceClientBaseRequestFailedException constructor.
     *
     * @param Response $response
     * @param string $endpoint
     * @param Throwable|null $previous
     */
    public function __construct(Response $response, string $endpoint, Throwable $previous = null)
    {
        $this->response = $response;

        $message = sprintf("%s: Failed request [%s] [%s]: \n %s", $this->getServiceName($endpoint), $response->status(), $endpoint, $response->body());

        parent::__construct($message, 500, $previous);
    }

    /**
     * Transforms a
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function getServiceName(string $endpoint): string
    {
        $urlParts = parse_url($endpoint);

        if ($urlParts == false) {
            return '';
        }

        $serviceSubDomain = explode('.', $urlParts['host'])[0];

        // Converts:
        // seamless-wallet-stage => Seamless Wallet Stage
        return ucwords(str_replace('-', ' ', $serviceSubDomain));
    }

    /**
     * Get the failed response
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
