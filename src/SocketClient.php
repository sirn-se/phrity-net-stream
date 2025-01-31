<?php

namespace Phrity\Net;

use Phrity\Util\ErrorHandler;
use Psr\Http\Message\UriInterface;

/**
 * Phrity\Net\SocketClient class.
 */
class SocketClient
{
    protected UriInterface $uri;
    protected ErrorHandler $handler;
    protected bool $persistent = false;
    protected int|null $timeout = null;
    protected Context $context;

    /**
     * Create new socker server instance
     * @param UriInterface $uri The URI to open socket on.
     */
    public function __construct(UriInterface $uri, Context|null $context = null)
    {
        $this->uri = $uri;
        $this->context = $context ?? new Context();
        $this->handler = new ErrorHandler();
    }


    // ---------- Configuration ---------------------------------------------------------------------------------------

    /**
     * Set stream context.
     * @param Context|array|null $options
     * @param array|null $params
     * @return SocketClient
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
     * Set connection persistency.
     * @param bool $persistent
     * @return SocketClient
     */
    public function setPersistent(bool $persistent): self
    {
        $this->persistent = $persistent;
        return $this;
    }

    /**
     * Set timeout in seconds.
     * @param int|null $timeout
     * @return SocketClient
     */
    public function setTimeout(int|null $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }


    // ---------- Operations ------------------------------------------------------------------------------------------

    /**
     * Create a connection on remote socket.
     * @return SocketStream The stream for opened conenction.
     * @throws StreamException if connection could not be created
     */
    public function connect(): SocketStream
    {
        $stream = $this->handler->with(function () {
            $error_code = $error_message = '';
            return stream_socket_client(
                $this->uri->__toString(),
                $error_code,
                $error_message,
                $this->timeout,
                $this->persistent ? STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT : STREAM_CLIENT_CONNECT,
                $this->context->getResource()
            );
        }, new StreamException(StreamException::CLIENT_CONNECT_ERR, ['uri' => $this->uri]));
        return new SocketStream($stream);
    }
}
