[Documentation](../README.md) / SocketStream

# SocketStream

The SocketStream class extends [Stream](Stream.md) and adds extra methods usable on a socket stream.

## Synopsis

```php
namespace Phrity\Net;

class SocketStream extends Stream
{
    // Methods

    // If stream is connected to remote
    public function isConnected(): bool;
    // Returns remote name
    public function getRemoteName(): string|null;
    // Returns local name
    public function getLocalName(): string|null;
    // Get resource type
    public function getResourceType(): string;
    // If stream is blocking or not
    public function isBlocking(): bool|null;
    // Change blocking mode
    public function setBlocking(bool $enable): bool;
    // If stream has readable content
    public function hasContents(): bool;
    // Set timeout in seconds
    public function setTimeout(int|float $timeout): bool;
    // Read a line from stream, up to $length bytes
    public function readLine(int<0, max> $length): string|null;
    // Closes the stream for further reading
    public function closeRead(): void;
    // Closes the stream for further writing
    public function closeWrite(): void;
}
```
