<?php

namespace Phrity\Net;

/**
 * StreamContainerInterface.
 */
interface StreamContainerInterface
{
    /**
     * @return StreamInterface
     */
    public function getStream(): StreamInterface;

    /**
     * @return non-empty-string
     */
    public function getIdentity(): string;
}
