<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Addressing;

/**
 * Represents WS-Addressing reference parameters (wsa:ReferenceParameters).
 *
 * Provides a fluent interface for adding reference parameters to an endpoint.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ReferenceParameters
{
    /** @var array<int, array{namespace: string, localName: string, value: string}> */
    private array $parameters = [];

    public function __construct(
        private readonly EndpointReference $parent,
    ) {}

    /**
     * Add a reference parameter.
     */
    public function parameter(string $namespace, string $localName, string $value): self
    {
        $this->parameters[] = [
            'namespace' => $namespace,
            'localName' => $localName,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Get all parameters.
     *
     * @return array<int, array{namespace: string, localName: string, value: string}>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Return to the parent endpoint reference.
     */
    public function end(): EndpointReference
    {
        return $this->parent;
    }
}
