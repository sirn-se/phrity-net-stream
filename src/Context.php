<?php

namespace Phrity\Net;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Context class.
 */
class Context
{
    private $stream;

    /**
     * Create exception.
     * @param open-resource|null $stream
     * @throws InvalidArgumentException if incorrect resource
     */
    public function __construct(mixed $stream = null)
    {
        if (is_null($stream)) {
            $stream = stream_context_create();
        }
        $type = gettype($stream);
        if ($type !== 'resource') {
             throw new InvalidArgumentException("Invalid stream provided; got type '{$type}'.");
        }
        $rtype = get_resource_type($stream);
        if (!in_array($rtype, ['stream', 'persistent stream', 'stream-context'])) {
             throw new InvalidArgumentException("Invalid stream provided; got resource type '{$rtype}'.");
        }
        $this->stream = $stream;
    }

    public function getOption(string $wrapper, string $option): mixed
    {
        return stream_context_get_options($this->stream)[$wrapper][$option] ?? null;
    }

    public function getOptions(): array
    {
        return stream_context_get_options($this->stream);
    }

    public function setOption(string $wrapper, string $option, mixed $value): self
    {
        if (!is_resource($this->stream) || !stream_context_set_option($this->stream, $wrapper, $option, $value)) {
            throw new StreamException(StreamException::CONTEXT_SET_ERR);
        }
        return $this;
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $wrapper => $wrapperOptions) {
            foreach ($wrapperOptions as $option => $value) {
                $this->setOption($wrapper, $option, $value);
            }
        }
        return $this;
    }

    public function getParam(string $param): mixed
    {
        return stream_context_get_params($this->stream)[$param] ?? null;
    }

    public function getParams(): array
    {
        return stream_context_get_params($this->stream);
    }

    public function setParam(string $param, mixed $value): self
    {
        $this->setParams([$param => $value]);
        return $this;
    }

    public function setParams(array $params): self
    {
        /** @phpstan-ignore booleanNot.alwaysFalse */
        if (!is_resource($this->stream) || !stream_context_set_params($this->stream, $params)) {
            throw new StreamException(StreamException::CONTEXT_SET_ERR);
        }
        return $this;
    }

    public function getResource(): mixed
    {
        return $this->stream;
    }
}
