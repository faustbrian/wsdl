<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\ResourceFramework;

/**
 * Represents a WS-ResourceProperties document (wsrf-rp:ResourceProperties).
 *
 * Provides a fluent interface for building resource properties documents with
 * properties and query expression dialects.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ResourceProperties
{
    /**
     * @var array<ResourceProperty>
     */
    private array $properties = [];

    /**
     * @var array<string>
     */
    private array $queryExpressionDialects = [];

    /**
     * @param mixed|null $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly mixed $parent = null,
    ) {}

    /**
     * Add a property to this resource properties document.
     *
     * @param string $name Property name (QName)
     * @param string $type Property type
     */
    public function addProperty(string $name, string $type): ResourceProperty
    {
        $property = new ResourceProperty($name, $type, $this);
        $this->properties[] = $property;

        return $property;
    }

    /**
     * Add a query expression dialect.
     *
     * @param string $dialect Query expression dialect URI
     */
    public function addQueryExpressionDialect(string $dialect): self
    {
        $this->queryExpressionDialects[] = $dialect;

        return $this;
    }

    /**
     * @return array<ResourceProperty>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return array<string>
     */
    public function getQueryExpressionDialects(): array
    {
        return $this->queryExpressionDialects;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'properties' => \array_map(
                static fn (ResourceProperty $property): array => $property->getConfig(),
                $this->properties,
            ),
            'queryExpressionDialects' => $this->queryExpressionDialects,
        ];
    }

    /**
     * Return to parent or return config array.
     *
     * @return mixed|array<string, mixed>
     */
    public function end(): mixed
    {
        return $this->parent ?? $this->getConfig();
    }
}
