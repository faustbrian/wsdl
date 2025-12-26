<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Wsdl2;

use Cline\WsdlBuilder\Documentation\Documentation;

/**
 * Represents a WSDL 2.0 service.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Service2
{
    private ?string $interfaceRef = null;

    /** @var array<string, Endpoint> */
    private array $endpoints = [];

    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Wsdl2 $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Set the interface reference for this service.
     */
    public function interface(string $interfaceRef): self
    {
        $this->interfaceRef = $interfaceRef;

        return $this;
    }

    /**
     * Add an endpoint to this service.
     */
    public function endpoint(string $name, string $binding, string $address): Endpoint
    {
        $endpoint = new Endpoint($this, $name, $binding, $address);
        $this->endpoints[$name] = $endpoint;

        return $endpoint;
    }

    /**
     * Add documentation to this service.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Return to the parent WSDL builder.
     */
    public function end(): Wsdl2
    {
        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInterfaceRef(): ?string
    {
        return $this->interfaceRef;
    }

    /**
     * @return array<string, Endpoint>
     */
    public function getEndpoints(): array
    {
        return $this->endpoints;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
