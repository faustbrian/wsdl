<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Discovery;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;

/**
 * Represents a WS-Discovery Hello message (service announcement).
 *
 * Sent by a service when it becomes available on the network.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Hello
{
    /**
     * @param array<string> $types Array of QNames representing service types
     * @param array<string> $xAddrs Array of transport addresses
     */
    public function __construct(
        private readonly EndpointReference $endpointReference,
        private readonly array $types = [],
        private readonly ?Scopes $scopes = null,
        private readonly array $xAddrs = [],
        private readonly int $metadataVersion = 1,
    ) {}

    /**
     * Create a Hello message with required parameters.
     *
     * @param array<string> $types
     * @param array<string> $xAddrs
     */
    public static function create(
        string $address,
        array $types = [],
        ?Scopes $scopes = null,
        array $xAddrs = [],
        int $metadataVersion = 1,
    ): self {
        return new self(
            new EndpointReference($address),
            $types,
            $scopes,
            $xAddrs,
            $metadataVersion,
        );
    }

    public function getEndpointReference(): EndpointReference
    {
        return $this->endpointReference;
    }

    /**
     * @return array<string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getScopes(): ?Scopes
    {
        return $this->scopes;
    }

    /**
     * @return array<string>
     */
    public function getXAddrs(): array
    {
        return $this->xAddrs;
    }

    public function getMetadataVersion(): int
    {
        return $this->metadataVersion;
    }
}
