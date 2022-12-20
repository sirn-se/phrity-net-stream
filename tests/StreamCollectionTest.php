<?php

/**
 * Tests for Net\StreamCollection class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net;

use PHPUnit\Framework\TestCase;


class StreamCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8000');
        $server = new SocketServer($uri);
        $resource = fopen(__DIR__ . '/fixtures/stream.txt', 'r+');
        $stream = new SocketStream($resource);

        $collection = new StreamCollection();
        $collection->attach($server, '@server');
        $collection->attach($stream, '@stream');

        $this->assertCount(2, $collection);
        foreach ($collection as $key => $item) {
            $this->assertSame($key == '@server' ? $server : $stream, $item);
        }

        $readable = $collection->getReadable();
        $this->assertCount(1, $readable);
        foreach ($readable as $key => $item) {
            $this->assertSame('@stream', $key);
            $this->assertSame($stream, $item);
        }

        $writable = $collection->getWritable();
        $this->assertCount(1, $writable);
        foreach ($writable as $key => $item) {
            $this->assertSame('@stream', $key);
            $this->assertSame($stream, $item);
        }

        $changed = $collection->waitRead(10); // Should not block
        $this->assertCount(1, $changed);
        foreach ($changed as $key => $item) {
            $this->assertSame('@stream', $key);
            $this->assertSame($stream, $item);
        }

        $collection->detach('@server');
        $this->assertCount(1, $collection);

        $collection->detach($stream);
        $this->assertEmpty($collection);
    }
}
