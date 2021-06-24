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
    public array $responseHeaders;

    /**
     * ServiceClientBaseRequestFailedException constructor.
     *
     * @param int $responseCode
     * @param string $responseBody
     * @param array $responseHeaders
     * @param string $endpoint
     * @param Throwable|null $previous
     */
    public function __construct(int $responseCode, string $responseBody, array $responseHeaders, string $endpoint, Throwable $previous = null)
    {
        $this->responseCode = $responseCode;
        $this->responseBody = $responseBody;
        $this->responseHeaders = $responseHeaders;

        $message = sprintf("%s: Failed request [%s] [%s]: \n %s", $this->getServiceName($endpoint), $responseCode, $endpoint, $responseBody);

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
