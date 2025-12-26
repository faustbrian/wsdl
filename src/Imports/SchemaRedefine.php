<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Imports;

use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;

/**
 * Represents an XSD schema redefine element for modifying types from another schema.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SchemaRedefine
{
    /** @var array<string, SimpleType> */
    private array $simpleTypes = [];

    /** @var array<string, ComplexType> */
    private array $complexTypes = [];

    /** @var array<string, AttributeGroup> */
    private array $attributeGroups = [];

    /** @var array<string, ElementGroup> */
    private array $groups = [];

    public function __construct(
        private readonly Wsdl $wsdl,
        private readonly string $schemaLocation,
    ) {}

    /**
     * Redefine a simple type.
     */
    public function simpleType(string $name): SimpleType
    {
        $type = new SimpleType($this->wsdl, $name);
        $this->simpleTypes[$name] = $type;

        return $type;
    }

    /**
     * Redefine a complex type.
     */
    public function complexType(string $name): ComplexType
    {
        $type = new ComplexType($this->wsdl, $name);
        $this->complexTypes[$name] = $type;

        return $type;
    }

    /**
     * Redefine an attribute group.
     */
    public function attributeGroup(string $name): AttributeGroup
    {
        $group = new AttributeGroup($this->wsdl, $name);
        $this->attributeGroups[$name] = $group;

        return $group;
    }

    /**
     * Redefine an element group.
     */
    public function group(string $name): ElementGroup
    {
        $group = new ElementGroup($this->wsdl, $name);
        $this->groups[$name] = $group;

        return $group;
    }

    /**
     * Return to the parent WSDL builder.
     */
    public function end(): Wsdl
    {
        return $this->wsdl;
    }

    public function getSchemaLocation(): string
    {
        return $this->schemaLocation;
    }

    /**
     * @return array<string, SimpleType>
     */
    public function getSimpleTypes(): array
    {
        return $this->simpleTypes;
    }

    /**
     * @return array<string, ComplexType>
     */
    public function getComplexTypes(): array
    {
        return $this->complexTypes;
    }

    /**
     * @return array<string, AttributeGroup>
     */
    public function getAttributeGroups(): array
    {
        return $this->attributeGroups;
    }

    /**
     * @return array<string, ElementGroup>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
