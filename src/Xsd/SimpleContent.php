<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd;

use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Xsd\Attributes\Attribute;

/**
 * Represents an XSD simpleContent element for complex types.
 * SimpleContent allows a complex type to have simple content (text) plus attributes.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SimpleContent
{
    private const string DERIVATION_EXTENSION = 'extension';
    private const string DERIVATION_RESTRICTION = 'restriction';

    private ?string $base = null;

    private ?string $derivationType = null;

    /** @var array<int, Attribute> */
    private array $attributes = [];

    public function __construct(
        private readonly ComplexType $parent,
    ) {}

    /**
     * Extend a simple type with attributes.
     */
    public function extension(XsdType|string $base): self
    {
        $this->base = $base instanceof XsdType ? $base->value : $base;
        $this->derivationType = self::DERIVATION_EXTENSION;

        return $this;
    }

    /**
     * Restrict a simple type with attributes.
     */
    public function restriction(XsdType|string $base): self
    {
        $this->base = $base instanceof XsdType ? $base->value : $base;
        $this->derivationType = self::DERIVATION_RESTRICTION;

        return $this;
    }

    /**
     * Add an attribute to this simpleContent.
     */
    public function attribute(string $name, XsdType|string $type): self
    {
        $attribute = Attribute::create($name, $type);
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * Return to the parent ComplexType.
     */
    public function end(): ComplexType
    {
        return $this->parent;
    }

    public function getBase(): ?string
    {
        return $this->base;
    }

    public function getDerivationType(): ?string
    {
        return $this->derivationType;
    }

    /**
     * @return array<int, Attribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
