<?php

namespace Phrity\Net;

use RuntimeException;

/**
 * Phrity\Net\SocketStream class.
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
     * Get name of local socket, or null if not connected.
     * @return string|null
     */
    public function getLocalName(): ?string
    {
        return stream_socket_get_name($this->stream, false);
    }

    /**
     * Get type of stream resoucre.
     * @return string
     */
    public function getResourceType(): string
    {
        return $this->stream ? get_resource_type($this->stream) : '';
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

    /**
     * Set timeout period on a stream.
     * @param int $seconds Seconds to be set.
     * @param int $microseconds Microseconds to be set.
     * @return bool If operation was succesful.
     * @throws \RuntimeException if stream is closed.
     */
    public function setTimeout(int $seconds, int $microseconds = 0): bool
    {
        if (!isset($this->stream)) {
            throw new RuntimeException("Stream is detached.");
        }
        return stream_set_timeout($this->stream, $seconds, $microseconds);
    }

    /**
     * Read line from the stream.
     * @param int $length Read up to $length bytes from the object and return them.
     * @return string|null Returns the data read from the stream, or null of eof.
     * @throws \RuntimeException if an error occurs.
     */
    public function readLine(int $length): ?string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached.');
        }
        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable.');
        }
        return $this->handler->with(function () use ($length) {
            $result = fgets($this->stream, $length);
            return $result === false ? null : $result;
        }, new RuntimeException('Failed gets() on stream.'));
    }
}
