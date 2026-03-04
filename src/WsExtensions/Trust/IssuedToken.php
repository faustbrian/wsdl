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
use Cline\WsdlBuilder\WsExtensions\Security\TokenAssertion;
use Override;

/**
 * Represents a WS-Trust IssuedToken assertion.
 *
 * Provides a fluent interface for building issued token assertions with
 * issuer endpoint and request security token template.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class IssuedToken extends TokenAssertion
{
    private ?EndpointReference $issuer = null;

    private ?RequestSecurityToken $requestSecurityTokenTemplate = null;

    public function __construct()
    {
        parent::__construct('sp:IssuedToken');
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
     * Set the RequestSecurityToken template.
     */
    public function requestSecurityTokenTemplate(RequestSecurityToken $template): self
    {
        $this->requestSecurityTokenTemplate = $template;

        return $this;
    }

    /**
     * Get the issuer endpoint reference.
     */
    public function getIssuer(): ?EndpointReference
    {
        return $this->issuer;
    }

    /**
     * Get the RequestSecurityToken template.
     */
    public function getRequestSecurityTokenTemplate(): ?RequestSecurityToken
    {
        return $this->requestSecurityTokenTemplate;
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

        if ($this->requestSecurityTokenTemplate instanceof RequestSecurityToken) {
            $config['requestSecurityTokenTemplate'] = $this->requestSecurityTokenTemplate->toArray();
        }

        return $config;
    }
}
