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
    StreamInterface
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
     * Create new stream wrapper instance
     */
    public function __construct()
    {
        $this->handler = new ErrorHandler();
    }

    /**
     * Create a new stream from a string.
     * @param string $content String content with which to populate the stream.
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        return new Stream($resource);
    }

    /**
     * Create a stream from an existing file.
     * @param string $filename The filename or stream URI to use as basis of stream.
     * @param string $mode The mode with which to open the underlying filename/stream.
     * @throws \RuntimeException If the file cannot be opened.
     * @throws \InvalidArgumentException If the mode is invalid.
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if (!file_exists($filename)) {
            throw new RuntimeException("File '{$filename}' do not exist.");
        }
        if (!in_array($mode, self::$modes)) {
            throw new InvalidArgumentException("Invalid mode '{$mode}'.");
        }
        return $this->handler->with(function () use ($filename, $mode) {
            $resource = fopen($filename, $mode);
            return new Stream($resource);
        }, new RuntimeException("Could not open '{$filename}'."));
    }

    /**
     * Create a new stream from an existing resource.
     * The stream MUST be readable and may be writable.
     * @param resource $resource The PHP resource to use as the basis for the stream.
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
