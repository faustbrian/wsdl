<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\ResourceFramework;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;

/**
 * Represents a WS-Resource (wsrf-r:Resource).
 *
 * Provides a fluent interface for building stateful web resources with
 * properties, lifetime management, and endpoint references.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Resource
{
    private ?ResourceProperties $resourceProperties = null;

    private ?ResourceLifetime $resourceLifetime = null;

    /**
     * @param mixed|null $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly EndpointReference $endpointReference,
        private readonly mixed $parent = null,
    ) {}

    /**
     * Add resource properties to this resource.
     */
    public function resourceProperties(): ResourceProperties
    {
        if ($this->resourceProperties === null) {
            $this->resourceProperties = new ResourceProperties($this);
        }

        return $this->resourceProperties;
    }

    /**
     * Add lifetime management to this resource.
     */
    public function resourceLifetime(): ResourceLifetime
    {
        if ($this->resourceLifetime === null) {
            $this->resourceLifetime = new ResourceLifetime($this);
        }

        return $this->resourceLifetime;
    }

    public function getEndpointReference(): EndpointReference
    {
        return $this->endpointReference;
    }

    public function getResourceProperties(): ?ResourceProperties
    {
        return $this->resourceProperties;
    }

    public function getResourceLifetime(): ?ResourceLifetime
    {
        return $this->resourceLifetime;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = [
            'endpointReference' => [
                'address' => $this->endpointReference->getAddress(),
            ],
        ];

        if ($this->endpointReference->getReferenceParameters() !== null) {
            $config['endpointReference']['referenceParameters'] = $this->endpointReference->getReferenceParameters();
        }

        if ($this->endpointReference->getMetadata() !== null) {
            $config['endpointReference']['metadata'] = $this->endpointReference->getMetadata();
        }

        if ($this->resourceProperties !== null) {
            $config['resourceProperties'] = $this->resourceProperties->getConfig();
        }

        if ($this->resourceLifetime !== null) {
            $config['resourceLifetime'] = $this->resourceLifetime->getConfig();
        }

        return $config;
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
