<?php

namespace Tests\Unit\RequestDrivers;

use Tests\TestCase;
use Tests\TestServiceClient;
use Illuminate\Support\Facades\Http;
use Cego\RequestInsurance\Models\RequestInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Cego\ServiceClientBase\RequestDrivers\RequestInsuranceDriver;
use Cego\ServiceClientBase\Exceptions\MissingSuggestedDependencyException;

class RequestInsuranceDriverTest extends TestCase
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
                                         ->useRequestInsurance();
    }

    /** @test */
    public function it_creates_request_insurance_rows_for_post_requests(): void
    {
        // Arrange
        $this->assertCount(0, RequestInsurance::all());
        $expectedPayload = json_encode([
            'data' => 123,
        ], JSON_THROW_ON_ERROR);

        // Act
        $this->client->testPostRequest('/my/post/endpoint', ['data' => 123], [
            RequestInsuranceDriver::OPTION_PRIORITY     => 1,
            RequestInsuranceDriver::OPTION_RETRY_COUNT  => 2,
            RequestInsuranceDriver::OPTION_RETRY_FACTOR => 3,
            RequestInsuranceDriver::OPTION_RETRY_CAP    => 4,
        ]);

        // Assert
        $this->assertCount(1, RequestInsurance::all());

        /** @var RequestInsurance $requestInsurance */
        $requestInsurance = RequestInsurance::findOrFail(1);

        $this->assertEquals('https://lupinsdev.dk/my/post/endpoint', $requestInsurance->url);
        $this->assertEquals($expectedPayload, $requestInsurance->payload);
        $this->assertEquals('post', $requestInsurance->method);
        $this->assertEquals(1, $requestInsurance->priority);
        $this->assertEquals(2, $requestInsurance->retry_count);
        $this->assertEquals(3, $requestInsurance->retry_factor);
        $this->assertEquals(4, $requestInsurance->retry_cap);
    }

    /** @test */
    public function it_creates_request_insurance_rows_for_put_requests(): void
    {
        // Arrange
        $this->assertCount(0, RequestInsurance::all());
        $expectedPayload = json_encode([
            'data' => 123,
        ], JSON_THROW_ON_ERROR);

        // Act
        $this->client->testPutRequest('/my/post/endpoint', ['data' => 123], [
            RequestInsuranceDriver::OPTION_PRIORITY     => 1,
            RequestInsuranceDriver::OPTION_RETRY_COUNT  => 2,
            RequestInsuranceDriver::OPTION_RETRY_FACTOR => 3,
            RequestInsuranceDriver::OPTION_RETRY_CAP    => 4,
        ]);

        // Assert
        $this->assertCount(1, RequestInsurance::all());

        /** @var RequestInsurance $requestInsurance */
        $requestInsurance = RequestInsurance::findOrFail(1);

        $this->assertEquals('https://lupinsdev.dk/my/post/endpoint', $requestInsurance->url);
        $this->assertEquals($expectedPayload, $requestInsurance->payload);
        $this->assertEquals('put', $requestInsurance->method);
        $this->assertEquals(1, $requestInsurance->priority);
        $this->assertEquals(2, $requestInsurance->retry_count);
        $this->assertEquals(3, $requestInsurance->retry_factor);
        $this->assertEquals(4, $requestInsurance->retry_cap);
    }

    /** @test */
    public function its_response_is_marked_as_async(): void
    {
        // Act
        $response = $this->client->testPostRequest('/my/post/endpoint', ['data' => 123], [
            RequestInsuranceDriver::OPTION_PRIORITY     => 1,
            RequestInsuranceDriver::OPTION_RETRY_COUNT  => 2,
            RequestInsuranceDriver::OPTION_RETRY_FACTOR => 3,
            RequestInsuranceDriver::OPTION_RETRY_CAP    => 4,
        ]);

        // Assert
        $this->assertFalse($response->isSynchronous);
    }

    /** @test */
    public function it_can_create_requests_with_no_options_and_data(): void
    {
        // Arrange
        $this->assertCount(0, RequestInsurance::all());

        // Act
        $this->client->testPostRequest('/my/post/endpoint');

        // Assert
        $this->assertCount(1, RequestInsurance::all());
    }

    /** @test */
    public function it_does_not_use_request_insurance_for_get_requests(): void
    {
        // Arrange
        Http::fake(static function () {
            return Http::response(["success" => true, "message" => "OK"]);
        });

        // Act
        $response = $this->client->testGetRequest('/my/get/endpoint');

        // Assert
        $this->assertTrue($response->isSynchronous);
    }
}
