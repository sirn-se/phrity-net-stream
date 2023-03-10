<?php

/**
 * Tests for Net\SocketStream class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class SocketStreamTest extends TestCase
{
    public function testTempStream(): void
    {
        $factory = new StreamFactory();
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = $factory->createSocketStreamFromResource($resource);

        $this->assertEquals('', $stream->getRemoteName());
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached.");
        $stream->setBlocking(false);
    }
}
