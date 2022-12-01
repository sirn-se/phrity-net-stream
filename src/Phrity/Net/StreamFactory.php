<?php

/**
 * File for Net\StreamFactory class.
 * @package Phrity > Net > Stream
 * @see https://www.php-fig.org/psr/psr-17/#24-streamfactoryinterface
 */

namespace Phrity\Net;

use InvalidArgumentException;
use Phrity\Util\ErrorHandler;
use Psr\Http\Message\{
    StreamFactoryInterface,
    UriInterface
};
use RuntimeException;

/**
 * Net\StreamFactory class.
 */
class StreamFactory implements StreamFactoryInterface
{
    private static $modes = ['r', 'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+', 'e'];

    private $handler;

    /**
     * Create new stream wrapper instance.
     */
    public function __construct()
    {
        $this->handler = new ErrorHandler();
    }


    // ---------- PSR-17 methods --------------------------------------------------------------------------------------

    /**
     * Create a new stream from a string.
     * @param string $content String content with which to populate the stream.
     * @return \Phrity\Net\Stream A stream instance.
     */
    public function createStream(string $content = ''): Stream
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        return $this->createStreamFromResource($resource);
    }

    /**
     * Create a stream from an existing file.
     * @param string $filename The filename or stream URI to use as basis of stream.
     * @param string $mode The mode with which to open the underlying filename/stream.
     * @throws \RuntimeException If the file cannot be opened.
     * @throws \InvalidArgumentException If the mode is invalid.
     * @return \Phrity\Net\Stream A stream instance.
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): Stream
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("File '{$filename}' do not exist.");
        }
        if (!in_array($mode, self::$modes)) {
            throw new InvalidArgumentException("Invalid mode '{$mode}'.");
        }
        return $this->handler->with(function () use ($filename, $mode) {
            $resource = fopen($filename, $mode);
            return $this->createStreamFromResource($resource);
        }, new RuntimeException("Could not open '{$filename}'."));
    }

    /**
     * Create a new stream from an existing resource.
     * The stream MUST be readable and may be writable.
     * @param resource $resource The PHP resource to use as the basis for the stream.
     * @return \Phrity\Net\Stream A stream instance.
     */
    public function createStreamFromResource($resource): Stream
    {
        return new Stream($resource);
    }


    // ---------- Extensions ------------------------------------------------------------------------------------------

    /**
     * Create a new ocket stream from an existing resource.
     * The stream MUST be readable and may be writable.
     * @param resource $resource The PHP resource to use as the basis for the stream.
     * @return \Phrity\Net\SocketStream A socket stream instance.
     */
    public function createSocketStreamFromResource($resource): SocketStream
    {
        return new SocketStream($resource);
    }

    /**
     * Create a new socket server.
     * @param \Psr\Http\Message\UriInterface $uri The URI to create server on.
     * @param int $flags Flags to set on socket.
     * @return \Phrity\Net\SocketServer A socket server instance.
     */
    public function createSocketServer(
        UriInterface $uri,
        int $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
    ): SocketServer {
        return new SocketServer($uri, $flags);
    }
}
