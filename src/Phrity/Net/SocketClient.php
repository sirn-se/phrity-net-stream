<?php

/**
 * File for Net\SocketServer class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net;

use Phrity\Util\ErrorHandler;
use Psr\Http\Message\{
    StreamInterface,
    UriInterface
};
use RuntimeException;

/**
 * Net\SocketClient class.
 */
class SocketClient
{
    protected $handler;

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

    /**
     * Create a connection on remote socket.
     * @return \Psr\Http\Message\StreamInterface|null The stream for opened conenction.
     * @throws \RuntimeException if connection could not be created
     */
    public function connect(): ?SocketStream
    {
        $stream = $this->handler->with(function () {
            $error_code = $error_message = '';
            return stream_socket_client($this->uri->__toString(), $error_code, $error_message);
        }, new RuntimeException("Could not create connection for '{$this->uri}'."));
        return $stream ? new SocketStream($stream) : null;
    }
}
