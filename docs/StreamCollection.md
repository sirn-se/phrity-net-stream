[Documentation](../README.md) / SocketServer

# SocketServer

The StreamCollection class to handle zero to many stream connections.
Enables select operations to observe multiple connections.

## Synopsis

```php
namespace Phrity\Net;

use Countable;
use Iterator;
use Psr\Http\Message\UriInterface;

class StreamCollection implements Countable, Iterator
{
    // Constructor

   public function __construct();

    // Collectors and selectors

    // Attach stream to collection
    public function attach(Stream $attach, string|null $key = null): string;
    // Detach stream from collection
    public function detach(Stream|string $detach): bool;
    // Get collection of readable streams
    public function getReadable(): self;
    // Get collection of writable streams
    public function getWritable(): self;
     // Wait for and get collection of streams with data to read
    public function waitRead(int|float $timeout = 60): self;

    // Countable interface implementation

    public function count(): int;

    // Iterator interface implementation

    public function current(): Stream|null;
    public function key(): string|null;
    public function next(): void;
    public function rewind(): void;
    public function valid(): bool;
}
```
