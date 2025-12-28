<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\MetadataExchange;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Addressing\Metadata;
use Cline\WsdlBuilder\WsExtensions\Addressing\ReferenceParameters;

/**
 * Represents a WS-MetadataExchange metadata reference.
 *
 * Provides a reference to external metadata that can be retrieved from another endpoint.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MetadataReference
{
    /**
     * @param EndpointReference    $address             The endpoint where metadata can be retrieved
     * @param array<string, mixed> $referenceProperties Optional reference properties
     */
    public function __construct(
        private readonly EndpointReference $address,
        private array $referenceProperties = [],
    ) {}

    /**
     * Add a reference property.
     */
    public function addReferenceProperty(string $name, mixed $value): self
    {
        $this->referenceProperties[$name] = $value;

        return $this;
    }

    /**
     * Get the endpoint address.
     */
    public function getAddress(): EndpointReference
    {
        return $this->address;
    }

    /**
     * Get all reference properties.
     *
     * @return array<string, mixed>
     */
    public function getReferenceProperties(): array
    {
        return $this->referenceProperties;
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $result = [
            'address' => [
                'address' => $this->address->getAddress(),
            ],
        ];

        if ($this->address->getReferenceParameters() instanceof ReferenceParameters) {
            $result['address']['referenceParameters'] = $this->address->getReferenceParameters();
        }

        if ($this->address->getMetadata() instanceof Metadata) {
            $result['address']['metadata'] = $this->address->getMetadata();
        }

        if ($this->referenceProperties !== []) {
            $result['referenceProperties'] = $this->referenceProperties;
        }

        return $result;
    }
}
