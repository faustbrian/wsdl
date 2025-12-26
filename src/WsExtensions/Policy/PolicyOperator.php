<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Policy;

/**
 * Represents a WS-Policy operator (All/ExactlyOne) with nesting support.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PolicyOperator
{
    /** @var array<int, PolicyOperator> */
    private array $nestedOperators = [];

    /** @var array<int, PolicyAssertion> */
    private array $assertions = [];

    /** @var array<int, Policy> */
    private array $nestedPolicies = [];

    public function __construct(
        private readonly Policy|PolicyOperator $parent,
        private readonly string $type,
    ) {}

    /**
     * Create a nested wsp:All operator.
     */
    public function all(): self
    {
        $operator = new self($this, 'all');
        $this->nestedOperators[] = $operator;

        return $operator;
    }

    /**
     * Create a nested wsp:ExactlyOne operator.
     */
    public function exactlyOne(): self
    {
        $operator = new self($this, 'exactlyOne');
        $this->nestedOperators[] = $operator;

        return $operator;
    }

    /**
     * Add a policy assertion.
     *
     * @param array<string, string>|null $attributes
     */
    public function assertion(string $namespace, string $localName, ?array $attributes = null): self
    {
        /** @var array<string, string>|null $attributes */
        $this->assertions[] = new PolicyAssertion($namespace, $localName, $attributes);

        return $this;
    }

    /**
     * Create a nested policy.
     */
    public function policy(?string $id = null, ?string $name = null): Policy
    {
        $policy = new Policy($id, $name, $this);
        $this->nestedPolicies[] = $policy;

        return $policy;
    }

    /**
     * Return to the parent (Policy or PolicyOperator).
     */
    public function end(): Policy|PolicyOperator
    {
        return $this->parent;
    }

    /**
     * Get the operator type (all or exactlyOne).
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get all nested operators.
     *
     * @return array<int, PolicyOperator>
     */
    public function getNestedOperators(): array
    {
        return $this->nestedOperators;
    }

    /**
     * Get all assertions.
     *
     * @return array<int, PolicyAssertion>
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }

    /**
     * Get all nested policies.
     *
     * @return array<int, Policy>
     */
    public function getNestedPolicies(): array
    {
        return $this->nestedPolicies;
    }
}
