[Documentation](../README.md) / SocketServer

# SocketServer

The SocketServer class enables a server on local socket.
Creates SocketStream connection instances.

## Synopsis

```php
namespace Phrity\Net;

use Psr\Http\Message\UriInterface;

class SocketServer extends Stream
{
    // Constructor

    public function __construct(UriInterface $uri, Context|null $context = null);

    // Methods

    // Accept connection on socket server
    public function accept(int|float|null $timeout = null): SocketStream|null;
    // Get available transports protocols
    public function getTransports(): array;
    // Get stream context
    public function getContext(): Context;
    // Set stream context
    public function setContext(Context $context): self;
    // If stream is blocking or not
    public function isBlocking(): bool|null;
    // Change blocking mode
    public function setBlocking(bool $enable): bool;
    public function getMetadata(string|null $key = null): mixed;
}
```
