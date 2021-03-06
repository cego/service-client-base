<?php
namespace Cego\ServiceClientBase;

use InvalidArgumentException;
use Cego\RequestInsurance\Models\RequestInsurance;
use Cego\ServiceClientBase\RequestDrivers\Request;
use Cego\ServiceClientBase\RequestDrivers\Response;
use Cego\ServiceClientBase\RequestDrivers\RequestLog;
use Cego\ServiceClientBase\RequestDrivers\RequestDriver;
use Cego\ServiceClientBase\RequestDrivers\HttpRequestDriver;
use Cego\ServiceClientBase\Exceptions\InvalidHeaderException;
use Cego\ServiceClientBase\RequestDrivers\RequestInsuranceDriver;
use Cego\ServiceClientBase\Exceptions\ServiceRequestFailedException;
use Cego\ServiceClientBase\Exceptions\MissingSuggestedDependencyException;

abstract class AbstractServiceClient
{
    /**
     * The base url for the service
     *
     * Examples:
     * https://safeservice-prod.spilnu.dk
     * https://marketing-automation-prod.spilnu.dk
     *
     * @var string
     */
    protected string $serviceBaseUrl;

    /**
     * A attribute used to mark if request insurance should be used
     *
     * @var bool
     */
    protected bool $useRequestInsurance = false;

    /**
     * A list of global headers that will be applied to all requests
     *
     * @var array
     */
    protected array $globalHeaders = [
        'Content-type'  => 'application/json',
        'Accept'        => 'application/json',
    ];

    /**
     * A list of global options that will be applied to all requests
     *
     * @var array
     */
    protected array $globalOptions = [

    ];

    /**
     * A flag to mark if all requests and responses should be logged
     *
     * @var bool
     */
    protected bool $logRequests = false;

    /**
     * The request log.
     *
     * To enable the request log call ->enableRequestLog()
     *
     * @var RequestLog[]
     */
    protected array $requestLog = [];

    /**
     * Private constructor to disallow using new
     *
     * @param string $serviceBaseUrl
     */
    final protected function __construct(string $serviceBaseUrl)
    {
        $this->serviceBaseUrl = rtrim($serviceBaseUrl, '/');

        // Validate data
        if (empty($this->serviceBaseUrl)) {
            throw new InvalidArgumentException("serviceBaseUrl cannot be empty!");
        }
    }

    /**
     * Named constructor
     *
     * @param string $serviceBaseUrl
     *
     * @return static
     */
    public static function create(string $serviceBaseUrl): self
    {
        return new static($serviceBaseUrl);
    }

    /**
     * Applies the basic auth header to all requests
     *
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function auth(string $username, string $password): self
    {
        $auth = sprintf('%s:%s', $username, $password);

        $this->pushGlobalHeader('Authorization', sprintf('Basic %s', base64_encode($auth)));

        return $this;
    }

    /**
     * Enables or disables the use of request insurance.
     *
     * NOTE: Request insurance is only possible for POST requests.
     *
     * @param bool $useRequestInsurance
     *
     * @return $this
     *
     * @throws MissingSuggestedDependencyException
     */
    public function useRequestInsurance(bool $useRequestInsurance = true): self
    {
        if ($useRequestInsurance && $this->cannotUseRequestInsurance()) {
            throw new MissingSuggestedDependencyException('Request Insurance', 'cego/request-insurance');
        }

        $this->useRequestInsurance = $useRequestInsurance;

        return $this;
    }

    /**
     * Sets the timeout for all HTTP requests.
     *  - This does not affect request insurance
     *
     * @param int $timeoutInSeconds
     *
     * @return AbstractServiceClient
     */
    public function withTimeout(int $timeoutInSeconds): self
    {
        $this->globalOptions[HttpRequestDriver::OPTION_TIMEOUT] = $timeoutInSeconds;

        return $this;
    }

    /**
     * Reverts any previously set custom timeout, and reverts to whatever the default value is.
     */
    public function withDefaultTimeout(): self
    {
        unset($this->globalOptions[HttpRequestDriver::OPTION_TIMEOUT]);

        return $this;
    }

    /**
     * Push headers to the global headers array, which applies these headers to all requests made by the client.
     *
     * Either push a single header by:
     *  ->pushGlobalHeader('Header_Name', 'Header_value')
     *
     * Or push a list of headers by:
     *  ->pushGlobalHeader(['Header_Name1' => 'Header_value2', 'Header_Name1' => 'Header_value2'])
     *
     * @param string|string[] $header
     * @param string|null $value
     *
     * @return $this
     */
    public function pushGlobalHeader($header, $value = null): self
    {
        if (is_array($header)) {
            foreach ($header as $headerName => $headerValue) {
                $this->pushGlobalHeader($headerName, $headerValue);
            }

            return $this;
        }

        if ( ! is_string($header) || ! is_string($value)) {
            throw new InvalidHeaderException('Header name and value must be strings!');
        }

        $this->globalHeaders[$header] = $value;

        return $this;
    }

