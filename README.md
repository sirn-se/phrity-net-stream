[![Build Status](https://github.com/sirn-se/phrity-net-stream/actions/workflows/acceptance.yml/badge.svg)](https://github.com/sirn-se/phrity-net-stream/actions)
[![Coverage Status](https://coveralls.io/repos/github/sirn-se/phrity-net-stream/badge.svg?branch=main)](https://coveralls.io/github/sirn-se/phrity-net-stream?branch=main)

# Introduction

Package that provide implementations of [PSR-7 StreamInterface](https://www.php-fig.org/psr/psr-7/#34-psrhttpmessagestreaminterface)
and [PSR-17 StreamFactoryInterface](https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface)
but also adds stream and socket related funcitonality.
It is designed for use with socket connections.

## Installation

Install with [Composer](https://getcomposer.org/);
```
composer require phrity/net-stream
```


## Versions

| Version | PHP | |
| --- | --- | --- |
| `1.0` | `^7.4\|^8.0` | Initial version |


## Stream class

The `Phrity\Net\Stream` class is fully compatible with [PSR-7 StreamInterface](https://www.php-fig.org/psr/psr-7/#34-psrhttpmessagestreaminterface),
implementing specified methods but no extras. Can be used anywhere where PSR-7 StreamInterface compability is expected.

```php
class Stream {
    public function __construct(resource $resource); // Must be a resource of type stream

    public function __toString(): string;
    public function close(): void;
    public function detach(): ?resource;
    public function getSize(): ?int;
    public function tell(): int;
    public function eof(): bool;
    public function isSeekable(): bool;
    public function seek(int $offset, int $whence = SEEK_SET): void;
    public function rewind(): void;
    public function isWritable(): bool;
    public function write(string $string): int;
    public function isReadable(): bool;
    public function read(int $length): string;
    public function getContents(): string;
    public function getMetadata(?string $key = null): mixed;
}
```

## StreamFactory class

The `Phrity\Net\StreamFactory` class is fully compatible with [PSR-17 StreamFactoryInterface](https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface),
implementing specified methods but no extras. Can be used anywhere where PSR-17 StreamFactoryInterface compability is expected.

```php
class StreamFactory {
    public function __construct();

    public function createStream(string $content = ''): StreamInterface;
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface;
    public function createStreamFromResource(resource $resource): StreamInterface; // Must be a resource of type stream
}
```
