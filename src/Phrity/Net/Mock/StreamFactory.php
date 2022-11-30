<?php

/**
 * File for Net\StreamFactory class.
 * @package Phrity > Net > Stream
 * @see https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface
 */

namespace Phrity\Net\Mock;

use Psr\Http\Message\StreamFactoryInterface;

/**
 * Net\StreamFactory class.
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * Create new stream wrapper instance
     */
    public function __construct(...$params)
    {
        return Mock::callback('StreamFactory.__construct', $params);
    }

    /**
     * Create a new stream from a string.
     * @param string $content String content with which to populate the stream.
     * @return \Phrity\Net\Stream A stream instance
     */
    public function createStream(...$params): Stream
    {
        return Mock::callback('StreamFactory.createStream', $params);
    }

    /**
     * Create a stream from an existing file.
     * @param string $filename The filename or stream URI to use as basis of stream.
     * @param string $mode The mode with which to open the underlying filename/stream.
     * @throws \RuntimeException If the file cannot be opened.
     * @throws \InvalidArgumentException If the mode is invalid.
     * @return \Phrity\Net\Stream A stream instance
     */
    public function createStreamFromFile(...$params): Stream
    {
        return Mock::callback('StreamFactory.createStreamFromFile', $params);
    }

    /**
     * Create a new stream from an existing resource.
     * The stream MUST be readable and may be writable.
     * @param resource $resource The PHP resource to use as the basis for the stream.
     * @return \Phrity\Net\Stream A stream instance
     */
    public function createStreamFromResource(...$params): Stream
    {
        return Mock::callback('StreamFactory.createStreamFromResource', $params);
    }

    /**
     * Create a new socket server.
     * @param UriInterface $uri The URI to create server on.
     * @param int $flags Flags to set on socket.
     * @return \Phrity\Net\SocketServer A socket server instance
     */
    public function createSocketServer(...$params): SocketServer {
        return Mock::callback('StreamFactory.createSocketServer', $params);
    }
}
