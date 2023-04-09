<?php

declare(strict_types=1);

namespace Phrity\Net\Test;

use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    SocketClient,
};
use Phrity\Net\Test\SocketServerMock;
use Phrity\Net\Uri;
use RuntimeException;

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
        $uri = new Uri('tcp://127.0.0.1:8000');
        $client = new SocketClient($uri);
        $this->assertInstanceOf(SocketClient::class, $client);
        $this->assertSame($client, $client->setPersistent(true));
        $this->assertSame($client, $client->setTimeout(1));

        // Will always fail
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Could not create connection for 'tcp://127.0.0.1:8000'.");
        $client->connect();
    }
}
