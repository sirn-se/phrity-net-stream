<?php

/**
 * File for Net\SocketStream class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net;

use RuntimeException;

/**
 * Net\SocketStream class.
 */
class SocketStream extends Stream
{
    /**
     * Get name of remote socket, or null if not connected.
     * @return string|null
     */
    public function getRemoteName(): ?string
    {
        return stream_socket_get_name($this->stream, true);
    }

    /**
     * If stream is in blocking mode.
     * @return bool|null
     */
    public function isBlocking(): ?bool
    {
        return $this->getMetadata('blocked');
    }

    /**
     * Toggle blocking/non-blocking mode.
     * @param bool $enable Blocking mode to set.
     * @return bool If operation was succesful.
     * @throws \RuntimeException if stream is closed.
     */
    public function setBlocking(bool $enable): bool
    {
        if (!isset($this->stream)) {
            throw new RuntimeException("Stream is detached.");
        }
        return stream_set_blocking($this->stream, $enable);
    }

    public function setTimeout(int $seconds, int $microseconds = 0): bool
    {
        return stream_set_timeout($this->stream, $seconds, $microseconds);
    }
}
