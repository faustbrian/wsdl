<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Addressing;

/**
 * Represents a WS-Addressing endpoint reference (wsa:EndpointReference).
 *
 * Provides a fluent interface for building endpoint references with
 * reference parameters and metadata.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class EndpointReference
{
    private ?ReferenceParameters $referenceParameters = null;

    private ?Metadata $metadata = null;

    public function __construct(
        private readonly string $address,
    ) {}

    /**
     * Add reference parameters to this endpoint reference.
     */
    public function referenceParameters(): ReferenceParameters
    {
        if ($this->referenceParameters === null) {
            $this->referenceParameters = new ReferenceParameters($this);
        }

        return $this->referenceParameters;
    }

    /**
     * Add metadata to this endpoint reference.
     */
    public function metadata(): Metadata
    {
        if ($this->metadata === null) {
            $this->metadata = new Metadata($this);
        }

        return $this->metadata;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getReferenceParameters(): ?ReferenceParameters
    {
        return $this->referenceParameters;
    }

    public function getMetadata(): ?Metadata
    {
        return $this->metadata;
    }
}
