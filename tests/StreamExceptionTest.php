<?php

declare(strict_types=1);

namespace Phrity\Net\Test;

use PHPUnit\Framework\TestCase;
use Phrity\Net\StreamException;

class StreamExceptionTest extends TestCase
{
    public function testDetachedException(): void
    {
        $exception = new StreamException(StreamException::STREAM_DETACHED);
        $this->assertEquals(1000, $exception->getCode());
        $this->assertEquals('Stream is detached.', $exception->getMessage());
    }

    public function testNotReadableException(): void
    {
        $exception = new StreamException(StreamException::NOT_READABLE);
        $this->assertEquals(1010, $exception->getCode());
        $this->assertEquals('Stream is not readable.', $exception->getMessage());
    }

    public function testNotWritableException(): void
    {
        $exception = new StreamException(StreamException::NOT_WRITABLE);
        $this->assertEquals(1011, $exception->getCode());
        $this->assertEquals('Stream is not writable.', $exception->getMessage());
    }

    public function testNotSeekableException(): void
    {
        $exception = new StreamException(StreamException::NOT_SEEKABLE);
        $this->assertEquals(1012, $exception->getCode());
        $this->assertEquals('Stream is not seekable.', $exception->getMessage());
    }

    public function testFailReadException(): void
    {
        $exception = new StreamException(StreamException::FAIL_READ);
        $this->assertEquals(1020, $exception->getCode());
        $this->assertEquals('Failed read() on stream.', $exception->getMessage());
    }

    public function testFailWriteException(): void
    {
        $exception = new StreamException(StreamException::FAIL_WRITE);
        $this->assertEquals(1021, $exception->getCode());
        $this->assertEquals('Failed write() on stream.', $exception->getMessage());
    }

    public function testFailSeekException(): void
    {
        $exception = new StreamException(StreamException::FAIL_SEEK);
        $this->assertEquals(1022, $exception->getCode());
        $this->assertEquals('Failed seek() on stream.', $exception->getMessage());
    }

    public function testFailTellException(): void
    {
        $exception = new StreamException(StreamException::FAIL_TELL);
        $this->assertEquals(1023, $exception->getCode());
        $this->assertEquals('Failed tell() on stream.', $exception->getMessage());
    }

    public function testFailContentsException(): void
    {
        $exception = new StreamException(StreamException::FAIL_CONTENTS);
        $this->assertEquals(1024, $exception->getCode());
        $this->assertEquals('Failed getContents() on stream.', $exception->getMessage());
    }

    public function testFailGetsException(): void
    {
        $exception = new StreamException(StreamException::FAIL_GETS);
        $this->assertEquals(1025, $exception->getCode());
        $this->assertEquals('Failed gets() on stream.', $exception->getMessage());
    }

    public function testClientConnectException(): void
    {
        $exception = new StreamException(StreamException::CLIENT_CONNECT_ERR, ['uri' => 'http://test.com']);
        $this->assertEquals(2000, $exception->getCode());
        $this->assertEquals('Client could not connect to "http://test.com".', $exception->getMessage());
    }

    public function testSchemeTransportException(): void
    {
        $exception = new StreamException(StreamException::SCHEME_TRANSPORT, ['scheme' => 'ssl']);
        $this->assertEquals(3000, $exception->getCode());
        $this->assertEquals('Scheme "ssl" is not supported.', $exception->getMessage());
    }

    public function testSchemeHandlerException(): void
    {
        $exception = new StreamException(StreamException::SCHEME_HANDLER, ['scheme' => 'ssl']);
        $this->assertEquals(3001, $exception->getCode());
        $this->assertEquals('Could not handle scheme "ssl".', $exception->getMessage());
    }

    public function testServerSocketException(): void
    {
        $exception = new StreamException(StreamException::SERVER_SOCKET_ERR, ['uri' => 'http://test.com']);
        $this->assertEquals(3010, $exception->getCode());
        $this->assertEquals('Could not create socket for "http://test.com".', $exception->getMessage());
    }

    public function testServerClosedException(): void
    {
        $exception = new StreamException(StreamException::SERVER_CLOSED);
        $this->assertEquals(3011, $exception->getCode());
        $this->assertEquals('Server is closed.', $exception->getMessage());
    }

    public function testServerAcceptException(): void
    {
        $exception = new StreamException(StreamException::SERVER_ACCEPT_ERR);
        $this->assertEquals(3012, $exception->getCode());
        $this->assertEquals('Could not accept on socket.', $exception->getMessage());
    }

    public function testCollectKeyConflictException(): void
    {
        $exception = new StreamException(StreamException::COLLECT_KEY_CONFLICT, ['key' => 'aaa']);
        $this->assertEquals(4000, $exception->getCode());
        $this->assertEquals('Stream with name "aaa" already attached.', $exception->getMessage());
    }

    public function testCollectSelectException(): void
    {
        $exception = new StreamException(StreamException::COLLECT_SELECT_ERR);
        $this->assertEquals(4001, $exception->getCode());
        $this->assertEquals('Failed to select streams for reading.', $exception->getMessage());
    }

    public function testContextSetException(): void
    {
        $exception = new StreamException(StreamException::CONTEXT_SET_ERR);
        $this->assertEquals(5000, $exception->getCode());
        $this->assertEquals('Failed to set option/param on context.', $exception->getMessage());
    }

    public function testWithPrevious(): void
    {
        $previous = new StreamException(StreamException::STREAM_DETACHED);
        $exception = new StreamException(StreamException::NOT_READABLE, [], $previous);
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertEquals(1010, $exception->getCode());
        $this->assertEquals('Stream is not readable. (Stream is detached.)', $exception->getMessage());
    }
}