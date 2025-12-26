<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\MetadataExchange;

/**
 * Represents a WS-MetadataExchange Metadata response container.
 *
 * Contains a collection of metadata sections returned in response to a GetMetadata request.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Metadata
{
    /**
     * @param array<MetadataSection> $metadataSections Collection of metadata sections
     */
    public function __construct(
        private array $metadataSections = [],
    ) {}

    /**
     * Add a metadata section to this container.
     */
    public function addSection(MetadataSection $section): self
    {
        $this->metadataSections[] = $section;

        return $this;
    }

    /**
     * Get all metadata sections.
     *
     * @return array<MetadataSection>
     */
    public function getSections(): array
    {
        return $this->metadataSections;
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'metadataSections' => \array_map(
                static fn (MetadataSection $section): array => $section->toArray(),
                $this->metadataSections,
            ),
        ];
    }
}
