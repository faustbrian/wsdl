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
 * Represents an operation within a WSDL 2.0 interface.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class InterfaceOperation
{
    private ?string $pattern = null;

    private ?string $input = null;

    private ?string $output = null;

    /** @var array<int, string> */
    private array $faults = [];

    private ?string $style = null;

    private bool $safe = false;

    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Interface_ $interface,
        private readonly string $name,
    ) {}

    /**
     * Set the message exchange pattern URI.
     */
    public function pattern(string $mep): self
    {
        $this->pattern = $mep;

        return $this;
    }

    /**
     * Set the input element reference.
     */
    public function input(string $element): self
    {
        $this->input = $element;

        return $this;
    }

    /**
     * Set the output element reference.
     */
    public function output(string $element): self
    {
        $this->output = $element;

        return $this;
    }

    /**
     * Add a fault reference (references an interface-level fault).
     */
    public function fault(string $ref): self
    {
        $this->faults[] = $ref;

        return $this;
    }

    /**
     * Set the operation style URI.
     */
    public function style(string $uri): self
    {
        $this->style = $uri;

        return $this;
    }

    /**
     * Mark this operation as safe (no side effects).
     */
    public function safe(bool $safe = true): self
    {
        $this->safe = $safe;

        return $this;
    }

    /**
     * Add documentation to this operation.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Return to the parent interface.
     */
    public function end(): Interface_
    {
        return $this->interface;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * @return array<int, string>
     */
    public function getFaults(): array
    {
        return $this->faults;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function isSafe(): bool
    {
        return $this->safe;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
