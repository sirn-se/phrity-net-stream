<?php

/**
 * Tests for Net\Stream class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phrity\Util\ErrorHandler;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamTest extends TestCase
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

    public function testTempStream(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        // Check initial state
        $this->assertEquals([
            'wrapper_type' => 'PHP',
            'stream_type' => 'TEMP',
            'mode' => 'w+b',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => 'php://temp',
        ], $stream->getMetadata());
        $this->assertEquals(31, $stream->tell());
        $this->assertFalse($stream->eof());
        $this->assertEquals(31, $stream->getSize());
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());

        // Reading while at the end of stream
        $this->assertEquals("", $stream->read(4));
        $this->assertEquals(31, $stream->tell());
        $this->assertTrue($stream->eof());

        // Writing at end of stream
        $this->assertEquals(16, $stream->write(" with added data"));
        $this->assertEquals(47, $stream->tell());
        $this->assertTrue($stream->eof());
        $this->assertEquals(47, $stream->getSize());

        // Reset and read from beginning
        $stream->rewind();
        $this->assertEquals("This", $stream->read(4));
        $this->assertEquals(4, $stream->tell());
        $this->assertFalse($stream->eof());

        // Seek and read remaining content
        $stream->seek(20);
        $this->assertEquals(20, $stream->tell());
        $this->assertEquals("test stream with added data", $stream->getContents());
        $this->assertEquals("This is a temporary test stream with added data", "{$stream}");

        // Close and check
        $stream->close();
        $this->assertNull($stream->getMetadata());
        $this->assertTrue($stream->eof());
        $this->assertNull($stream->getSize());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isReadable());
        $stream->close();
        $this->assertNull($stream->detach());

        // Should issue a warning but return empty string
        (new ErrorHandler())->withAll(function () use ($stream) {
            $this->assertEquals("", "{$stream}");
        }, function ($exceptions, $result) {
            $this->assertEquals('Stream is detached', $exceptions[0]->getMessage());
        }, E_USER_WARNING);
    }

    public function testTellOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached");
        $stream->tell();
    }

    public function testReadOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached");
        $stream->read(4);
    }

    public function testWriteOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached");
        $stream->write("Will fail");
    }

    public function testSeekOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached");
        $stream->seek(0);
    }

    public function testRewindOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached");
        $stream->rewind();
    }

    public function testGetContentsOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached");
        $stream->getContents();
    }

    public function testReadOnly(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-readonly.txt';
        $stream = $factory->createStreamFromFile($file, 'r');

        // Check initial state
        $this->assertEquals([
            'wrapper_type' => 'plainfile',
            'stream_type' => 'STDIO',
            'mode' => 'r',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => $file,
            'timed_out' => false,
            'blocked' => true,
            'eof' => false,
        ], $stream->getMetadata());
        $this->assertEquals(0, $stream->tell());
        $this->assertFalse($stream->eof());
        $this->assertEquals(22, $stream->getSize());
        $this->assertTrue($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());

        // Read and check
        $this->assertEquals("Test case ", $stream->read(10));
        $this->assertEquals(10, $stream->tell());

        $stream->close();
    }

    public function testReadOnlyWriteError(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-readonly.txt';
        $stream = $factory->createStreamFromFile($file, 'r');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is not writable");
        $stream->write("Should fail");
    }

    public function testWriteOnly(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-writeonly.txt';
        $stream = $factory->createStreamFromFile($file, 'w');

        // Check initial state
        $this->assertEquals([
            'wrapper_type' => 'plainfile',
            'stream_type' => 'STDIO',
            'mode' => 'w',
            'unread_bytes' => 0,
            'seekable' => true,
            'uri' => $file,
            'timed_out' => false,
            'blocked' => true,
            'eof' => false,
        ], $stream->getMetadata());
        $this->assertEquals(0, $stream->tell());
        $this->assertFalse($stream->eof());
        $this->assertEquals(0, $stream->getSize());
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isWritable());
        $this->assertFalse($stream->isReadable());

        // Read and check
        $this->assertEquals(22, $stream->write("Test case for streams."));
        $this->assertEquals(22, $stream->tell());

        $stream->close();
    }

    public function testWriteOnlyReadError(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-writeonly.txt';
        $stream = $factory->createStreamFromFile($file, 'w');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is not readable");
        $stream->read(10);
    }
}
