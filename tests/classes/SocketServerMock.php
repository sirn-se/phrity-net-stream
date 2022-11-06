<?php

/**
 * Tests for Net\SocketServer class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net;

/**
 * Net\SocketServerMock class.
 */
class SocketServerMock extends SocketServer
{
    // Faking transports
    public function getTransports(): array
    {
        return [
            'fake',
        ];
    }
}
