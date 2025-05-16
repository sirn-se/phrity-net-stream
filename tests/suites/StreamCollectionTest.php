<?php

/**
 * Tests for Net\StreamCollection class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    SocketServer,
    SocketStream,
    StreamCollection,
    StreamException
};
use Phrity\Net\Uri;
use TypeError;

class StreamCollectionTest extends TestCase
{
    public function testCollection(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8020');
        $server = new SocketServer($uri);
        /** @var resource $resource */
        $resource = fopen(__DIR__ . '/../fixtures/stream.txt', 'r+');
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

        /* @phpstan-ignore method.alreadyNarrowedType */
        $this->assertIsString($collection->attach($stream));
        $this->assertCount(1, $collection);
    }

    public function testInvalidTimeout(): void
    {
        $collection = new StreamCollection();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be 0 or more.');
        $collection->waitRead(-1);
    }

    public function testAttachError(): void
    {
        /** @var resource $resource */
        $resource = fopen(__DIR__ . '/../fixtures/stream.txt', 'r+');
        $stream = new SocketStream($resource);
        $collection = new StreamCollection();
        $collection->attach($stream, 'my-key');
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::COLLECT_KEY_CONFLICT);
        $this->expectExceptionMessage('Stream with name "my-key" already attached.');
        $collection->attach($stream, 'my-key');
    }

    public function testDetachError(): void
    {
        $collection = new StreamCollection();
        $this->expectException(TypeError::class);
        /* @phpstan-ignore argument.type */
        $collection->detach(1);
    }

    public function testRecoverableSelectError(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8001');
        $server = new SocketServer($uri);
        $collection = new StreamCollection();
        $collection->attach($server, '@server');

        pcntl_signal(SIGTERM, function () {
        });
        exec('php -r "usleep(1);posix_kill(' . getmypid() . ', SIGTERM);" > /dev/null 2>/dev/null &');
        $changed = $collection->waitRead(10);
        $this->assertCount(0, $changed);
    }

    public function testEmptyCollection(): void
    {
        $collection = new StreamCollection();
        $changed = $collection->waitRead(10); // Should not block
        $this->assertEmpty($changed);
    }
}
