<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Attributes;

use Cline\WsdlBuilder\Enums\XsdType;

/**
 * Represents an XSD attribute definition.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Attribute
{
    private ?string $use = null;

    private ?string $default = null;

    private ?string $fixed = null;

    private ?string $form = null;

    public function __construct(
        private readonly string $name,
        private readonly string $type,
    ) {}

    /**
     * Create a new attribute.
     */
    public static function create(string $name, XsdType|string $type): self
    {
        return new self(
            $name,
            $type instanceof XsdType ? $type->value : $type,
        );
    }

    /**
     * Set the use constraint (required, optional, prohibited).
     */
    public function use(string $use): self
    {
        $this->use = $use;

        return $this;
    }

    /**
     * Set the default value.
     */
    public function default(string $value): self
    {
        $this->default = $value;

        return $this;
    }

    /**
     * Set the fixed value.
     */
    public function fixed(string $value): self
    {
        $this->fixed = $value;

        return $this;
    }

    /**
     * Set the form (qualified, unqualified).
     */
    public function form(string $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUse(): ?string
    {
        return $this->use;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getFixed(): ?string
    {
        return $this->fixed;
    }

    public function getForm(): ?string
    {
        return $this->form;
    }
}