    /**
     * Pops headers from the global headers array.
     *
     * Either pop a single header by:
     *  ->popGlobalHeader('Header_Name')
     *
     * Or pop a list of headers by:
     *  ->popGlobalHeader(['Header_Name1', 'Header_Name1'])
     *
     * @param string|string[] $header
     *
     * @return $this
     */
    public function popGlobalHeader($header): self
    {
        if (is_array($header)) {
            foreach ($header as $headerItem) {
                $this->popGlobalHeader($headerItem);
            }

            return $this;
        }

        if ( ! is_string($header)) {
            throw new InvalidHeaderException('Header name must be of type string!');
        }

        unset($this->globalHeaders[$header]);

        return $this;
    }

    /**
     * Performs a GET request
     *
     * @param string $endpoint
     * @param array $queryParameters
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    protected function getRequest(string $endpoint, array $queryParameters = [], array $options = []): Response
    {
        return $this->makeRequest('get', $endpoint, $queryParameters, $options);
    }

    /**
     * Performs a POST request
     *
     * @param string $endpoint
     * @param array $data
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    protected function postRequest(string $endpoint, array $data = [], array $options = []): Response
    {
        return $this->makeRequest('post', $endpoint, $data, $options);
    }

    /**
     * Performs a PUT request
     *
     * @param string $endpoint
     * @param array $data
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    protected function putRequest(string $endpoint, array $data = [], array $options = []): Response
    {
        return $this->makeRequest('put', $endpoint, $data, $options);
    }

    /**
     * Performs a DELETE request
     *
     * @param string $endpoint
     * @param array $data
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    protected function deleteRequest(string $endpoint, array $data = [], array $options = []): Response
    {
        return $this->makeRequest('delete', $endpoint, $data, $options);
    }

    /**
     * Performs a PATCH request
     *
     * @param string $endpoint
     * @param array $data
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    protected function patchRequest(string $endpoint, array $data = [], array $options = []): Response
    {
        return $this->makeRequest('patch', $endpoint, $data, $options);
    }

    /**
     * Makes a request to the service
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $options = []): Response
    {
        $endpoint = $this->prependBaseUrl($endpoint);
        $options = array_merge($this->globalOptions, $options);

        try {
            $response = $this->getRequestDriver($method)
                ->makeRequest($method, $endpoint, $data, $this->globalHeaders, $options);
        } catch (ServiceRequestFailedException $exception) {
            $response = new Response($exception->responseCode, json_decode($exception->responseBody, true) ?? [], $exception->responseHeaders, true);

            throw $exception;
        } finally {
            /** @phpstan-ignore-next-line */
            if (isset($response)) {
                $this->logRequest(new Request($method, $endpoint, $data, $this->globalHeaders, $options), $response);
            }
        }

        return $response;
    }

    /**
     * Prepends the service base url to the endpoint
     *
     * @param string $endpoint
     *
     * @return string
     */
    protected function prependBaseUrl(string $endpoint): string
    {
        return sprintf('%s/%s', $this->serviceBaseUrl, ltrim($endpoint, '/'));
    }

    /**
     * Returns the request driver that should be used for sending requests from the client towards the service
     *
     * @param string $method
     *
     * @return RequestDriver
     */
    protected function getRequestDriver(string $method): RequestDriver
    {
        $isNotGetRequest = strcasecmp($method, 'get') != 0;

        return $isNotGetRequest && $this->shouldUseRequestInsurance()
            ? new RequestInsuranceDriver()
            : new HttpRequestDriver();
    }

    /**
     * Returns true if the client should use request insurance, and false otherwise
     *
     * @return bool
     */
    protected function shouldUseRequestInsurance(): bool
    {
        return $this->useRequestInsurance
            && $this->canUseRequestInsurance();
    }

    /**
     * Checks if it is possible to use request insurance or not.
     *
     * Basically it checks if the request insurance package is installed or not
     *
     * @return bool
     */
    protected function canUseRequestInsurance(): bool
    {
        return class_exists(RequestInsurance::class);
    }

    /**
     * Checks if it is possible to use request insurance or not.
     *
     * Basically it checks if the request insurance package is installed or not
     *
     * @return bool
     */
    protected function cannotUseRequestInsurance(): bool
    {
        return ! $this->canUseRequestInsurance();
    }

    /**
     * Used to enable / disable the request log
     *
     * @param bool $useRequestLog
     *
     * @return $this
     */
    public function useRequestLog($useRequestLog = true): self
    {
        $this->logRequests = $useRequestLog;

        return $this;
    }

    /**
     * Logs a request and response pair
     *
     * @param Request $request
     * @param Response $response
     */
    protected function logRequest(Request $request, Response $response): void
    {
        if ($this->logRequests) {
            $this->requestLog[] = new RequestLog($request, $response);
        }
    }

    /**
     * Clears the request log
     *
     * @return $this
     */
    public function clearRequestLog(): self
    {
        $this->requestLog = [];

        return $this;
    }

    /**
     * Returns the current request log
     *
     * @return RequestLog[]|RequestLog
     */
    public function getRequestLog(int $index = null)
    {
        if (is_numeric($index)) {
            return $this->requestLog[$index];
        }

        return $this->requestLog;
    }
}
