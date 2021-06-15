<?php

namespace Cego\ServiceClientBase\RequestDrivers;

use ArrayAccess;
use Illuminate\Support\Collection;

/**
 * Class Response
 *
 * @implements ArrayAccess<string, mixed>
 *
 * @package Cego\ServiceClientBase\RequestDrivers
 */
class Response implements ArrayAccess
{
    public int $code;
    public Collection $data;
    public Collection $headers;
    public bool $isSynchronous;

    /**
     * Response constructor.
     *
     * @param int $code
     * @param array $data
     * @param array $headers
     * @param bool $isSynchronous
     */
    public function __construct(int $code, array $data, array $headers, bool $isSynchronous)
    {
        $this->code = $code;
        $this->data = new Collection($data);
        $this->headers = (new Collection($headers))->keyBy(fn ($value, $key) => strtolower($key)); // Lowercase all headers
        $this->isSynchronous = $isSynchronous;
    }

    /**
     * Returns a single data entry point, with support for dot notation for nested levels of access.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function data(string $key)
    {
        $lookupMap = explode('.', $key);

        $data = $this->data;

        foreach ($lookupMap as $nextKey) {
            $data = $data[$nextKey];
        }

        if ($data === $this->data) {
            return null;
        }

        return $data;
    }

    /**
     * Returns the value of the given header, or null if it does not exist.
     *
     * @param string $header
     *
     * @return string|null
     */
    public function header(string $header): ?string
    {
        return $this->headers->get(strtolower($header));
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }
}
