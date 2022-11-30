<?php

/**
 * File for Net\SocketServer class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net\Mock;

use ErrorException;
use Phrity\Util\ErrorHandler;
use Psr\Http\Message\{
    StreamInterface,
    UriInterface
};
use RuntimeException;

/**
 * Net\SocketServer class.
 */
class SocketServer
{
    private static $internet_schemes = ['tcp', 'udp', 'tls', 'ssl'];
    private static $unix_schemes = ['unix', 'udg'];

    private $handler;
    private $socket;

    /**
     * Create new socker server instance
     * UriInterface $uri The URI to open socket on.
     * int $flags Flags to set on socket.
     * @throws \RuntimeException if unable to create socket.
     */
    public function __construct(...$params)
    {
        Mock::callback('SocketServer.__construct', $params);
    }

    /**
     * Automatically close on destruct.
     */
    public function __destruct(...$params)
    {
        Mock::callback('SocketServer.__destruct', $params);
    }

    /**
     * Close this server socket.
     */
    public function close(...$params): void
    {
        Mock::callback('close.__destruct', $params);
    }

    /**
     * Accept a connection on a socket.
     * @param int|null $timeout Override the default socket accept timeout.
     * @return StreamInterface|null The stream for opened conenction.
     * @throws \RuntimeException if socket is closed
     */
    public function accept(...$params): ?StreamInterface
    {
        return Mock::callback('SocketServer.accept', $params);
    }

    /**
     * Retrieve list of registered socket transports.
     * @return array List of registered transports.
     */
    public function getTransports(...$params): array
    {
        return Mock::callback('SocketServer.getTransports', $params);
    }

    /**
     * Toggle blocking/non-blocking mode.
     * @param bool $enable Blocking mode to set.
     * @return bool If operation was succesful.
     * @throws \RuntimeException if socket is closed.
     */
    public function setBlocking(...$params): bool
    {
        return Mock::callback('SocketServer.setBlocking', $params);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata(...$params)
    {
        return Mock::callback('SocketServer.getMetadata', $params);
    }
}
