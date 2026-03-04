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
 * Represents an endpoint within a WSDL 2.0 service (replaces port in WSDL 1.1).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Endpoint
{
    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Service2 $service,
        private readonly string $name,
        private readonly string $binding,
        private readonly string $address,
    ) {}

    /**
     * Add documentation to this endpoint.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Return to the parent service.
     */
    public function end(): Service2
    {
        return $this->service;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBinding(): string
    {
        return $this->binding;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
