<?php

namespace Phrity\Net;

use Phrity\Util\ErrorHandler;
use Psr\Http\Message\{
    StreamInterface,
    UriInterface
};
use RuntimeException;

/**
 * Phrity\Net\SocketClient class.
 */
class SocketClient
{
    protected $handler;
    protected $persistent = false;
    protected $timeout = null;
    protected $context = null;

    /**
     * Create new socker server instance
     * \Psr\Http\Message\UriInterface $uri The URI to open socket on.
     * int $flags Flags to set on socket.
     * @throws \RuntimeException if unable to create socket.
     */
    public function __construct(UriInterface $uri)
    {
        $this->uri = $uri;
        $this->handler = new ErrorHandler();
    }


    // ---------- Configuration ---------------------------------------------------------------------------------------

    /**
     * Set connection persistency.
     * $param bool $persistent
     * @return \Phrity\Net\SocketClient
     */
    public function setPersistent(bool $persistent): self
    {
        $this->persistent = $persistent;
        return $this;
    }

    /**
     * Set timeout in seconds.
     * $param int|null $timeout
     * @return \Phrity\Net\SocketClient
     */
    public function setTimeout(?int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Set stream context.
     * $param int|null $timeout
     * @return \Phrity\Net\SocketClient
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }


    // ---------- Operations ------------------------------------------------------------------------------------------

    /**
     * Create a connection on remote socket.
     * @return \Phrity\Net\SocketStream|null The stream for opened conenction.
     * @throws \RuntimeException if connection could not be created
     */
    public function connect(): ?SocketStream
    {
        $stream = $this->handler->with(function () {
            $error_code = $error_message = '';
            return stream_socket_client(
                $this->uri->__toString(),
                $error_code,
                $error_message,
                $this->timeout,
                $this->persistent ? STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT : STREAM_CLIENT_CONNECT,
                $this->context
            );
        }, new RuntimeException("Could not create connection for '{$this->uri}'."));
        return $stream ? new SocketStream($stream) : null;
    }


}
