<?php

namespace Cego\ServiceClientBase\RequestDrivers;

use JsonException;
use Cego\RequestInsurance\Models\RequestInsurance;
use Illuminate\Contracts\Container\BindingResolutionException;

class RequestInsuranceDriver implements RequestDriver
{
    public const OPTION_PRIORITY = 'priority';
    public const OPTION_RETRY_COUNT = 'retry_count';
    public const OPTION_RETRY_FACTOR = 'retry_factor';
    public const OPTION_RETRY_CAP = 'retry_cap';

    protected const OPTIONS = [
        self::OPTION_PRIORITY,
        self::OPTION_RETRY_COUNT,
        self::OPTION_RETRY_FACTOR,
        self::OPTION_RETRY_CAP,
    ];

    /**
     * Makes a request to the service asynchronously
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @param array $options
     *
     * @return Response
     *
     * @throws JsonException
     * @throws BindingResolutionException
     */
    public function makeRequest(string $method, string $endpoint, array $data = [], array $headers = [], array $options = []): Response
    {
        $headers = array_merge(['User-Agent' => sprintf('ServiceClient/%s', class_basename($this))], $headers);

        /** @var RequestInsurance $requestInsurance */
        $requestInsurance = app()->make(RequestInsurance::class);

        /** @phpstan-ignore-next-line Its a magic method from Laravel models */
        $requestInsurance::create($this->getRequestData($method, $endpoint, $data, $headers, $options));

        return new Response(0, [], false);
    }

    /**
     * Returns the request insurance data array
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @param array $options
     *
     * @return array
     *
     * @throws JsonException
     */
    protected function getRequestData(string $method, string $endpoint, array $data, array $headers, array $options): array
    {
        // Required fields
        $request = [
            'method'  => $method,
            'url'     => $endpoint,
            'payload' => json_encode($data, JSON_THROW_ON_ERROR),
            'headers' => json_encode($headers, JSON_THROW_ON_ERROR),
        ];

        // Add options
        foreach (static::OPTIONS as $option) {
            if (isset($options[$option])) {
                $request[$option] = $options[$option];
            }
        }

        return $request;
    }
}
