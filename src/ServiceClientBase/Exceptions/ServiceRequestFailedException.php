<?php

namespace Cego\ServiceClientBase\Exceptions;

use Exception;
use Throwable;

/**
 * Class ServiceClientBaseRequestFailedException
 */
class ServiceRequestFailedException extends Exception
{
    public int $responseCode;
    public string $responseBody;

    /**
     * ServiceClientBaseRequestFailedException constructor.
     *
     * @param int $code
     * @param string $body
     * @param string $endpoint
     * @param Throwable|null $previous
     */
    public function __construct(int $code, string $body, string $endpoint, Throwable $previous = null)
    {
        $this->responseCode = $code;
        $this->responseBody = $body;

        $message = sprintf("%s: Failed request [%s] [%s]: \n %s", $this->getServiceName($endpoint), $code, $endpoint, $body);

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
}
