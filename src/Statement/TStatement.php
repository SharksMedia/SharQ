<?php

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

trait TStatement
{
    private ?string $identifier = null;

    public function identify(string $class): void
    {
        $this->identifier = $class;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function getClass(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }
}
