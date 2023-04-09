<?php

/**
 * Tests for Net\Stream class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    Stream,
    StreamFactory,
};
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
        $this->assertEquals('TEMP', $stream->getMetadata('stream_type'));
        $this->assertNull($stream->getMetadata('no_such_key'));

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
            $this->assertEquals('Stream is detached.', $exceptions[0]->getMessage());
        }, E_USER_WARNING);
    }

    public function testDetach(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $resource = $stream->detach();
        $this->assertIsResource($resource);
        $this->assertEquals('stream', get_resource_type($resource));
        $this->assertNull($stream->getMetadata());
        $this->assertTrue($stream->eof());
        $this->assertNull($stream->getSize());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isReadable());

        $resource = $stream->detach();
        $this->assertNull($resource);
    }

    public function testSeekFailure(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Failed to seek.");
        $stream->seek(-1);
        $stream->close();
    }

    public function testTellOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached.");
        $stream->tell();
        $stream->close();
    }

    public function testReadOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached.");
        $stream->read(4);
        $stream->close();
    }

    public function testWriteOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached.");
        $stream->write("Will fail");
        $stream->close();
    }

    public function testSeekOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached.");
        $stream->seek(0);
        $stream->close();
    }

    public function testRewindOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached.");
        $stream->rewind();
        $stream->close();
    }

    public function testGetContentsOnClosed(): void
    {
        $factory = new StreamFactory();
        $stream = $factory->createStream('This is a temporary test stream');

        $stream->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is detached.");
        $stream->getContents();
        $stream->close();
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
        $this->expectExceptionMessage("Stream is not writable.");
        $stream->write("Should fail");
        $stream->close();
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
        $this->expectExceptionMessage("Stream is not readable.");
        $stream->read(10);
        $stream->close();
    }

    public function testWriteOnlyGetContentsError(): void
    {
        $factory = new StreamFactory();
        $file = __DIR__ . '/fixtures/stream-writeonly.txt';
        $stream = $factory->createStreamFromFile($file, 'w');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is not readable.");
        $stream->getContents();
        $stream->close();
    }

    public function testDirectoryStream(): void
    {
        $dir = opendir(__DIR__);
        $stream = new Stream($dir);
        $this->assertEquals([
            'wrapper_type' => 'plainfile',
            'stream_type' => 'dir',
            'mode' => 'r',
            'unread_bytes' => 0,
            'seekable' => true,
            'timed_out' => false,
            'blocked' => true,
            'eof' => false,
        ], $stream->getMetadata());
        $this->assertEquals(0, $stream->tell());
        $this->assertFalse($stream->eof());
        $this->assertNull($stream->getSize());
        $this->assertTrue($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $this->assertEquals("", $stream->getContents());
    }

    public function testRemoteStream(): void
    {
        $remote = fopen('https://phrity.sirn.se/', 'r');
        $stream = new Stream($remote);
        $this->assertEquals('http', $stream->getMetadata('wrapper_type'));
        $this->assertEquals('tcp_socket/ssl', $stream->getMetadata('stream_type'));
        $this->assertEquals('r', $stream->getMetadata('mode'));
        $this->assertFalse($stream->getMetadata('seekable'));
        $this->assertFalse($stream->getMetadata('timed_out'));
        $this->assertTrue($stream->getMetadata('blocked'));
        $this->assertFalse($stream->getMetadata('eof'));
        $this->assertEquals('https://phrity.sirn.se/', $stream->getMetadata('uri'));
        $this->assertIsArray($stream->getMetadata('crypto'));
        $this->assertIsArray($stream->getMetadata('wrapper_data'));

        $this->assertEquals(0, $stream->tell());
        $this->assertFalse($stream->eof());
        $this->assertNull($stream->getSize());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());

        $stream->close();
    }

    public function testSeekOnRemoteError(): void
    {
        $remote = fopen('https://phrity.sirn.se/', 'r');
        $stream = new Stream($remote);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Stream is not seekable.");
        $stream->seek(0);
        $stream->close();
    }

    public function testConstructNoResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; got type 'string'.");
        $stream = new Stream("should fail");
    }

    public function testConstructInvalidResource(): void
    {
        // We need something that is a resouce but not a "stream" resource
        $resource = stream_context_create([]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid stream provided; gor resource type 'stream-context'.");
        $stream = new Stream($resource);
    }
}
