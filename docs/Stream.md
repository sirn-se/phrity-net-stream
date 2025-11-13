[Documentation](../README.md) / Stream

# Stream

The Stream class is fully compatible with [PSR-7 StreamInterface](https://www.php-fig.org/psr/psr-7/#34-psrhttpmessagestreaminterface),
implementing specified methods but no extras. Can be used anywhere where PSR-7 StreamInterface compability is expected.

## Synopsis

```php
namespace Phrity\Net;

use Psr\Http\Message\StreamInterface;
use Stringable;

class Stream implements StreamInterface, Stringable
{
    // Constructor metod

    // Must be a resource of type stream
    public function __construct(resource $stream);

    // PSR-7 StreamInterface methods

    public function close(): void;
    public function detach(): resource|null;
    public function getSize(): int|null;
    public function tell(): int;
    public function eof(): bool;
    public function isSeekable(): bool;
    public function seek(int $offset, int $whence = SEEK_SET): void;
    public function rewind(): void;
    public function isWritable(): bool;
    public function write(string $string): int;
    public function isReadable(): bool;
    public function read(int<1, max> $length): string;
    public function getContents(): string;
    public function getMetadata(string|null $key = null): mixed;

    // Stringable method

    public function __toString(): string;

    // Additional methods

    // StreamInterface methods
    public function getContext(): Context;
    public function getResource(): resource;
}
```
