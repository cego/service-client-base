<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\TestServiceClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
