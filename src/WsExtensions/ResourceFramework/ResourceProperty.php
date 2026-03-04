<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\ResourceFramework;

/**
 * Represents an individual resource property (wsrf-rp:ResourceProperty).
 *
 * Provides a fluent interface for building resource properties with
 * modifiability and subscribability flags.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ResourceProperty
{
    private bool $modifiable = false;

    private bool $subscribable = false;

    /**
     * @param string     $name   Property name (QName)
     * @param string     $type   Property type
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly string $name,
        private readonly string $type,
        private readonly mixed $parent = null,
    ) {}

    /**
     * Set whether this property is modifiable.
     */
    public function modifiable(bool $modifiable = true): self
    {
        $this->modifiable = $modifiable;

        return $this;
    }

    /**
     * Set whether this property is subscribable.
     */
    public function subscribable(bool $subscribable = true): self
    {
        $this->subscribable = $subscribable;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isModifiable(): bool
    {
        return $this->modifiable;
    }

    public function isSubscribable(): bool
    {
        return $this->subscribable;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'modifiable' => $this->modifiable,
            'subscribable' => $this->subscribable,
        ];
    }

    /**
     * Return to parent or return config array.
     *
     * @return array<string, mixed>|mixed
     */
    public function end(): mixed
    {
        return $this->parent ?? $this->getConfig();
    }
}
