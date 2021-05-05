<?php

namespace Cego\ServiceClientBase\RequestDrivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
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
            /** @var HttpResponse $response */
            $response = Http::withHeaders(['User-Agent' => sprintf('ServiceClient/%s', static::class)])
                            ->withHeaders($headers)
                            ->timeout($options[static::OPTION_TIMEOUT] ?? env("SERVICE_CLIENT_TIMEOUT", 3))
                            ->retry(env("SERVICE_CLIENT_MAXIMUM_NUMBER_OF_RETRIES", 3), env("SERVICE_CLIENT_RETRY_DELAY", 100))
                            ->$method($endpoint, $data);

            return $this->transformResponse($response);
        } catch (RequestException $exception) {
            throw new ServiceRequestFailedException($exception->response, $endpoint);
        }
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
        return new Response($httpResponse->status(), $httpResponse->json() ?? [], true);
    }
}
