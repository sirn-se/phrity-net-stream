<?php

/**
 * Tests for Net\SocketServer class.
 * @package Phrity > Net > Stream
 */

declare(strict_types=1);

namespace Phrity\Net\Test;

use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    SocketServer,
    StreamException,
    Uri
};
use Phrity\Net\Test\SocketServerMock;

class SocketServerTest extends TestCase
{
    public function tearDown(): void
    {
        if (file_exists('/tmp/test.sock')) {
            unlink('/tmp/test.sock');
        }
    }

    public function testNonBlockingTcpServer(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8000');
        $server = new SocketServer($uri);
        $this->assertInstanceOf(SocketServer::class, $server);
        $this->assertTrue($server->isBlocking());
        $this->assertTrue($server->setBlocking(false));
        $this->assertFalse($server->isBlocking());
        $this->assertEquals([
            'timed_out' => false,
            'blocked' => false,
            'eof' => false,
            'stream_type' => 'tcp_socket/ssl',
            'mode' => 'r+',
            'unread_bytes' => 0,
            'seekable' => false,
        ], $server->getMetadata());
        $stream = $server->accept(0);
        $this->assertNull($stream); // Non-blocking, nothing to accept
        $server->close();
        $this->assertNull($server->getMetadata());
    }

    public function testNonBlockingUnixServer(): void
    {
        $uri = new Uri("unix:///tmp/test.sock");
        $server = new SocketServer($uri);
        $this->assertInstanceOf(SocketServer::class, $server);
        $this->assertTrue($server->setBlocking(false));
        $this->assertEquals([
            'timed_out' => false,
            'blocked' => false,
            'eof' => false,
            'stream_type' => 'unix_socket',
            'mode' => 'r+',
            'unread_bytes' => 0,
            'seekable' => false,
        ], $server->getMetadata());
        $stream = $server->accept(0);
        $this->assertNull($stream); // Non-blocking, nothing to accept
        $server->close();
    }

    public function testUnsupportedScheme(): void
    {
        $uri = new Uri('http://0.0.0.0:8000');
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::SCHEME_TRANSPORT);
        $this->expectExceptionMessage('Scheme "http" is not supported.');
        $server = new SocketServer($uri);
    }

    public function testUnknownScheme(): void
    {
        $uri = new Uri('fake://0.0.0.0:8000');
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::SCHEME_HANDLER);
        $this->expectExceptionMessage('Could not handle scheme "fake".');
        $server = new SocketServerMock($uri);
    }

    public function testCreateFailure(): void
    {
        $uri = new Uri('tcp://0.0.0.0');
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::SERVER_SOCKET_ERR);
        $this->expectExceptionMessage('Could not create socket for "tcp://0.0.0.0".');
        $server = new SocketServer($uri);
    }

    public function testBlockingServerTimeout(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8000');
        $server = new SocketServer($uri);
        $this->assertEquals([
            'timed_out' => false,
            'blocked' => true,
            'eof' => false,
            'stream_type' => 'tcp_socket/ssl',
            'mode' => 'r+',
            'unread_bytes' => 0,
            'seekable' => false,
        ], $server->getMetadata());
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::SERVER_ACCEPT_ERR);
        $this->expectExceptionMessage('Could not accept on socket.');
        $stream = $server->accept(0);
        $server->close();
    }

    public function testAcceptOnClosedError(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8000');
        $server = new SocketServer($uri);
        $server->close();
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::SERVER_CLOSED);
        $this->expectExceptionMessage('Server is closed.');
        $server->accept();
    }

    public function testSetBlockingOnClosedError(): void
    {
        $uri = new Uri('tcp://0.0.0.0:8000');
        $server = new SocketServer($uri);
        $server->close();
        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::SERVER_CLOSED);
        $this->expectExceptionMessage('Server is closed.');
        $server->setBlocking(true);
    }
}
