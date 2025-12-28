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
 * Represents a SOAP header fault definition.
 *
 * Used when header processing fails.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class HeaderFault
{
    private BindingUse $use = BindingUse::Literal;

    private ?string $namespace = null;

    private ?string $encodingStyle = null;

    private bool $required = false;

    public function __construct(
        private readonly string $message,
        private readonly string $part,
    ) {}

    /**
     * Set the binding use (literal or encoded).
     */
    public function use(BindingUse $use): self
    {
        $this->use = $use;

        return $this;
    }

    /**
     * Set the header fault namespace.
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
     * Mark the header fault as required.
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
