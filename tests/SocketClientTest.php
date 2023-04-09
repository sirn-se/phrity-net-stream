<?php

declare(strict_types=1);

namespace Phrity\Net\Test;

use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    SocketClient,
    SocketStream,
};
use Phrity\Net\Test\SocketServerMock;
use Phrity\Net\Uri;
use RuntimeException;
use function hey as stream_socket_client;

class SocketClientTest extends TestCase
{
    public function tearDown(): void
    {
        if (file_exists('/tmp/test.sock')) {
            unlink('/tmp/test.sock');
        }
    }

    public function testClient(): void
    {
        $uri = new Uri('tcp://www.php.net:80');
        $client = new SocketClient($uri);
        $this->assertInstanceOf(SocketClient::class, $client);
        $this->assertSame($client, $client->setPersistent(true));
        $this->assertSame($client, $client->setTimeout(1));
        $this->assertSame($client, $client->setContext([]));

        $stream = $client->connect();
        $this->assertInstanceOf(SocketStream::class, $stream);
        $stream->close();
    }

    public function testClientConnectFailure(): void
    {
        $uri = new Uri('tcp://localhost:80');
        $client = new SocketClient($uri);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not create connection for 'tcp://localhost:80'.");
        $client->connect();
    }
}
