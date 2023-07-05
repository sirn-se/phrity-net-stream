<?php

/**
 * Tests for Net\StreamFactory class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    SocketClient,
    SocketServer,
    SocketStream,
    Stream,
    StreamCollection,
    StreamFactory,
};
use Phrity\Net\Uri;
use Psr\Http\Message\{
    StreamFactoryInterface,
    StreamInterface
};
use RuntimeException;

class StreamFactoryTest extends TestCase
{
    public function setUp(): void
    {
        chmod(__DIR__ . '/fixtures/stream-readonly.txt', 0400);
        chmod(__DIR__ . '/fixtures/stream-writeonly.txt', 0200);
    }

    public function tearDown(): void
    {
        chmod(__DIR__ . '/fixtures/stream-readonly.txt', 0644);
        chmod(__DIR__ . '/fixtures/stream-writeonly.txt', 0644);
    }

    public function testFactory(): void
    {
        $factory = new StreamFactory();
        $this->assertInstanceOf(StreamFactoryInterface::class, $factory);
        $this->assertInstanceOf(StreamFactory::class, $factory);
    }

    public function testCreateStream(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('Test creating stream');
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCreateStreamFromFile(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream.txt';
        $stream = $factory->createStreamFromFile($file, 'r+');
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCreateStreamFromFileNoFileError(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/do-not-exist.txt';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("File '{$file}' do not exist.");
        $stream = $factory->createStreamFromFile($file, 'r+');
    }

    public function testCreateStreamFromFileInvalidModeError(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream.txt';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid mode \'invalid\'.');
        $stream = $factory->createStreamFromFile($file, 'invalid');
    }

    public function testCreateStreamFromFileReadOnly(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-readonly.txt';
        $stream = $factory->createStreamFromFile($file, 'r');
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCreateStreamFromFileReadOnlyError(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-readonly.txt';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not open '{$file}'.");
        $stream = $factory->createStreamFromFile($file, 'w');
    }

    public function testCreateStreamFromFileWriteOnly(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-writeonly.txt';
        $stream = $factory->createStreamFromFile($file, 'w');
        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    public function testCreateStreamFromFileWriteOnlyError(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-writeonly.txt';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not open '{$file}'.");
        $stream = $factory->createStreamFromFile($file, 'r');
    }

    public function testCreateStreamFromResource(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createStreamFromResource($resource);
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(Stream::class, $stream);
    }

    public function testCreateStreamFromResourceInvalidResource(): void
    {
        $factory = new StreamFactory();
        $resource = 'No, not a resource';
        // This provoke stream implementation to throw exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got type 'string'.");
        $stream = $factory->createStreamFromResource($resource);
    }

    public function testCreateSocketStreamFromResource(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createSocketStreamFromResource($resource);
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertInstanceOf(SocketStream::class, $stream);
    }

    public function testCreateSocketServer(): void
    {
        $url = new Uri('tcp://0.0.0.0:8000');
        $factory = new StreamFactory();
        $server = $factory->createSocketServer($url);
        $this->assertInstanceOf(SocketServer::class, $server);
        $server->close();
    }

    public function testCreateSocketClient(): void
    {
        $url = new Uri('tcp://0.0.0.0:8000');
        $factory = new StreamFactory();
        $client = $factory->createSocketClient($url);
        $this->assertInstanceOf(SocketClient::class, $client);
    }

    public function testCreateStreamCollection(): void
    {
        $factory = new StreamFactory();
        $collection = $factory->createStreamCollection();
        $this->assertInstanceOf(StreamCollection::class, $collection);
    }
}
