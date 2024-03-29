<?php

/**
 * Tests for Net\SocketServer class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net\Test;

use Phrity\Net\SocketServer;

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
