[Documentation](../README.md) / SocketClient

# SocketClient

The SocketClient class enables a client for remote socket.
Creates SocketStream connection instances.

## Synopsis

```php
namespace Phrity\Net;

use Psr\Http\Message\UriInterface;

class SocketClient
{
    // Constructor

    public function __construct(UriInterface $uri, Context|null $context = null);

    // Methods

     // If client should use persistent connection
    public function setPersistent(bool $persistent): self;
    // Set timeout in seconds
    public function setTimeout(int|float|null $timeout): self;
    // Get stream context
    public function getContext(): Context;
    // Set stream context
    public function setContext(Context $context): self;
    // Connect to remote
    public function connect(): SocketStream;
}
```
