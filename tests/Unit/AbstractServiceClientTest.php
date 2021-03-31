<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\TestServiceClient;
use InvalidArgumentException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Cego\ServiceClientBase\RequestDrivers\HttpRequestDriver;
use Cego\ServiceClientBase\Exceptions\InvalidHeaderException;
use Cego\ServiceClientBase\Exceptions\MissingSuggestedDependencyException;

class AbstractServiceClientTest extends TestCase
{
    use RefreshDatabase;

    protected TestServiceClient $client;

    /**
     * @throws MissingSuggestedDependencyException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = TestServiceClient::create('https://lupinsdev.dk/')
                                         ->auth('Username', 'Password')
                                         ->useRequestInsurance(false);
    }

    /** @test */
    public function it_can_push_global_headers(): void
    {
        // Arrange
        Http::fake(static function (Request $request) {
            return Http::response($request->headers()); // Returns headers as json response
        });

        // Act
        $response = $this->client
            ->pushGlobalHeader('custom_header', 'value')
            ->pushGlobalHeader(['header_from_array' => 'value2'])
            ->testGetRequest('/my/get/endpoint');

        // Assert
        $this->assertEquals('value', $response->data->get('custom_header')[0]);
        $this->assertEquals('value2', $response->data->get('header_from_array')[0]);
    }

    /** @test */
    public function it_can_pop_headers(): void
    {
        // Arrange
        Http::fake(static function (Request $request) {
            return Http::response($request->headers()); // Returns headers as json response
        });

        // Act
        $response = $this->client
            ->pushGlobalHeader(['header1' => 'value1', 'header2' => 'value2', 'header3' => 'value3'])
            ->popGlobalHeader('header1')
            ->popGlobalHeader(['header3'])
            ->testGetRequest('/my/get/endpoint');

        // Assert
        $this->assertNull($response->data->get('header1'));
        $this->assertEquals('value2', $response->data->get('header2')[0]);
        $this->assertNull($response->data->get('header3'));
    }

    /** @test */
    public function it_only_accept_string_headers_push_single(): void
    {
        // Arrange
        $this->expectException(InvalidHeaderException::class);

        // Act
        $this->client->pushGlobalHeader(123, 123);
    }

    /** @test */
    public function it_only_accept_string_headers_push_array(): void
    {
        // Arrange
        $this->expectException(InvalidHeaderException::class);

        // Act
        $this->client->pushGlobalHeader([1, 2, 3]);
    }

    /** @test */
    public function it_only_accept_string_headers_pop_single(): void
    {
        // Arrange
        $this->expectException(InvalidHeaderException::class);

        // Act
        $this->client->popGlobalHeader(1);
    }

    /** @test */
    public function it_only_accept_string_headers_pop_array(): void
    {
        // Arrange
        $this->expectException(InvalidHeaderException::class);

        // Act
        $this->client->popGlobalHeader([1, 2, 3]);
    }

    /** @test */
    public function it_cannot_have_empty_base_url(): void
    {
        // Arrange
        $this->expectException(InvalidArgumentException::class);

        // Act
        TestServiceClient::create('');
    }

    /** @test */
    public function it_can_correctly_create_the_auth_header(): void
    {
        // Arrange
        Http::fake(static function (Request $request) {
            return Http::response($request->headers()); // Returns headers as json response
        });

        $expectedAuthHeader = 'Basic VXNlcm5hbWU6UGFzc3dvcmQ=';

        $this->client = TestServiceClient::create('https://lupinsdev.dk/')
                                         ->auth('Username', 'Password') // Make sure the username and password stay consistent
                                         ->useRequestInsurance(false);

        // Act
        $response = $this->client->testGetRequest('/endpoint');

        // Assert
        $this->assertEquals($expectedAuthHeader, $response->data->get('Authorization')[0]);
    }

    /** @test */
    public function it_can_set_timeout(): void
    {
        // Arrange

        // Act
        $this->client->withTimeout(123);

        // Assert
        $this->assertEquals([
            HttpRequestDriver::OPTION_TIMEOUT => 123,
        ], $this->client->getGlobalOptions());
    }

    /** @test */
    public function it_can_clear_timeouts(): void
    {
        // Arrange

        // Act
        $this->client
            ->withTimeout(123)
            ->withDefaultTimeout();

        // Assert
        $this->assertEquals([], $this->client->getGlobalOptions());
    }
}
