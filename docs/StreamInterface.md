[Documentation](../README.md) / StreamInterface

# StreamInterface

Interface indicating a Stream implementation.

## Synopsis

```php
namespace Phrity\Net;

interface StreamInterface extends Psr\Http\Message\StreamInterface
{
    // Methods

    public function getContext(): Context;
    public function getResource(): resource;
}
```
