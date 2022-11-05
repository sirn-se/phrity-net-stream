<?php

/**
 * File for Net\Stream class.
 * @package Phrity > Net > Stream
 * @see https://www.php-fig.org/psr/psr-7/#34-psrhttpmessagestreaminterface
 */

namespace Phrity\Net;

use InvalidArgumentException;
use Phrity\Util\ErrorHandler;
use Psr\Http\Message\StreamInterface;
use SEEK_SET;
use RuntimeException;
use Throwable;

/**
 * Net\Stream class.
 */
class Stream implements StreamInterface
{
    private static $readmodes = ['r', 'r+', 'w+', 'a+', 'x+', 'c+'];
    private static $writemodes = ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'];

    private $stream;
    private $handler;
    private $readable = false;
    private $writable = false;
    private $seekable = false;

    /**
     * Create new stream wrapper instance
     * @param resource $resource A stream resource to wrap
     * @throws \InvalidArgumentException If not a valid stream resource
     */
    public function __construct($stream)
    {
        if (gettype($stream) !== 'resource') {
             throw new InvalidArgumentException('Invalid stream provided');
        }
        if (!in_array(get_resource_type($stream), ['stream', 'persistent stream'])) {
             throw new InvalidArgumentException('Invalid stream provided');
        }
        $this->stream = $stream;
        $this->handler = new ErrorHandler();

        $meta = $this->getMetadata();
        $mode = substr($meta['mode'], 0, 2);
        $this->readable = in_array($mode, self::$readmodes);
        $this->writable = in_array($mode, self::$writemodes);
        $this->seekable = $meta['seekable'];
    }


    // ---------- PSR-7 methods ---------------------------------------------------------------------------------------

    /**
     * Closes the stream and any underlying resources.
     * @return void
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            fclose($this->stream);
        }
        unset($this->stream);
        $this->readable = $this->writable = $this->seekable = false;
    }

    /**
     * Separates any underlying resources from the stream.
     * After the stream has been detached, the stream is in an unusable state.
     * @return resource|null Underlying stream, if any
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }
        $stream = $this->stream;
        unset($this->stream);
        $this->readable = $this->writable = $this->seekable = false;
        return $stream;
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
        if (!isset($this->stream)) {
            return null;
        }
        $meta = stream_get_meta_data($this->stream);
        if (isset($key)) {
            return array_key_exists($key, $meta) ? $meta[$key] : null;
        }
        return $meta;
    }

    /**
     * Returns the current position of the file read/write pointer
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        return $this->handler->with(function () {
            return ftell($this->stream);
        }, new RuntimeException('Failed tell() on stream'));
    }

    /**
     * Returns true if the stream is at the end of the stream.
     * @return bool
     */
    public function eof(): bool
    {
        return empty($this->stream) || feof($this->stream);
    }

    /**
     * Read data from the stream.
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable');
        }
        return $this->handler->with(function () use ($length) {
            return fread($this->stream, $length);
        }, new RuntimeException('Failed read() on stream'));
    }

    /**
     * Write data to the stream.
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->writable) {
            throw new RuntimeException('Stream is not writable');
        }
        return $this->handler->with(function () use ($string) {
            return fwrite($this->stream, $string);
        }, new RuntimeException('Failed write() on stream'));
    }

    /**
     * Get the size of the stream if known.
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(): ?int
    {
        if (!isset($this->stream)) {
            return null;
        }
        $stats = fstat($this->stream);
        return array_key_exists('size', $stats) ? $stats['size'] : null;
    }

    /**
     * Returns whether or not the stream is seekable.
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * Seek to a position in the stream.
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }
        $result = fseek($this->stream, $offset, $whence);
        if ($result !== 0) {
            throw new RuntimeException('Failed to seek');
        }
    }

    /**
     * Seek to the beginning of the stream.
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     * @throws \RuntimeException on failure.
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Returns whether or not the stream is writable.
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * Returns whether or not the stream is readable.
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * Returns the remaining contents in a string
     * @return string
     * @throws \RuntimeException if unable to read.
     * @throws \RuntimeException if error occurs while reading.
     */
    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }
        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable');
        }
        return $this->handler->with(function () {
            return stream_get_contents($this->stream);
        }, new RuntimeException('Failed getContents() on stream'));
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     * @return string
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }
            return $this->getContents();
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }
    }
}
