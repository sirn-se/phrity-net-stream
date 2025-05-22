[Documentation](../README.md) / StreamException

# StreamException

The StreamException is thrown when a stream related error occurs.

## Synopsis

```php
namespace Phrity\Net;

use RuntimeException;
use Throwable;

class StreamException extends RuntimeException
{
    // Constructor

    public function __construct(int $code, array $data = [], Throwable|null $previous = null)
}
```
