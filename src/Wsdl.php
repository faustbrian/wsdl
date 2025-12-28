<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder;

use Cline\WsdlBuilder\Contracts\WsdlBuilderInterface;
use Cline\WsdlBuilder\Core\Binding;
use Cline\WsdlBuilder\Core\Message;
use Cline\WsdlBuilder\Core\Operation;
use Cline\WsdlBuilder\Core\Port;
use Cline\WsdlBuilder\Core\PortType;
use Cline\WsdlBuilder\Core\Service;
use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Enums\BindingStyle;
use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Enums\SoapVersion;
use Cline\WsdlBuilder\Imports\SchemaImport;
use Cline\WsdlBuilder\Imports\SchemaInclude;
use Cline\WsdlBuilder\Imports\SchemaRedefine;
use Cline\WsdlBuilder\Imports\WsdlImport;
use Cline\WsdlBuilder\Operations\Notification;
use Cline\WsdlBuilder\Operations\OneWay;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyAttachment;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;
use Cline\WsdlBuilder\Xsd\DerivedTypes\ListType;
use Cline\WsdlBuilder\Xsd\DerivedTypes\UnionType;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;
use DOMDocument;
use DOMElement;

use function sprintf;

/**
 * Fluent WSDL 1.1 builder.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Wsdl implements WsdlBuilderInterface
{
    use PolicyAttachment;

    public const string WSDL_NS = 'http://schemas.xmlsoap.org/wsdl/';

    public const string XSD_NS = 'http://www.w3.org/2001/XMLSchema';

    public const string SOAP_NS = 'http://schemas.xmlsoap.org/wsdl/soap/';

    public const string SOAP12_NS = 'http://schemas.xmlsoap.org/wsdl/soap12/';

    public const string HTTP_TRANSPORT = 'http://schemas.xmlsoap.org/soap/http';

    private SoapVersion $soapVersion = SoapVersion::Soap11;

    private BindingStyle $defaultStyle = BindingStyle::Document;

    private BindingUse $defaultUse = BindingUse::Literal;

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

    /** @var array<string, Message> */
    private array $messages = [];

    /** @var array<string, PortType> */
    private array $portTypes = [];

    /** @var array<string, Binding> */
    private array $bindings = [];

    /** @var array<string, Service> */
    private array $services = [];

    /** @var array<int, WsdlImport> */
    private array $wsdlImports = [];

    /** @var array<int, SchemaImport> */
    private array $schemaImports = [];

    /** @var array<int, SchemaInclude> */
    private array $schemaIncludes = [];

    /** @var array<int, SchemaRedefine> */
    private array $schemaRedefines = [];

    private ?Documentation $documentation = null;

    private function __construct(
        private readonly string $name,
        private readonly string $targetNamespace,
    ) {}

    /**
     * Create a new WSDL builder.
     */
    public static function create(string $name, string $targetNamespace): self
    {
        return new self($name, $targetNamespace);
    }

    /**
     * Set the SOAP version.
     */
    public function soapVersion(SoapVersion $version): self
    {
        $this->soapVersion = $version;

        return $this;
    }

    /**
     * Set the default binding style.
     */
    public function defaultStyle(BindingStyle $style): self
    {
        $this->defaultStyle = $style;

        return $this;
    }

    /**
     * Set the default binding use.
     */
    public function defaultUse(BindingUse $use): self
    {
        $this->defaultUse = $use;

        return $this;
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
     * Add a message.
     */
    public function message(string $name): Message
    {
        $message = new Message($this, $name);
        $this->messages[$name] = $message;

        return $message;
    }

    /**
     * Add a port type (interface).
     */
    public function portType(string $name): PortType
    {
        $portType = new PortType($this, $name);
        $this->portTypes[$name] = $portType;

        return $portType;
    }

    /**
     * Add a binding.
     */
    public function binding(string $name, string $portType): Binding
    {
        $binding = new Binding($this, $name, $portType);
        $this->bindings[$name] = $binding;

        return $binding;
    }

    /**
     * Add a service.
     */
    public function service(string $name): Service
    {
        $service = new Service($this, $name);
        $this->services[$name] = $service;

        return $service;
    }

    /**
     * Shorthand: Add an operation with auto-generated messages and types.
     */
    public function operation(string $name): Operation
    {
        return new Operation($this, $name);
    }

    /**
     * Shorthand: Add a one-way operation (input only, no response).
     */
    public function oneWay(string $name): OneWay
    {
        return new OneWay($this, $name);
    }

    /**
     * Shorthand: Add a notification operation (output only, no input).
     */
    public function notification(string $name): Notification
    {
        return new Notification($this, $name);
    }

    /**
     * Import another WSDL document.
     */
    public function import(string $namespace, string $location): self
    {
        $this->wsdlImports[] = new WsdlImport($namespace, $location);

        return $this;
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
     * Redefine types from an XSD schema.
     */
    public function redefine(string $schemaLocation): SchemaRedefine
    {
        $redefine = new SchemaRedefine($this, $schemaLocation);
        $this->schemaRedefines[] = $redefine;

        return $redefine;
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
     * Get the SOAP version.
     */
    public function getSoapVersion(): SoapVersion
    {
        return $this->soapVersion;
    }

    /**
     * Get the default style.
     */
    public function getDefaultStyle(): BindingStyle
    {
        return $this->defaultStyle;
    }

    /**
     * Get the default use.
     */
    public function getDefaultUse(): BindingUse
    {
        return $this->defaultUse;
    }

    /**
     * Get the SOAP namespace for current version.
     */
    public function getSoapNamespace(): string
    {
        return $this->soapVersion->namespace();
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
     * Get all messages.
     *
     * @return array<string, Message>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Get all port types.
     *
     * @return array<string, PortType>
     */
    public function getPortTypes(): array
    {
        return $this->portTypes;
    }

    /**
     * Get all bindings.
     *
     * @return array<string, Binding>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Get all services.
     *
     * @return array<string, Service>
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Get all WSDL imports.
     *
     * @return array<int, WsdlImport>
     */
    public function getImports(): array
    {
        return $this->wsdlImports;
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
     * Get all schema redefines.
     *
     * @return array<int, SchemaRedefine>
     */
    public function getRedefines(): array
    {
        return $this->schemaRedefines;
    }

    /**
     * Get the documentation.
     */
    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }

    /**
     * Build and return the WSDL XML.
     */
    public function build(): string
    {
        return new WsdlGenerator($this)->generate();
    }

    /**
     * Build and return as DOMDocument.
     */
    public function buildDom(): DOMDocument
    {
        return new WsdlGenerator($this)->generateDom();
    }
}
