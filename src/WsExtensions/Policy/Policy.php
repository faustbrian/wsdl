<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Policy;

/**
 * Represents a WS-Policy wsp:Policy element.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Policy
{
    public const string POLICY_NS = 'http://www.w3.org/ns/ws-policy';

    /** @var array<int, PolicyOperator> */
    private array $operators = [];

    /** @var array<int, PolicyAssertion> */
    private array $assertions = [];

    /** @var array<int, PolicyReference> */
    private array $references = [];

    public function __construct(
        private readonly ?string $id = null,
        private readonly ?string $name = null,
        private readonly ?object $parent = null,
    ) {}

    /**
     * Create a wsp:All operator (all assertions must be satisfied).
     */
    public function all(): PolicyOperator
    {
        $operator = new PolicyOperator($this, 'all');
        $this->operators[] = $operator;

        return $operator;
    }

    /**
     * Create a wsp:ExactlyOne operator (exactly one alternative).
     */
    public function exactlyOne(): PolicyOperator
    {
        $operator = new PolicyOperator($this, 'exactlyOne');
        $this->operators[] = $operator;

        return $operator;
    }

    /**
     * Add a policy assertion.
     *
     * @param null|array<string, string> $attributes
     */
    public function assertion(string $namespace, string $localName, ?array $attributes = null): self
    {
        /** @var null|array<string, string> $attributes */
        $this->assertions[] = new PolicyAssertion($namespace, $localName, $attributes);

        return $this;
    }

    /**
     * Add a policy reference.
     */
    public function reference(string $uri): self
    {
        $this->references[] = new PolicyReference($uri);

        return $this;
    }

    /**
     * Get the policy ID.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the policy name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get all policy operators.
     *
     * @return array<int, PolicyOperator>
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    /**
     * Get all policy assertions.
     *
     * @return array<int, PolicyAssertion>
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }

    /**
     * Get all policy references.
     *
     * @return array<int, PolicyReference>
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    /**
     * Return to the parent object (for fluent interface).
     */
    public function end(): ?object
    {
        return $this->parent;
    }
}
