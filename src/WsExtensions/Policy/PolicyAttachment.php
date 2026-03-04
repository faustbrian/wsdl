<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Policy;

/**
 * Trait for attaching WS-Policy policies to WSDL elements.
 *
 * @author Brian Faust <brian@cline.sh>
 */
trait PolicyAttachment
{
    /** @var array<int, Policy> */
    private array $policies = [];

    /** @var array<int, PolicyReference> */
    private array $policyReferences = [];

    /**
     * Create an inline policy.
     */
    public function policy(?string $id = null, ?string $name = null): Policy
    {
        $policy = new Policy($id, $name, $this);
        $this->policies[] = $policy;

        return $policy;
    }

    /**
     * Reference an external policy.
     */
    public function policyReference(string $uri, ?string $digest = null, ?string $digestAlgorithm = null): self
    {
        $this->policyReferences[] = new PolicyReference($uri, $digest, $digestAlgorithm);

        return $this;
    }

    /**
     * Get all inline policies.
     *
     * @return array<int, Policy>
     */
    public function getPolicies(): array
    {
        return $this->policies;
    }

    /**
     * Get all policy references.
     *
     * @return array<int, PolicyReference>
     */
    public function getPolicyReferences(): array
    {
        return $this->policyReferences;
    }
}
