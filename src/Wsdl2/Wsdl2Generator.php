<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Wsdl2;

use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Xsd\Attributes\AnyAttribute;
use Cline\WsdlBuilder\Xsd\Attributes\Attribute;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;
use Cline\WsdlBuilder\Xsd\Compositors\All;
use Cline\WsdlBuilder\Xsd\Compositors\Any;
use Cline\WsdlBuilder\Xsd\Compositors\Choice;
use Cline\WsdlBuilder\Xsd\DerivedTypes\ListType;
use Cline\WsdlBuilder\Xsd\DerivedTypes\UnionType;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;
use Cline\WsdlBuilder\Xsd\SimpleContent;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\Element;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;
use DOMDocument;
use DOMElement;

use function array_map;
use function implode;
use function str_starts_with;

/**
 * Generates WSDL 2.0 XML from the builder.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Wsdl2Generator
{
    private DOMDocument $dom;

    private DOMElement $description;

    public function __construct(
        private readonly Wsdl2 $wsdl,
    ) {}

    /**
     * Generate WSDL 2.0 XML string.
     */
    public function generate(): string
    {
        $dom = $this->generateDom();
        $dom->formatOutput = true;

        return $dom->saveXML() ?: '';
    }

    /**
     * Generate DOMDocument.
     */
    public function generateDom(): DOMDocument
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = true;

        $this->createDescription();
        $this->createTypes();
        $this->createInterfaces();
        $this->createBindings();
        $this->createServices();

        return $this->dom;
    }

    private function createDescription(): void
    {
        $this->description = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:description');
        $this->description->setAttribute('targetNamespace', $this->wsdl->getTargetNamespace());
        $this->description->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:tns',
            $this->wsdl->getTargetNamespace(),
        );
        $this->description->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xs',
            Wsdl2::XSD_NS,
        );
        $this->description->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:wsoap',
            Wsdl2::SOAP_NS,
        );

        // Add WSDL-level documentation
        $this->addDocumentation($this->description, $this->wsdl->getDocumentation());

        $this->dom->appendChild($this->description);
    }

    private function createTypes(): void
    {
        $simpleTypes = $this->wsdl->getSimpleTypes();
        $complexTypes = $this->wsdl->getComplexTypes();
        $listTypes = $this->wsdl->getListTypes();
        $unionTypes = $this->wsdl->getUnionTypes();
        $elementGroups = $this->wsdl->getElementGroups();
        $attributeGroups = $this->wsdl->getAttributeGroups();
        $schemaImports = $this->wsdl->getSchemaImports();
        $schemaIncludes = $this->wsdl->getSchemaIncludes();

        $hasTypes = $simpleTypes !== [] || $complexTypes !== [] || $listTypes !== []
            || $unionTypes !== [] || $elementGroups !== [] || $attributeGroups !== []
            || $schemaImports !== [] || $schemaIncludes !== [];

        if (!$hasTypes) {
            return;
        }

        $types = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:types');
        $schema = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:schema');
        $schema->setAttribute('targetNamespace', $this->wsdl->getTargetNamespace());

        // Schema imports
        foreach ($schemaImports as $import) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:import');
            $el->setAttribute('namespace', $import->namespace);

            if ($import->schemaLocation !== null) {
                $el->setAttribute('schemaLocation', $import->schemaLocation);
            }
            $schema->appendChild($el);
        }

        // Schema includes
        foreach ($schemaIncludes as $include) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:include');
            $el->setAttribute('schemaLocation', $include->schemaLocation);
            $schema->appendChild($el);
        }

        // Element groups
        foreach ($elementGroups as $group) {
            $this->addElementGroup($schema, $group);
        }

        // Attribute groups
        foreach ($attributeGroups as $group) {
            $this->addAttributeGroup($schema, $group);
        }

        // Simple types
        foreach ($simpleTypes as $simpleType) {
            $this->addSimpleType($schema, $simpleType);
        }

        // List types
        foreach ($listTypes as $listType) {
            $this->addListType($schema, $listType);
        }

        // Union types
        foreach ($unionTypes as $unionType) {
            $this->addUnionType($schema, $unionType);
        }

        // Complex types
        foreach ($complexTypes as $complexType) {
            $this->addComplexType($schema, $complexType);
        }

        $types->appendChild($schema);
        $this->description->appendChild($types);
    }

    private function createInterfaces(): void
    {
        foreach ($this->wsdl->getInterfaces() as $interface) {
            $this->addInterface($interface);
        }
    }

    private function addInterface(Interface_ $interface): void
    {
        $el = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:interface');
        $el->setAttribute('name', $interface->getName());

        $this->addDocumentation($el, $interface->getDocumentation());

        // Add extends attribute if interface extends another
        $extends = $interface->getExtends();

        if ($extends !== []) {
            $el->setAttribute('extends', implode(' ', array_map(fn (string $name): string => 'tns:'.$name, $extends)));
        }

        // Add interface-level faults
        foreach ($interface->getFaults() as $fault) {
            $faultEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:fault');
            $faultEl->setAttribute('name', $fault->name);
            $faultEl->setAttribute('element', 'tns:'.$fault->element);
            $el->appendChild($faultEl);
        }

        // Add operations
        foreach ($interface->getOperations() as $operation) {
            $this->addInterfaceOperation($el, $operation);
        }

        $this->description->appendChild($el);
    }

    private function addInterfaceOperation(DOMElement $interface, InterfaceOperation $operation): void
    {
        $opEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:operation');
        $opEl->setAttribute('name', $operation->getName());

        $this->addDocumentation($opEl, $operation->getDocumentation());

        // Add pattern attribute (Message Exchange Pattern URI)
        if ($operation->getPattern() !== null) {
            $opEl->setAttribute('pattern', $operation->getPattern());
        }

        // Add style attribute if set
        if ($operation->getStyle() !== null) {
            $opEl->setAttribute('style', $operation->getStyle());
        }

        // Add safe attribute if true
        if ($operation->isSafe()) {
            $opEl->setAttribute('safe', 'true');
        }

        // Add input with element reference
        if ($operation->getInput() !== null) {
            $inputEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:input');
            $inputEl->setAttribute('element', 'tns:'.$operation->getInput());
            $opEl->appendChild($inputEl);
        }

        // Add output with element reference (if not one-way)
        if ($operation->getOutput() !== null) {
            $outputEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:output');
            $outputEl->setAttribute('element', 'tns:'.$operation->getOutput());
            $opEl->appendChild($outputEl);
        }

        // Add fault references
        foreach ($operation->getFaults() as $faultRef) {
            $faultEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:outfault');
            $faultEl->setAttribute('ref', 'tns:'.$faultRef);
            $opEl->appendChild($faultEl);
        }

        $interface->appendChild($opEl);
    }

    private function createBindings(): void
    {
        foreach ($this->wsdl->getBindings() as $binding) {
            $this->addBinding($binding);
        }
    }

    private function addBinding(Binding2 $binding): void
    {
        $el = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:binding');
        $el->setAttribute('name', $binding->getName());
        $el->setAttribute('interface', 'tns:'.$binding->getInterfaceRef());

        if ($binding->getType() !== null) {
            $el->setAttribute('type', $binding->getType());
        }

        $this->addDocumentation($el, $binding->getDocumentation());

        // Add binding operations
        foreach ($binding->getOperations() as $operation) {
            $this->addBindingOperation($el, $operation);
        }

        // Add binding faults
        foreach ($binding->getFaults() as $fault) {
            $this->addBindingFault($el, $fault);
        }

        $this->description->appendChild($el);
    }

    private function addBindingOperation(DOMElement $binding, BindingOperation2 $operation): void
    {
        $opEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:operation');
        $opEl->setAttribute('ref', 'tns:'.$operation->getRef());

        $this->addDocumentation($opEl, $operation->getDocumentation());

        // Add SOAP binding details if soapAction is set
        if ($operation->getSoapAction() !== null) {
            $soapOpEl = $this->dom->createElementNS(Wsdl2::SOAP_NS, 'wsoap:operation');
            $soapOpEl->setAttribute('soapAction', $operation->getSoapAction());
            $opEl->appendChild($soapOpEl);
        }

        $binding->appendChild($opEl);
    }

    private function addBindingFault(DOMElement $binding, BindingFault2 $fault): void
    {
        $faultEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:fault');
        $faultEl->setAttribute('ref', 'tns:'.$fault->getRef());

        $this->addDocumentation($faultEl, $fault->getDocumentation());

        $binding->appendChild($faultEl);
    }

    private function createServices(): void
    {
        foreach ($this->wsdl->getServices() as $service) {
            $this->addService($service);
        }
    }

    private function addService(Service2 $service): void
    {
        $el = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:service');
        $el->setAttribute('name', $service->getName());

        if ($service->getInterfaceRef() !== null) {
            $el->setAttribute('interface', 'tns:'.$service->getInterfaceRef());
        }

        $this->addDocumentation($el, $service->getDocumentation());

        // Add endpoints
        foreach ($service->getEndpoints() as $endpoint) {
            $this->addEndpoint($el, $endpoint);
        }

        $this->description->appendChild($el);
    }

    private function addEndpoint(DOMElement $service, Endpoint $endpoint): void
    {
        $epEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:endpoint');
        $epEl->setAttribute('name', $endpoint->getName());
        $epEl->setAttribute('binding', 'tns:'.$endpoint->getBinding());
        $epEl->setAttribute('address', $endpoint->getAddress());

        $this->addDocumentation($epEl, $endpoint->getDocumentation());

        $service->appendChild($epEl);
    }

    private function addDocumentation(DOMElement $parent, ?Documentation $doc): void
    {
        if ($doc === null) {
            return;
        }

        $docEl = $this->dom->createElementNS(Wsdl2::WSDL_NS, 'wsdl:documentation');
        $docEl->textContent = $doc->content;

        if ($doc->lang !== null) {
            $docEl->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:lang', $doc->lang);
        }

        if ($doc->source !== null) {
            $docEl->setAttribute('source', $doc->source);
        }

        // Insert documentation as first child
        if ($parent->firstChild !== null) {
            $parent->insertBefore($docEl, $parent->firstChild);
        } else {
            $parent->appendChild($docEl);
        }
    }

    private function addElementGroup(DOMElement $schema, ElementGroup $group): void
    {
        $groupEl = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:group');
        $groupEl->setAttribute('name', $group->getName());

        $elements = $group->getElements();
        $choice = $group->getChoice();
        $all = $group->getAll();

        if ($choice !== null) {
            $this->addChoice($groupEl, $choice);
        } elseif ($all !== null) {
            $this->addAll($groupEl, $all);
        } elseif ($elements !== []) {
            $sequence = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:sequence');

            foreach ($elements as $element) {
                $this->addElementFromArray($sequence, $element);
            }
            $groupEl->appendChild($sequence);
        }

        $schema->appendChild($groupEl);
    }

    private function addAttributeGroup(DOMElement $schema, AttributeGroup $group): void
    {
        $groupEl = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:attributeGroup');
        $groupEl->setAttribute('name', $group->getName());

        foreach ($group->getAttributes() as $attr) {
            $this->addAttribute($groupEl, $attr);
        }

        $anyAttr = $group->getAnyAttribute();

        if ($anyAttr !== null) {
            $this->addAnyAttribute($groupEl, $anyAttr);
        }

        $schema->appendChild($groupEl);
    }

    private function addSimpleType(DOMElement $schema, SimpleType $type): void
    {
        $simpleType = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:simpleType');
        $simpleType->setAttribute('name', $type->getName());

        $this->addDocumentation($simpleType, $type->getDocumentation());

        $restriction = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:restriction');
        $restriction->setAttribute('base', $type->getBase());

        if ($type->getMinLength() !== null) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:minLength');
            $el->setAttribute('value', (string) $type->getMinLength());
            $restriction->appendChild($el);
        }

        if ($type->getMaxLength() !== null) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:maxLength');
            $el->setAttribute('value', (string) $type->getMaxLength());
            $restriction->appendChild($el);
        }

        if ($type->getPattern() !== null) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:pattern');
            $el->setAttribute('value', $type->getPattern());
            $restriction->appendChild($el);
        }

        foreach ($type->getEnumeration() as $value) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:enumeration');
            $el->setAttribute('value', $value);
            $restriction->appendChild($el);
        }

        if ($type->getMinInclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:minInclusive');
            $el->setAttribute('value', $type->getMinInclusive());
            $restriction->appendChild($el);
        }

        if ($type->getMaxInclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:maxInclusive');
            $el->setAttribute('value', $type->getMaxInclusive());
            $restriction->appendChild($el);
        }

        if ($type->getMinExclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:minExclusive');
            $el->setAttribute('value', $type->getMinExclusive());
            $restriction->appendChild($el);
        }

        if ($type->getMaxExclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:maxExclusive');
            $el->setAttribute('value', $type->getMaxExclusive());
            $restriction->appendChild($el);
        }

        $simpleType->appendChild($restriction);
        $schema->appendChild($simpleType);
    }

    private function addListType(DOMElement $schema, ListType $type): void
    {
        $simpleType = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:simpleType');
        $simpleType->setAttribute('name', $type->getName());

        $list = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:list');
        $list->setAttribute('itemType', $type->getItemType());

        // If there are restrictions, wrap in restriction
        if ($type->getMinLength() !== null || $type->getMaxLength() !== null
            || $type->getPattern() !== null || $type->getEnumeration() !== []) {
            $innerSimple = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:simpleType');
            $innerSimple->appendChild($list);

            $restriction = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:restriction');
            $restriction->appendChild($innerSimple);

            if ($type->getMinLength() !== null) {
                $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:minLength');
                $el->setAttribute('value', (string) $type->getMinLength());
                $restriction->appendChild($el);
            }

            if ($type->getMaxLength() !== null) {
                $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:maxLength');
                $el->setAttribute('value', (string) $type->getMaxLength());
                $restriction->appendChild($el);
            }

            if ($type->getPattern() !== null) {
                $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:pattern');
                $el->setAttribute('value', $type->getPattern());
                $restriction->appendChild($el);
            }

            foreach ($type->getEnumeration() as $value) {
                $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:enumeration');
                $el->setAttribute('value', $value);
                $restriction->appendChild($el);
            }

            $simpleType->appendChild($restriction);
        } else {
            $simpleType->appendChild($list);
        }

        $schema->appendChild($simpleType);
    }

    private function addUnionType(DOMElement $schema, UnionType $type): void
    {
        $simpleType = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:simpleType');
        $simpleType->setAttribute('name', $type->getName());

        $union = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:union');
        $union->setAttribute('memberTypes', implode(' ', $type->getMemberTypes()));

        $simpleType->appendChild($union);
        $schema->appendChild($simpleType);
    }

    private function addComplexType(DOMElement $schema, ComplexType $type): void
    {
        $complexType = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:complexType');
        $complexType->setAttribute('name', $type->getName());

        if ($type->isAbstract()) {
            $complexType->setAttribute('abstract', 'true');
        }

        if ($type->isMixed()) {
            $complexType->setAttribute('mixed', 'true');
        }

        $this->addDocumentation($complexType, $type->getDocumentation());

        // Handle simpleContent (simple type with attributes)
        $simpleContent = $type->getSimpleContent();

        if ($simpleContent !== null) {
            $this->addSimpleContent($complexType, $simpleContent);
            $schema->appendChild($complexType);

            return;
        }

        $sequenceParent = $complexType;

        if ($type->getExtends() !== null) {
            $complexContent = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:complexContent');
            $extension = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:extension');
            $extension->setAttribute('base', 'tns:'.$type->getExtends());
            $complexContent->appendChild($extension);
            $complexType->appendChild($complexContent);
            $sequenceParent = $extension;
        }

        $elements = $type->getElements();
        $compositors = $type->getCompositors();
        $groupRefs = $type->getGroupRefs();

        // Render elements in a sequence if present
        if ($elements !== [] || $groupRefs !== []) {
            $sequence = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:sequence');

            foreach ($elements as $element) {
                $this->addElement($sequence, $element);
            }

            // Add group references
            foreach ($groupRefs as $ref) {
                $groupRef = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:group');
                $groupRef->setAttribute('ref', 'tns:'.$ref);
                $sequence->appendChild($groupRef);
            }

            $sequenceParent->appendChild($sequence);
        }

        // Render compositors in sequence order
        foreach ($compositors as $compositor) {
            if ($compositor instanceof Choice) {
                $this->addChoice($sequenceParent, $compositor);
            } elseif ($compositor instanceof All) {
                $this->addAll($sequenceParent, $compositor);
            } elseif ($compositor instanceof Any) {
                $this->addAny($sequenceParent, $compositor);
            }
        }

        // Add attributes
        foreach ($type->getAttributes() as $attr) {
            $this->addAttribute($sequenceParent, $attr);
        }

        // Add attribute group references
        foreach ($type->getAttributeGroupRefs() as $ref) {
            $attrGroupRef = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:attributeGroup');
            $attrGroupRef->setAttribute('ref', 'tns:'.$ref);
            $sequenceParent->appendChild($attrGroupRef);
        }

        // Add anyAttribute
        $anyAttr = $type->getAnyAttribute();

        if ($anyAttr !== null) {
            $this->addAnyAttribute($sequenceParent, $anyAttr);
        }

        $schema->appendChild($complexType);
    }

    private function addSimpleContent(DOMElement $complexType, SimpleContent $simpleContent): void
    {
        $simpleContentEl = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:simpleContent');

        $derivationType = $simpleContent->getDerivationType();
        $base = $simpleContent->getBase();

        if ($derivationType !== null && $base !== null) {
            $derivationEl = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:'.$derivationType);

            // Determine if base is a built-in XSD type or custom type
            if (str_starts_with($base, 'xs:')) {
                $derivationEl->setAttribute('base', $base);
            } else {
                $derivationEl->setAttribute('base', 'tns:'.$base);
            }

            // Add attributes
            foreach ($simpleContent->getAttributes() as $attr) {
                $this->addAttribute($derivationEl, $attr);
            }

            $simpleContentEl->appendChild($derivationEl);
        }

        $complexType->appendChild($simpleContentEl);
    }

    private function addElement(DOMElement $parent, Element $element): void
    {
        $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:element');
        $el->setAttribute('name', $element->name);
        $el->setAttribute('type', $element->type);

        if ($element->nullable) {
            $el->setAttribute('nillable', 'true');
        }

        if ($element->minOccurs !== null) {
            $el->setAttribute('minOccurs', (string) $element->minOccurs);
        }

        if ($element->maxOccurs !== null) {
            $el->setAttribute('maxOccurs', $element->maxOccurs === -1 ? 'unbounded' : (string) $element->maxOccurs);
        }

        $parent->appendChild($el);
    }

    /**
     * @param array{name: string, type: string, nullable: bool, minOccurs: ?int, maxOccurs: ?int} $element
     */
    private function addElementFromArray(DOMElement $parent, array $element): void
    {
        $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:element');
        $el->setAttribute('name', $element['name']);
        $el->setAttribute('type', $element['type']);

        if ($element['nullable']) {
            $el->setAttribute('nillable', 'true');
        }

        if ($element['minOccurs'] !== null) {
            $el->setAttribute('minOccurs', (string) $element['minOccurs']);
        }

        if ($element['maxOccurs'] !== null) {
            $el->setAttribute('maxOccurs', $element['maxOccurs'] === -1 ? 'unbounded' : (string) $element['maxOccurs']);
        }

        $parent->appendChild($el);
    }

    private function addAttribute(DOMElement $parent, Attribute $attr): void
    {
        $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:attribute');
        $el->setAttribute('name', $attr->getName());
        $el->setAttribute('type', $attr->getType());

        if ($attr->getUse() !== null) {
            $el->setAttribute('use', $attr->getUse());
        }

        if ($attr->getDefault() !== null) {
            $el->setAttribute('default', $attr->getDefault());
        }

        if ($attr->getFixed() !== null) {
            $el->setAttribute('fixed', $attr->getFixed());
        }

        if ($attr->getForm() !== null) {
            $el->setAttribute('form', $attr->getForm());
        }

        $parent->appendChild($el);
    }

    private function addAnyAttribute(DOMElement $parent, AnyAttribute $anyAttr): void
    {
        $el = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:anyAttribute');
        $el->setAttribute('namespace', $anyAttr->getNamespace());
        $el->setAttribute('processContents', $anyAttr->getProcessContents());
        $parent->appendChild($el);
    }

    private function addChoice(DOMElement $parent, Choice $choice): void
    {
        $choiceEl = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:choice');

        if ($choice->getMinOccurs() !== null) {
            $choiceEl->setAttribute('minOccurs', (string) $choice->getMinOccurs());
        }

        if ($choice->getMaxOccurs() !== null) {
            $choiceEl->setAttribute('maxOccurs', $choice->getMaxOccurs() === -1 ? 'unbounded' : (string) $choice->getMaxOccurs());
        }

        foreach ($choice->getElements() as $element) {
            $this->addElement($choiceEl, $element);
        }

        $parent->appendChild($choiceEl);
    }

    private function addAll(DOMElement $parent, All $all): void
    {
        $allEl = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:all');

        foreach ($all->getElements() as $element) {
            $this->addElement($allEl, $element);
        }

        $parent->appendChild($allEl);
    }

    private function addAny(DOMElement $parent, Any $any): void
    {
        $anyEl = $this->dom->createElementNS(Wsdl2::XSD_NS, 'xs:any');
        $anyEl->setAttribute('namespace', $any->getNamespace());
        $anyEl->setAttribute('processContents', $any->getProcessContents());

        if ($any->getMinOccurs() !== null) {
            $anyEl->setAttribute('minOccurs', (string) $any->getMinOccurs());
        }

        if ($any->getMaxOccurs() !== null) {
            $anyEl->setAttribute('maxOccurs', $any->getMaxOccurs() === -1 ? 'unbounded' : (string) $any->getMaxOccurs());
        }

        $parent->appendChild($anyEl);
    }
}
