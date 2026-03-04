<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Trust;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Addressing\Metadata;
use Cline\WsdlBuilder\WsExtensions\Addressing\ReferenceParameters;
use Cline\WsdlBuilder\WsExtensions\Policy\Policy;
use Cline\WsdlBuilder\WsExtensions\Security\TokenAssertion;
use Override;

/**
 * Represents a WS-SecureConversation token assertion.
 *
 * Provides a fluent interface for building secure conversation tokens with
 * bootstrap policy and issuer endpoint.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SecureConversation extends TokenAssertion
{
    private ?Policy $bootstrapPolicy = null;

    private ?EndpointReference $issuer = null;

    public function __construct()
    {
        parent::__construct('sp:SecureConversationToken');
    }

    /**
     * Set the bootstrap policy for establishing the secure conversation.
     */
    public function bootstrapPolicy(Policy $policy): self
    {
        $this->bootstrapPolicy = $policy;

        return $this;
    }

    /**
     * Set the issuer endpoint reference.
     */
    public function issuer(EndpointReference $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * Get the bootstrap policy.
     */
    public function getBootstrapPolicy(): ?Policy
    {
        return $this->bootstrapPolicy;
    }

    /**
     * Get the issuer endpoint reference.
     */
    public function getIssuer(): ?EndpointReference
    {
        return $this->issuer;
    }

    /**
     * Convert to array representation for policy generation.
     *
     * @return array<string, mixed>
     */
    #[Override()]
    public function toArray(): array
    {
        $config = parent::toArray();

        if ($this->bootstrapPolicy instanceof Policy) {
            $config['bootstrapPolicy'] = [
                'id' => $this->bootstrapPolicy->getId(),
            ];
        }

        if ($this->issuer instanceof EndpointReference) {
            $config['issuer'] = [
                'address' => $this->issuer->getAddress(),
            ];

            if ($this->issuer->getReferenceParameters() instanceof ReferenceParameters) {
                $config['issuer']['referenceParameters'] = $this->issuer->getReferenceParameters();
            }

            if ($this->issuer->getMetadata() instanceof Metadata) {
                $config['issuer']['metadata'] = $this->issuer->getMetadata();
            }
        }

        return $config;
    }
}
