<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\ResourceFramework;

/**
 * Represents a WS-ResourceProperties GetResourceProperty request.
 *
 * Provides a fluent interface for building property retrieval requests.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class GetResourceProperty
{
    /**
     * @param string     $resourceProperty Property name (QName)
     * @param null|mixed $parent           Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly string $resourceProperty,
        private readonly mixed $parent = null,
    ) {}

    public function getResourceProperty(): string
    {
        return $this->resourceProperty;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'resourceProperty' => $this->resourceProperty,
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
