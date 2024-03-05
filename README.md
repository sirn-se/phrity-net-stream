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

## Stream class

The `Phrity\Net\Stream` class is fully compatible with [PSR-7 StreamInterface](https://www.php-fig.org/psr/psr-7/#34-psrhttpmessagestreaminterface),
implementing specified methods but no extras. Can be used anywhere where PSR-7 StreamInterface compability is expected.

```php
class Stream implements StreamInterface
{

    // Constructor

    public function __construct(resource $stream); // Must be a resource of type stream

    // PSR-7 methods

    public function __toString(): string;
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
    public function read(int $length): string;
    public function getContents(): string;
    public function getMetadata(string|null $key = null): mixed;

    // Additional methods

    public function getResource(): resource;
}
```

## SocketStream class

The `Phrity\Net\SocketStream` class extends `Phrity\Net\Stream` and adds extra methods usable on a socket stream.

```php
class SocketStream extends Stream
{

    // Methods

    public function isConnected(): bool; // If stream is connected to remote
    public function getRemoteName(): string|null; // Returns remote name
    public function getLocalName(): string|null; // Returns local name
    public function getResourceType(): string; // Get resource type
    public function isBlocking(): bool|null; // If stream is blocking or not
    public function setBlocking(bool $enable): bool; // Change blocking mode
    public function setTimeout(int $seconds, int $microseconds = 0): bool; // Set timeout
    public function readLine(int $length): string|null // Read a line from stream, up to $length bytes
    public function closeRead(): void; // Closes the stream for further reading
    public function closeWrite(): void; // Closes the stream for further writing
}
```

## SocketClient class

The `Phrity\Net\SocketClient` class enables a client for remote socket.

```php
class SocketClient {

    // Constructor

    public function __construct(UriInterface $uri);

    // Methods

    public function setPersistent(bool $persistent): self; // If client should use persisten connection
    public function setTimeout(int|null $timeout): self; // Set timeout
    public function setContext(array|null $options = null, array|null $params = null): self; // Set stream context
    public function connect(): SocketStream; // Connect to remote
}
```

## SocketServer class

The `Phrity\Net\SocketServer` class enables a server on local socket.

```php
class SocketServer extends Stream {

    // Constructor

    public function __construct(UriInterface $uri);

    // Methods

    public function accept(int|null $timeout = null): SocketStream|null; // Accept connection on socket server
    public function getTransports(): array; // Get available transports
    public function isBlocking(): bool|null; // If stream is blocking or not
    public function setBlocking(bool $enable): bool; // Change blocking mode
}
```

## StreamCollection class

The `Phrity\Net\StreamCollection` class to handle zero to many connections.

```php
class StreamCollection implements Countable, Iterator
{

    // Constructor

   public function __construct();

    // Collectors and selectors

    public function attach(Stream $attach, string|null $key = null): string; // Attach stream to collection
    public function detach(Stream|string $detach): bool; // Detach stream from collection
    public function getReadable(): self; // Get collection of readable streams
    public function getWritable(): self; // Get collection of writable streams
    public function waitRead(int $seconds = 60): self; // Wait for and get collection of streams with data to read

    // Countable interface implementation

    public function count(): int;

    // Iterator interface implementation

    public function current(): Stream;
    public function key(): string;
    public function next(): void;
    public function rewind(): void;
    public function valid(): bool;
}
```

## StreamFactory class

The `Phrity\Net\StreamFactory` class is fully compatible with [PSR-17 StreamFactoryInterface](https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface),
implementing specified methods and some extras. Can be used anywhere where PSR-17 StreamFactoryInterface compability is expected.

```php
class StreamFactory implements StreamFactoryInterface
{

    // Constructor

    public function __construct();

    // PSR-17 methods

    public function createStream(string $content = ''): Stream;
    public function createStreamFromFile(string $filename, string $mode = 'r'): Stream;
    public function createStreamFromResource(resource $resource): Stream; // Must be a resource of type stream

    // Additional methods

    public function createSocketStreamFromResource($resource): SocketStream; // Create a socket stream
    public function createSocketClient(UriInterface $uri): SocketClient; / Create socket client
    public function createSocketServer(UriInterface $uri): SocketServer; // Create a socket server
    public function createStreamCollection(): StreamCollection; // Create a stream collection
}
```

## StreamException class

The `Phrity\Net\StreamException` is thrown when a stream related error occurs.

```php
class StreamException extends RuntimeException {

    // Constructor

    public function __construct(int $code, array $data = [], Throwable|null $previous = null)
}
```

## Versions

| Version | PHP | |
| --- | --- | --- |
| `2.0` | `^8.0` | Modernization |
| `1.3` | `^7.4\|^8.0` | Closing read and write separately |
| `1.2` | `^7.4\|^8.0` | Socket client |
| `1.1` | `^7.4\|^8.0` | Stream collection |
| `1.0` | `^7.4\|^8.0` | Initial version |
