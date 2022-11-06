<?php

/**
 * File for Net\SocketServer class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net;

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
    public function __construct(UriInterface $uri, int $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN)
    {
        $this->handler = new ErrorHandler();
        if (!in_array($uri->getScheme(), $this->getTransports())) {
            throw new RuntimeException("Scheme '{$uri->getScheme()}' is not supported.");
        }
        if (in_array(substr($uri->getScheme(), 0, 3), self::$internet_schemes)) {
            $address = "{$uri->getScheme()}://{$uri->getAuthority()}";
        } elseif (in_array($uri->getScheme(), self::$unix_schemes)) {
            $address = "{$uri->getScheme()}://{$uri->getPath()}";
        } else {
            throw new RuntimeException("Could not handle scheme '{$uri->getScheme()}'.");
        }
        $this->socket = $this->handler->with(function () use ($address, $flags) {
            $error_code = $error_message = '';
            return stream_socket_server($address, $error_code, $error_message, $flags);
        }, new RuntimeException("Could not create socket for '{$uri}'."));
    }

    /**
     * Automatically close on destruct.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close this server socket.
     */
    public function close(): void
    {
        if (isset($this->socket)) {
            fclose($this->socket);
            unset($this->socket);
        }
    }

    /**
     * Accept a connection on a socket.
     * @param int $timeout Override the default socket accept timeout.
     * @return \Phrity\Net\SocketStream The stream for opened conenction.
     * @throws \RuntimeException if socket is closed
     */
    public function accept(?int $timeout = null): ?StreamInterface
    {
        if (!isset($this->socket)) {
            throw new RuntimeException("Server is closed.");
        }
        return $this->handler->with(function () use ($timeout) {
            $peer_name = '';
            return stream_socket_accept($this->socket, $timeout, $peer_name);
        }, function (ErrorException $e) {
            // If non-blocking mode, don't throw error on time out
            if ($this->getMetadata('blocked') === false && substr_count($e->getMessage(), 'timed out') > 0) {
                return null;
            }
            throw new RuntimeException("Could not accept on socket.");
        });
    }

    /**
     * Retrieve list of registered socket transports.
     * @return array List of registered transports.
     */
    public function getTransports(): array
    {
        return stream_get_transports();
    }

    /**
     * Toggle blocking/non-blocking mode.
     * @param bool $enable Blocking mode to set.
     * @return bool If operation was succesful.
     * @throws \RuntimeException if socket is closed.
     */
    public function setBlocking(bool $enable): bool
    {
        if (!isset($this->socket)) {
            throw new RuntimeException("Server is closed.");
        }
        return stream_set_blocking($this->socket, $enable);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->socket)) {
            return null;
        }
        $meta = stream_get_meta_data($this->socket);
        if (isset($key)) {
            return array_key_exists($key, $meta) ? $meta[$key] : null;
        }
        return $meta;
    }
}
