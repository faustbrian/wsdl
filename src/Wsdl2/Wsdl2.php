<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Wsdl2;

use Cline\WsdlBuilder\Contracts\WsdlBuilderInterface;
use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Imports\SchemaImport;
use Cline\WsdlBuilder\Imports\SchemaInclude;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;
use Cline\WsdlBuilder\Xsd\DerivedTypes\ListType;
use Cline\WsdlBuilder\Xsd\DerivedTypes\UnionType;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;
use DOMDocument;

/**
 * Fluent WSDL 2.0 builder.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Wsdl2 implements WsdlBuilderInterface
{
    public const string WSDL_NS = 'http://www.w3.org/ns/wsdl';

    public const string XSD_NS = 'http://www.w3.org/2001/XMLSchema';

    public const string SOAP_NS = 'http://www.w3.org/ns/wsdl/soap';

    public const string HTTP_NS = 'http://www.w3.org/ns/wsdl/http';

    public const string SOAP_HTTP_BINDING = 'http://www.w3.org/2003/05/soap/bindings/HTTP/';

    /** @var array<string, SimpleType> */
    private array $simpleTypes = [];

    /** @var array<string, ComplexType> */
    private array $complexTypes = [];

    /** @var array<string, ElementGroup> */
    private array $elementGroups = [];

    /** @var array<string, AttributeGroup> */
    private array $attributeGroups = [];

    /** @var array<string, ListType> */
    private array $listTypes = [];

    /** @var array<string, UnionType> */
    private array $unionTypes = [];

    /** @var array<string, Interface_> */
    private array $interfaces = [];

    /** @var array<string, Binding2> */
    private array $bindings = [];

    /** @var array<string, Service2> */
    private array $services = [];

    /** @var array<int, SchemaImport> */
    private array $schemaImports = [];

    /** @var array<int, SchemaInclude> */
    private array $schemaIncludes = [];

    private ?Documentation $documentation = null;

    private function __construct(
        private readonly string $name,
        private readonly string $targetNamespace,
    ) {}

    /**
     * Create a new WSDL 2.0 builder.
     */
    public static function create(string $name, string $targetNamespace): self
    {
        return new self($name, $targetNamespace);
    }

    /**
     * Add a simple type restriction.
     */
    public function simpleType(string $name): SimpleType
    {
        $type = new SimpleType($this, $name);
        $this->simpleTypes[$name] = $type;

        return $type;
    }

    /**
     * Add a complex type.
     */
    public function complexType(string $name): ComplexType
    {
        $type = new ComplexType($this, $name);
        $this->complexTypes[$name] = $type;

        return $type;
    }

    /**
     * Add an element group.
     */
    public function elementGroup(string $name): ElementGroup
    {
        $group = new ElementGroup($this, $name);
        $this->elementGroups[$name] = $group;

        return $group;
    }

    /**
     * Add an attribute group.
     */
    public function attributeGroup(string $name): AttributeGroup
    {
        $group = new AttributeGroup($this, $name);
        $this->attributeGroups[$name] = $group;

        return $group;
    }

    /**
     * Add a list type.
     */
    public function listType(string $name): ListType
    {
        $type = new ListType($this, $name);
        $this->listTypes[$name] = $type;

        return $type;
    }

    /**
     * Add a union type.
     */
    public function unionType(string $name): UnionType
    {
        $type = new UnionType($this, $name);
        $this->unionTypes[$name] = $type;

        return $type;
    }

    /**
     * Add an interface (replaces portType in WSDL 1.1).
     */
    public function interface(string $name): Interface_
    {
        $interface = new Interface_($this, $name);
        $this->interfaces[$name] = $interface;

        return $interface;
    }

    /**
     * Add a binding.
     */
    public function binding(string $name, string $interfaceRef): Binding2
    {
        $binding = new Binding2($this, $name, $interfaceRef);
        $this->bindings[$name] = $binding;

        return $binding;
    }

    /**
     * Add a service.
     */
    public function service(string $name): Service2
    {
        $service = new Service2($this, $name);
        $this->services[$name] = $service;

        return $service;
    }

    /**
     * Import an XSD schema from another namespace.
     */
    public function schemaImport(string $namespace, ?string $schemaLocation = null): self
    {
        $this->schemaImports[] = new SchemaImport($namespace, $schemaLocation);

        return $this;
    }

    /**
     * Include an XSD schema from the same namespace.
     */
    public function schemaInclude(string $schemaLocation): self
    {
        $this->schemaIncludes[] = new SchemaInclude($schemaLocation);

        return $this;
    }

    /**
     * Add documentation at the WSDL level.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Get the name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the target namespace.
     */
    public function getTargetNamespace(): string
    {
        return $this->targetNamespace;
    }

    /**
     * Get all simple types.
     *
     * @return array<string, SimpleType>
     */
    public function getSimpleTypes(): array
    {
        return $this->simpleTypes;
    }

    /**
     * Get all complex types.
     *
     * @return array<string, ComplexType>
     */
    public function getComplexTypes(): array
    {
        return $this->complexTypes;
    }

    /**
     * Get all element groups.
     *
     * @return array<string, ElementGroup>
     */
    public function getElementGroups(): array
    {
        return $this->elementGroups;
    }

    /**
     * Get all attribute groups.
     *
     * @return array<string, AttributeGroup>
     */
    public function getAttributeGroups(): array
    {
        return $this->attributeGroups;
    }

    /**
     * Get all list types.
     *
     * @return array<string, ListType>
     */
    public function getListTypes(): array
    {
        return $this->listTypes;
    }

    /**
     * Get all union types.
     *
     * @return array<string, UnionType>
     */
    public function getUnionTypes(): array
    {
        return $this->unionTypes;
    }

    /**
     * Get all interfaces.
     *
     * @return array<string, Interface_>
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * Get all bindings.
     *
     * @return array<string, Binding2>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Get all services.
     *
     * @return array<string, Service2>
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Get all schema imports.
     *
     * @return array<int, SchemaImport>
     */
    public function getSchemaImports(): array
    {
        return $this->schemaImports;
    }

    /**
     * Get all schema includes.
     *
     * @return array<int, SchemaInclude>
     */
    public function getSchemaIncludes(): array
    {
        return $this->schemaIncludes;
    }

    /**
     * Get the documentation.
     */
    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }

    /**
     * Build and return the WSDL 2.0 XML.
     */
    public function build(): string
    {
        $generator = new Wsdl2Generator($this);

        return $generator->generate();
    }

    /**
     * Build and return as DOMDocument.
     */
    public function buildDom(): DOMDocument
    {
        $generator = new Wsdl2Generator($this);

        return $generator->generateDom();
    }
}
