# service-client-base
A project containing utility and abstractions for various specific clients implementations

Project was initially created by:

- Niki Ewald Zakariassen (NIZA)

## Usage
The abstract service client supplies a base fluid interface for interacting with services.

### Extend AbstractServiceClient

```php
use Cego\ServiceClientBase\AbstractServiceClient;
use Cego\ServiceClientBase\RequestDrivers\Response;

class YourServiceClient extends AbstractServiceClient
{
    public function myGetRequestEndpoint(array $queryParameters = [], array $options = []): Response
    {
        return $this->getRequest('/my/get/endpoint', $queryParameters, $options);
    }

    public function myPostRequestEndpoint(array $data = [], array $options = []): Response
    {
        return $this->postRequest('/my/post/endpoint', $data, $options);
    }
}
```

### Interface
```php
// Getting a client instance
YourServiceClient::create('service_base_url')
                 ->auth('username', 'password');
```

```php
// Using request insurance
YourServiceClient::create('service_base_url')
                 ->auth('username', 'password')
                 ->useRequestInsurance();
```
<sub>Note: Request insurance is only usable for POST requests, and is remembered for following requests. GET requests will always use the synchronous HTTP driver - Remember to install cego/request-insurance for enabling this feature</sub>


### Error Handling

The client does not use error return values, meaning if a request failed then an exception will be thrown: [ServiceRequestFailedException.php](src/ServiceClientBase/Exceptions/ServiceRequestFailedException.php).

The client has a configurable amount of retries on errors, before throwing an exception.
- env("SERVICE_CLIENT_TIMEOUT")
- env("SERVICE_CLIENT_MAXIMUM_NUMBER_OF_RETRIES")
- env("SERVICE_CLIENT_RETRY_DELAY")
