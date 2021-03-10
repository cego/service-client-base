<?php

namespace Tests\Unit\RequestDrivers;

use Tests\TestCase;
use Tests\TestServiceClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Cego\ServiceClientBase\Exceptions\ServiceRequestFailedException;
use Cego\ServiceClientBase\Exceptions\MissingSuggestedDependencyException;

class HttpRequestDriverTest extends TestCase
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
    public function it_can_send_post_http_requests_synchronous(): void
    {
        // Arrange
        Http::fake(static function () {
            return Http::response(["success" => true, "message" => "OK"]);
        });

        $expectedData = ["success" => true, "message" => "OK"];

        // Act
        $response = $this->client->testPostRequest('/my/post/endpoint');

        // Assert
        $this->assertTrue($response->isSynchronous);
        $this->assertEquals($expectedData, $response->data->toArray());
        $this->assertEquals(200, $response->code);
    }

    /** @test */
    public function it_can_send_get_http_requests_synchronous(): void
    {
        // Arrange
        Http::fake(static function () {
            return Http::response(["success" => true, "message" => "OK"]);
        });

        $expectedData = ["success" => true, "message" => "OK"];

        // Act
        $response = $this->client->testGetRequest('/my/get/endpoint');

        // Assert
        $this->assertTrue($response->isSynchronous);
        $this->assertEquals($expectedData, $response->data->toArray());
        $this->assertEquals(200, $response->code);
    }

    /** @test */
    public function it_throws_service_request_failed_exceptions_on_server_failure(): void
    {
        // Arrange
        $this->expectException(ServiceRequestFailedException::class);

        Http::fake(static function () {
            return Http::response(["success" => false, "message" => "ERROR"], 500);
        });

        // Act
        $this->client->testGetRequest('/my/get/endpoint');
    }

    /** @test */
    public function it_throws_service_request_failed_exceptions_on_user_failure(): void
    {
        // Arrange
        $this->expectException(ServiceRequestFailedException::class);

        Http::fake(static function () {
            return Http::response(["success" => false, "message" => "ERROR"], 400);
        });

        // Act
        $this->client->testGetRequest('/my/get/endpoint');
    }
}
