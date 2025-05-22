[Documentation](../README.md) / StreamFactory

# StreamFactory

The StreamFactory class is fully compatible with [PSR-17 StreamFactoryInterface](https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface),
implementing specified methods and some extras. Can be used anywhere where PSR-17 StreamFactoryInterface compability is expected.

## Synopsis

```php
namespace Phrity\Net;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;

class StreamFactory implements StreamFactoryInterface
{
    // Constructor

    public function __construct();

    // PSR-17 StreamFactoryInterface methods

    public function createStream(string $content = ''): Stream;
    public function createStreamFromFile(string $filename, string $mode = 'r'): Stream;
    // Must be a resource of type stream
    public function createStreamFromResource(resource $resource): Stream;

    // Additional methods

    // Create a socket stream
    public function createSocketStreamFromResource($resource): SocketStream;
    // Create socket client
    public function createSocketClient(UriInterface $uri, Context|null $context = null): SocketClient;
    // Create a socket server
    public function createSocketServer(UriInterface $uri, Context|null $context = null): SocketServer;
    // Create a stream collection
    public function createStreamCollection(): StreamCollection;
}
```
