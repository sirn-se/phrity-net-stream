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
    }
}
