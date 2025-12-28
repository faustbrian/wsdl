<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Addressing;

/**
 * Represents WS-Addressing metadata (wsa:Metadata).
 *
 * Provides a fluent interface for adding metadata items to an endpoint.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Metadata
{
    /** @var array<int, array{namespace: string, localName: string, content: mixed}> */
    private array $items = [];

    public function __construct(
        private readonly EndpointReference $parent,
    ) {}

    /**
     * Add a metadata item.
     */
    public function add(string $namespace, string $localName, mixed $content): self
    {
        $this->items[] = [
            'namespace' => $namespace,
            'localName' => $localName,
            'content' => $content,
        ];

        return $this;
    }

    /**
     * Get all metadata items.
     *
     * @return array<int, array{namespace: string, localName: string, content: mixed}>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Return to the parent endpoint reference.
     */
    public function end(): EndpointReference
    {
        return $this->parent;
    }
}
