<?php

namespace Phrity\Net;

use ErrorException;
use Phrity\Util\ErrorHandler;
use Psr\Http\Message\UriInterface;

/**
 * SocketServer class.
 */
class SocketServer extends Stream
{
    private static array $internet_schemes = ['tcp', 'udp', 'tls', 'ssl'];
    private static array $unix_schemes = ['unix', 'udg'];

    protected ErrorHandler $handler;
    protected string $address;
    protected $stream;
    protected Context $context;

    /**
     * Create new socker server instance
     * @param UriInterface $uri The URI to open socket on.
     * @throws StreamException if unable to create socket.
     */
    public function __construct(UriInterface $uri, Context|null $context = null)
    {
        $this->handler = new ErrorHandler();
        if (!in_array($uri->getScheme(), $this->getTransports())) {
            throw new StreamException(StreamException::SCHEME_TRANSPORT, ['scheme' => $uri->getScheme()]);
        }
        if (in_array(substr($uri->getScheme(), 0, 3), self::$internet_schemes)) {
            $this->address = "{$uri->getScheme()}://{$uri->getAuthority()}";
        } elseif (in_array($uri->getScheme(), self::$unix_schemes)) {
            $this->address = "{$uri->getScheme()}://{$uri->getPath()}";
        } else {
            throw new StreamException(StreamException::SCHEME_HANDLER, ['scheme' => $uri->getScheme()]);
        }
        $this->context = $context ?? new Context();
        $this->stream = $this->handler->with(function () {
            $error_code = $error_message = '';
            return stream_socket_server(
                $this->address,
                $error_code,
                $error_message,
                STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                $this->context->getResource()
            );
        }, new StreamException(StreamException::SERVER_SOCKET_ERR, ['uri' => $uri->__toString()]));
        $this->evalStream();
    }


    // ---------- Configuration ---------------------------------------------------------------------------------------

    /**
     * Set stream context.
     * @param Context|array|null $options
     * @param array|null $params
     * @return static
     */
    public function setContext(Context|array|null $options = null, array|null $params = null): self
    {
        if ($options instanceof Context) {
            $this->context = $options;
            return $this;
        }
        // @deprecated
        // @todo Add deprecation warning
        $this->context->setOptions($options ?? []);
        $this->context->setParams($params ?? []);
        return $this;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * Retrieve list of registered socket transports.
     * @return array List of registered transports.
     */
    public function getTransports(): array
    {
        return stream_get_transports();
    }

    /**
     * If server is in blocking mode.
     * @return bool|null
     */
    public function isBlocking(): bool|null
    {
        return $this->getMetadata('blocked');
    }

    /**
     * Toggle blocking/non-blocking mode.
     * @param bool $enable Blocking mode to set.
     * @return bool If operation was succesful.
     * @throws StreamException if socket is closed.
     */
    public function setBlocking(bool $enable): bool
    {
        if (!isset($this->stream)) {
            throw new StreamException(StreamException::SERVER_CLOSED);
        }
        return stream_set_blocking($this->stream, $enable);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata(string|null $key = null): mixed
    {
        if (!isset($this->stream)) {
            return null;
        }
        // Add URI default for version compability
        $meta = array_merge([
            'uri' => $this->address,
        ], stream_get_meta_data($this->stream));
        if (isset($key)) {
            return array_key_exists($key, $meta) ? $meta[$key] : null;
        }
        return $meta;
    }


    // ---------- Operations ------------------------------------------------------------------------------------------

    /**
     * Accept a connection on a socket.
     * @param int|null $timeout Override the default socket accept timeout.
     * @return SocketStream|null The stream for opened conenction.
     * @throws StreamException if socket is closed
     */
    public function accept(int|null $timeout = null): SocketStream|null
    {
        if (!isset($this->stream)) {
            throw new StreamException(StreamException::SERVER_CLOSED);
        }
        $stream = $this->handler->with(function () use ($timeout) {
            $peer_name = '';
            return stream_socket_accept($this->stream, $timeout, $peer_name);
        }, function (ErrorException $e) {
            // If non-blocking mode, don't throw error on time out
            if ($this->getMetadata('blocked') === false && substr_count($e->getMessage(), 'timed out') > 0) {
                return null;
            }
            throw new StreamException(StreamException::SERVER_ACCEPT_ERR);
        });
        return $stream ? new SocketStream($stream) : null;
    }
}
