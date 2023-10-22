<?php

/**
 * Tests for Net\SocketStream class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net\Test;

use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    SocketStream,
    StreamFactory,
    StreamException
};

class SocketStreamTest extends TestCase
{
    public function testTempStream(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createSocketStreamFromResource($resource);

        $this->assertTrue($stream->isConnected());
        $this->assertEquals('', $stream->getRemoteName());
        $this->assertEquals('', $stream->getLocalName());
        $this->assertEquals('stream', $stream->getResourceType());
        $this->assertTrue($stream->isBlocking());

        $this->assertTrue($stream->setBlocking(false));
        $this->assertFalse($stream->isBlocking());

        $this->assertFalse($stream->setTimeout(1, 2));
    }

    public function testSetBlockingOnClosed(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createSocketStreamFromResource($resource);
        $stream->close();
        $this->assertNull($stream->isBlocking());
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::STREAM_DETACHED);
        $this->expectExceptionMessage('Stream is detached.');
        $stream->setBlocking(false);
    }

    public function testSetTimeoutOnClosed(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createSocketStreamFromResource($resource);
        $stream->close();
        $this->assertNull($stream->isBlocking());
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::STREAM_DETACHED);
        $this->expectExceptionMessage('Stream is detached.');
        $stream->setTimeout(1, 2);
    }

    public function testReadLine(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream-readonly.txt', 'r');
        $stream = $factory->createSocketStreamFromResource($resource);
        $this->assertEquals('Test case for streams.', $stream->readLine(1024));
    }

    public function testReadLineOnClosed(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream-readonly.txt', 'r');
        $stream = $factory->createSocketStreamFromResource($resource);
        $stream->close();
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::STREAM_DETACHED);
        $this->expectExceptionMessage('Stream is detached.');
        $stream->readLine(1024);
    }

    public function testWriteOnlyReadLineError(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream-writeonly.txt', 'w');
        $stream = $factory->createSocketStreamFromResource($resource);
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::NOT_READABLE);
        $this->expectExceptionMessage('Stream is not readable.');
        $stream->readLine(1024);
    }

    public function testReadClose(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createSocketStreamFromResource($resource);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $stream->closeRead();
        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isConnected());
        $stream->closeWrite();
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isConnected());
    }

    public function testWriteClose(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createSocketStreamFromResource($resource);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $stream->closeWrite();
        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isConnected());
        $stream->closeRead();
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isConnected());
    }
}
