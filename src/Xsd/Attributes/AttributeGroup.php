<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Attributes;

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Contracts\WsdlBuilderInterface;

/**
 * Represents a reusable XSD attribute group.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class AttributeGroup
{
    /** @var array<int, Attribute> */
    private array $attributes = [];

    private ?AnyAttribute $anyAttribute = null;

    public function __construct(
        private readonly WsdlBuilderInterface $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add an attribute to this group.
     */
    public function attribute(string $name, XsdType|string $type): Attribute
    {
        $attribute = Attribute::create($name, $type);
        $this->attributes[] = $attribute;

        return $attribute;
    }

    /**
     * Add a wildcard attribute.
     */
    public function anyAttribute(): AnyAttribute
    {
        $this->anyAttribute = new AnyAttribute();

        return $this->anyAttribute;
    }

    /**
     * Return to the parent WSDL builder.
     */
    public function end(): WsdlBuilderInterface
    {
        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, Attribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAnyAttribute(): ?AnyAttribute
    {
        return $this->anyAttribute;
    }
}
