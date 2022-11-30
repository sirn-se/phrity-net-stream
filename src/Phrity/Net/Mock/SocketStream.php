<?php

/**
 * File for Net\SocketStream class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net\Mock;

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

}
