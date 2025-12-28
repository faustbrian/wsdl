<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\ResourceFramework;

/**
 * Represents a WS-ResourceProperties SetResourceProperties request.
 *
 * Provides a fluent interface for building property modification requests
 * with insert, update, and delete operations.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SetResourceProperties
{
    /** @var array<array<string, mixed>> */
    private array $insert = [];

    /** @var array<array<string, mixed>> */
    private array $update = [];

    /** @var array<string> */
    private array $delete = [];

    /**
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly mixed $parent = null,
    ) {}

    /**
     * Add a property to insert.
     *
     * @param string $name  Property name (QName)
     * @param mixed  $value Property value
     */
    public function insert(string $name, mixed $value): self
    {
        $this->insert[] = [
            'name' => $name,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Add a property to update.
     *
     * @param string $name  Property name (QName)
     * @param mixed  $value Property value
     */
    public function update(string $name, mixed $value): self
    {
        $this->update[] = [
            'name' => $name,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Add a property to delete.
     *
     * @param string $name Property name (QName)
     */
    public function delete(string $name): self
    {
        $this->delete[] = $name;

        return $this;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getInsert(): array
    {
        return $this->insert;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function getUpdate(): array
    {
        return $this->update;
    }

    /**
     * @return array<string>
     */
    public function getDelete(): array
    {
        return $this->delete;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'insert' => $this->insert,
            'update' => $this->update,
            'delete' => $this->delete,
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
