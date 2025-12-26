<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Core;

use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyAttachment;

/**
 * Represents a WSDL service.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Service
{
    use PolicyAttachment;
    /** @var array<string, Port> */
    private array $ports = [];

    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Wsdl $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add a port to this service.
     */
    public function port(string $name, string $binding, string $address): self
    {
        $this->ports[$name] = new Port($name, $binding, $address);

        return $this;
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
    public function end(): Wsdl
    {
        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, Port>
     */
    public function getPorts(): array
    {
        return $this->ports;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
