<?php

namespace Phrity\Net;

/**
 * SelectableInterface.
 */
interface SelectableInterface extends StreamContainerInterface
{
    /**
     * @return non-empty-string
     */
    public function getIdentity(): string;

    public function onError(): void;
    public function onSelect(): void;
}
