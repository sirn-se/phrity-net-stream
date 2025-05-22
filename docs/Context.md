[Documentation](../README.md) / Context

# Context

The Context wraps context for various streams.

## Synopsis

```php
namespace Phrity\Net;

use Closure;

class Context
{
    // Constructor

    public function __construct(mixed $stream = null);

    // Methods

    public function getOption(string $wrapper, string $option): mixed;
    public function getOptions(): array;
    public function setOption(string $wrapper, string $option, mixed $value): self;
    public function setOptions(array $options): self;
    public function getResource(): mixed;

    // Listener methods

    public function onResolve(Closure $closure): void;
    public function onConnect(Closure $closure): void;
    public function onAuthRequired(Closure $closure): void;
    public function onMimeType(Closure $closure): void;
    public function onFileSize(Closure $closure): void;
    public function onRedirected(Closure $closure): void;
    public function onProgress(Closure $closure): void;
    public function onCompleted(Closure $closure): void;
    public function onFailure(Closure $closure): void;
    public function onAuthResult(Closure $closure): void;
}
```
