<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Soap;

use Cline\WsdlBuilder\Enums\BindingUse;

/**
 * Represents a SOAP header definition.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Header
{
    private BindingUse $use;

    private ?string $namespace = null;

    private ?string $encodingStyle = null;

    private bool $required = false;

    /** @var array<int, HeaderFault> */
    private array $headerFaults = [];

    public function __construct(
        private readonly string $message,
        private readonly string $part,
    ) {
        $this->use = BindingUse::Literal;
    }

    /**
     * Add a header fault.
     */
    public function headerFault(string $message, string $part): HeaderFault
    {
        $fault = new HeaderFault($message, $part);
        $this->headerFaults[] = $fault;

        return $fault;
    }

    /**
     * @return array<int, HeaderFault>
     */
    public function getHeaderFaults(): array
    {
        return $this->headerFaults;
    }

    /**
     * Set the binding use (literal or encoded).
     */
    public function use(BindingUse $use): self
    {
        $this->use = $use;

        return $this;
    }

    /**
     * Set the header namespace.
     */
    public function namespace(string $ns): self
    {
        $this->namespace = $ns;

        return $this;
    }

    /**
     * Set the encoding style.
     */
    public function encodingStyle(string $style): self
    {
        $this->encodingStyle = $style;

        return $this;
    }

    /**
     * Mark the header as required.
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPart(): string
    {
        return $this->part;
    }

    public function getUse(): BindingUse
    {
        return $this->use;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getEncodingStyle(): ?string
    {
        return $this->encodingStyle;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
