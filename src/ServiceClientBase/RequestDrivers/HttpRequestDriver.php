<?php

namespace Cego\ServiceClientBase\RequestDrivers;

use BadMethodCallException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response as HttpResponse;
use Cego\ServiceClientBase\Exceptions\ServiceRequestFailedException;

class HttpRequestDriver implements RequestDriver
{
    public const OPTION_TIMEOUT = 'http_timeout';

    /**
     * Makes a request to the service synchronously and returns the response
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @param array $options
     *
     * @return Response
     *
     * @throws ServiceRequestFailedException
     */
    public function makeRequest(string $method, string $endpoint, array $data = [], array $headers = [], array $options = []): Response
    {
        try {
            $response = $this->sendRequestWithRetries($method, $endpoint, $data, $headers, $options);

            return $this->transformResponse($response);
        } catch (RequestException $exception) {
            throw new ServiceRequestFailedException($exception->response->status(), $exception->response->body(), $exception->response->headers(), $endpoint, $exception);
        } catch (ConnectionException $exception) {
            throw new ServiceRequestFailedException(0, 'No Response: TIMEOUT', [], $endpoint, $exception);
        }
    }

    /**
     * Sends a synchronous http request with a number of retries for server errors
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @param array $options
     *
     * @return HttpResponse
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    protected function sendRequestWithRetries(string $method, string $endpoint, array $data, array $headers, array $options): HttpResponse
    {
        $retries = env("SERVICE_CLIENT_MAXIMUM_NUMBER_OF_RETRIES", 3);
        $timeout = env("SERVICE_CLIENT_TIMEOUT", 3);
        $delay = env("SERVICE_CLIENT_RETRY_DELAY", 100);

        for ($try = 0; $try < $retries; $try++) {
            try {
                /** @var HttpResponse $response */
                $response = Http::withHeaders(['User-Agent' => sprintf('ServiceClient/%s', class_basename($this))])
                    ->withHeaders($headers)
                    ->timeout($options[static::OPTION_TIMEOUT] ?? $timeout)
                    ->$method($endpoint, $data);
            } catch (ConnectionException $exception) {
                // If we have used up all of our retries, throw an exception
                if ($try == $retries - 1) {
                    throw $exception;
                }

                continue;
            }

            // If successful then return the response
            if ($response->successful()) {
                return $response;
            }

            // Do not retry client errors
            if ($response->clientError()) {
                $response->throw();
            }

            // If we have used up all of our retries, throw an exception
            if ($try == $retries - 1) {
                $response->throw();
            }

            // Sleep before retry
            usleep($delay * 1000);
        }

        throw new BadMethodCallException('Unexpected state, we should never reach this line. Either a request is successful or it should throw an exception.');
    }

    /**
     * Transforms a http response into the expected response class
     *
     * @param HttpResponse $httpResponse
     *
     * @return Response
     */
    protected function transformResponse(HttpResponse $httpResponse): Response
    {
        return new Response($httpResponse->status(), $httpResponse->json() ?? [], $httpResponse->headers(), true);
    }
}
