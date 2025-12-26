<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\MetadataExchange;

use Cline\WsdlBuilder\WsExtensions\MetadataExchange\Enums\MetadataDialect;

/**
 * Represents a WS-MetadataExchange GetMetadata request.
 *
 * Used to request specific types of metadata from a service endpoint.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class GetMetadata
{
    /**
     * @param MetadataDialect $dialect Type of metadata requested
     * @param string|null $identifier Optional identifier for specific metadata
     */
    public function __construct(
        public MetadataDialect $dialect,
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
            'dialect' => $this->dialect->value,
        ];

        if ($this->identifier !== null) {
            $result['identifier'] = $this->identifier;
        }

        return $result;
    }
}
