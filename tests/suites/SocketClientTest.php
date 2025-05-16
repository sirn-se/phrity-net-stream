<?php

declare(strict_types=1);

namespace Phrity\Net\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Phrity\Net\{
    Context,
    SocketClient,
    SocketStream,
    StreamException,
    Uri
};

class SocketClientTest extends TestCase
{
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

        $this->expectException(StreamException::class);
        $this->expectExceptionCode(StreamException::CLIENT_CONNECT_ERR);
        $this->expectExceptionMessage('Client could not connect to "tcp://localhost:80".');
        $client->connect();
    }

    public function testInvalidTimeout(): void
    {
        $uri = new Uri('tcp://www.php.net:80');
        $client = new SocketClient($uri);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be 0 or more.');
        $client->setTimeout(-1);
    }

    public function testContext(): void
    {
        $uri = new Uri('tcp://www.php.net:80');
        $context = new Context();
        $context->setOption('a', 'b', 'c');
        $client = new SocketClient($uri, $context);
        $this->assertSame($context, $client->getContext());
        $this->assertEquals('c', $client->getContext()->getOption('a', 'b'));
        $client->setContext($context);
        $this->assertSame($context, $client->getContext());
        $this->assertEquals('c', $client->getContext()->getOption('a', 'b'));
        $stream = $client->connect();
        $this->assertNotSame($context, $stream->getContext());
        $this->assertEquals('c', $stream->getContext()->getOption('a', 'b'));
        $stream->close();
    }
}
