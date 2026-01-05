<?php

/**
 * Tests for Net\SocketServer class.
 * @package Phrity > Net > Stream
 */

namespace Phrity\Net\Test;

use Phrity\Net\{
    Stream,
    StreamContainerInterface,
    StreamFactory,
};

class StreamContainer implements StreamContainerInterface
{
    public function getStream(): Stream
    {
        $factory = new StreamFactory();
        return $factory->createStream('This is a temporary test stream');
    }

    public function getIdentity(): string
    {
        return 'test-stream-container';
    }
}
