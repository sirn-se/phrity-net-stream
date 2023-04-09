<?php

/**
 * Tests for Net\StreamCollection class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net\Test;

use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    SocketServer,
    SocketStream,
    StreamCollection,
};
use Phrity\Net\Uri;
use RuntimeException;
use TypeError;

class StreamCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8000');
        $server = new SocketServer($uri);
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = new SocketStream($resource);

        $collection = new StreamCollection();
        $this->assertEquals('@server', $collection->attach($server, '@server'));
        $this->assertEquals('@stream', $collection->attach($stream, '@stream'));

        $this->assertCount(2, $collection);
        foreach ($collection as $key => $item) {
            $this->assertSame($key == '@server' ? $server : $stream, $item);
        }

        $readable = $collection->getReadable();
        $this->assertCount(2, $readable);
        foreach ($readable as $key => $item) {
            $this->assertSame($key == '@stream' ? $stream : $server, $item);
        }

        $writable = $collection->getWritable();
        $this->assertCount(2, $writable);
        foreach ($writable as $key => $item) {
            $this->assertSame($key == '@stream' ? $stream : $server, $item);
        }

        $changed = $collection->waitRead(10); // Should not block
        $this->assertCount(1, $changed);
        foreach ($changed as $key => $item) {
            $this->assertSame('@stream', $key);
            $this->assertSame($stream, $item);
        }

        $this->assertTrue($collection->detach('@server'));
        $this->assertFalse($collection->detach('no-such-key'));
        $this->assertCount(1, $collection);

        $this->assertTrue($collection->detach($stream));
        $this->assertFalse($collection->detach($server));
        $this->assertEmpty($collection);

        $this->assertIsString($collection->attach($stream));
        $this->assertCount(1, $collection);
    }

    public function testAttachError(): void
    {
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = new SocketStream($resource);
        $collection = new StreamCollection();
        $collection->attach($stream, 'my-key');
        $this->expectException(RuntimeException::class);
        $collection->attach($stream, 'my-key');
    }

    public function testDetachhError(): void
    {
        $collection = new StreamCollection();
        $this->expectException(TypeError::class);
        $collection->detach(1);
    }
}
