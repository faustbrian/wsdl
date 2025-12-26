<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\MetadataExchange;

/**
 * Represents a WS-MetadataExchange metadata section.
 *
 * Contains a single piece of metadata (WSDL, XSD, Policy, etc.) with its dialect identifier.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class MetadataSection
{
    /**
     * @param string $dialect Metadata dialect URI
     * @param mixed $content The actual metadata content (WSDL, XSD, Policy, etc.)
     * @param string|null $identifier Optional identifier for this metadata section
     */
    public function __construct(
        public string $dialect,
        public mixed $content,
        public ?string $identifier = null,
    ) {}

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'dialect' => $this->dialect,
            'content' => $this->content,
        ];

        if ($this->identifier !== null) {
            $result['identifier'] = $this->identifier;
        }

        return $result;
    }
}
