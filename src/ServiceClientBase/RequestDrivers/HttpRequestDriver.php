<?php

namespace Cego\ServiceClientBase\RequestDrivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response as HttpResponse;
use Cego\ServiceClientBase\Exceptions\ServiceRequestFailedException;

class HttpRequestDriver implements RequestDriver
{
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
        $maxTries = env("SEAMLESS_WALLET_CLIENT_MAXIMUM_NUMBER_OF_RETRIES", 3);
        $try = 0;

        do {
            /** @var HttpResponse $response */
            $response = Http::withHeaders($headers)
                ->asJson()
                ->acceptJson()
                ->timeout(env("SEAMLESS_WALLET_CLIENT_TIMEOUT", 1))
                ->$method($endpoint, $data);

            // Bailout if successful
            if ($response->successful()) {
                return $this->transformResponse($response);
            }

            // Do not retry client errors
            if ($response->clientError()) {
                throw new ServiceRequestFailedException($response);
            }

            // Wait 1 sec before trying again, if server error
            usleep(env("SEAMLESS_WALLET_CLIENT_RETRY_DELAY", 1000000));
            $try++;
        } while ($try < $maxTries);

        throw new ServiceRequestFailedException($response);
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
        return new Response($httpResponse->status(), $httpResponse->json(), true);
    }
}
