<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder;

use Cline\WsdlBuilder\Core\Binding;
use Cline\WsdlBuilder\Core\Message;
use Cline\WsdlBuilder\Core\PortType;
use Cline\WsdlBuilder\Core\Service;
use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Imports\SchemaRedefine;
use Cline\WsdlBuilder\WsExtensions\Http\HttpBinding;
use Cline\WsdlBuilder\WsExtensions\Mime\MimeContent;
use Cline\WsdlBuilder\WsExtensions\Mime\MimeMultipartRelated;
use Cline\WsdlBuilder\WsExtensions\Mime\MimePart;
use Cline\WsdlBuilder\WsExtensions\Policy\Policy;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyAssertion;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyOperator;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyReference;
use Cline\WsdlBuilder\Xsd\Annotations\Annotation;
use Cline\WsdlBuilder\Xsd\Attributes\AnyAttribute;
use Cline\WsdlBuilder\Xsd\Attributes\Attribute;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;
use Cline\WsdlBuilder\Xsd\Compositors\All;
use Cline\WsdlBuilder\Xsd\Compositors\Any;
use Cline\WsdlBuilder\Xsd\Compositors\Choice;
use Cline\WsdlBuilder\Xsd\Constraints\Key;
use Cline\WsdlBuilder\Xsd\Constraints\KeyRef;
use Cline\WsdlBuilder\Xsd\Constraints\Unique;
use Cline\WsdlBuilder\Xsd\DerivedTypes\ListType;
use Cline\WsdlBuilder\Xsd\DerivedTypes\UnionType;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;
use Cline\WsdlBuilder\Xsd\SimpleContent;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;
use DOMDocument;
use DOMElement;

use function implode;
use function str_starts_with;

/**
 * Generates WSDL XML from the builder.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class WsdlGenerator
{
    private const string WSP_NS = 'http://www.w3.org/ns/ws-policy';

    private const string WSA_NS = 'http://www.w3.org/2006/05/addressing/wsdl';

    private const string SP_NS = 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702';

    private const string HTTP_BINDING_NS = 'http://schemas.xmlsoap.org/wsdl/http/';

    /**
     * XOP namespace for MTOM/XOP binary attachments.
     *
     * @phpstan-ignore classConstant.unused
     */
    private const string XOP_NS = 'http://www.w3.org/2004/08/xop/include';

    private const string MIME_NS = 'http://schemas.xmlsoap.org/wsdl/mime/';

    private DOMDocument $dom;

    private DOMElement $definitions;

    public function __construct(
        private readonly Wsdl $wsdl,
    ) {}

    /**
     * Generate WSDL XML string.
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

        $this->createDefinitions();
        $this->createImports();
        $this->createTypes();
        $this->createMessages();
        $this->createPortTypes();
        $this->createBindings();
        $this->createServices();

        return $this->dom;
    }

    private function createDefinitions(): void
    {
        $this->definitions = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:definitions');
        $this->definitions->setAttribute('name', $this->wsdl->getName());
        $this->definitions->setAttribute('targetNamespace', $this->wsdl->getTargetNamespace());
        $this->definitions->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:tns',
            $this->wsdl->getTargetNamespace(),
        );
        $this->definitions->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsd',
            Wsdl::XSD_NS,
        );
        $this->definitions->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:soap',
            $this->wsdl->getSoapNamespace(),
        );

        // Add WS-Policy namespace declarations if policies exist
        if ($this->hasPolicies()) {
            $this->definitions->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:wsp',
                self::WSP_NS,
            );
        }

        // Add WS-Addressing namespace if any bindings or port types use addressing
        if ($this->hasAddressing()) {
            $this->definitions->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:wsaw',
                self::WSA_NS,
            );
        }

        // Add WS-SecurityPolicy namespace if needed
        if ($this->hasSecurityPolicy()) {
            $this->definitions->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:sp',
                self::SP_NS,
            );
        }

        // Add MIME namespace if any bindings use MIME attachments
        if ($this->hasMimeBindings()) {
            $this->definitions->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:mime',
                self::MIME_NS,
            );
        }

        // Add HTTP binding namespace if any bindings use HTTP
        if ($this->hasHttpBinding()) {
            $this->definitions->setAttributeNS(
                'http://www.w3.org/2000/xmlns/',
                'xmlns:http',
                self::HTTP_BINDING_NS,
            );
        }

        // Add WSDL-level documentation
        $this->addDocumentation($this->definitions, $this->wsdl->getDocumentation());

        // Add WSDL-level policies before types section
        foreach ($this->wsdl->getPolicies() as $policy) {
            $this->addPolicy($this->definitions, $policy);
        }

        $this->dom->appendChild($this->definitions);
    }

    private function createImports(): void
    {
        // WSDL imports
        foreach ($this->wsdl->getImports() as $import) {
            $el = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:import');
            $el->setAttribute('namespace', $import->namespace);
            $el->setAttribute('location', $import->location);
            $this->definitions->appendChild($el);
        }
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
        $schemaRedefines = $this->wsdl->getRedefines();

        $hasTypes = $simpleTypes !== [] || $complexTypes !== [] || $listTypes !== []
            || $unionTypes !== [] || $elementGroups !== [] || $attributeGroups !== []
            || $schemaImports !== [] || $schemaIncludes !== [] || $schemaRedefines !== [];

        if (!$hasTypes) {
            return;
        }

        $types = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:types');
        $schema = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:schema');
        $schema->setAttribute('targetNamespace', $this->wsdl->getTargetNamespace());

        // Schema imports
        foreach ($schemaImports as $import) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:import');
            $el->setAttribute('namespace', $import->namespace);

            if ($import->schemaLocation !== null) {
                $el->setAttribute('schemaLocation', $import->schemaLocation);
            }
            $schema->appendChild($el);
        }

        // Schema includes
        foreach ($schemaIncludes as $include) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:include');
            $el->setAttribute('schemaLocation', $include->schemaLocation);
            $schema->appendChild($el);
        }

        // Schema redefines
        foreach ($schemaRedefines as $redefine) {
            $this->addSchemaRedefine($schema, $redefine);
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
        $this->definitions->appendChild($types);
    }

    private function addDocumentation(DOMElement $parent, ?Documentation $doc): void
    {
        if ($doc === null) {
            return;
        }

        $docEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:documentation');
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
        $groupEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:group');
        $groupEl->setAttribute('name', $group->getName());

        $elements = $group->getElements();
        $choice = $group->getChoice();
        $all = $group->getAll();

        if ($choice !== null) {
            $this->addChoice($groupEl, $choice);
        } elseif ($all !== null) {
            $this->addAll($groupEl, $all);
        } elseif ($elements !== []) {
            $sequence = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:sequence');

            foreach ($elements as $element) {
                $this->addElementFromArray($sequence, $element);
            }
            $groupEl->appendChild($sequence);
        }

        $schema->appendChild($groupEl);
    }

    private function addAttributeGroup(DOMElement $schema, AttributeGroup $group): void
    {
        $groupEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:attributeGroup');
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
        $simpleType = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:simpleType');
        $simpleType->setAttribute('name', $type->getName());

        if ($type->getFinal() !== null) {
            $simpleType->setAttribute('final', $type->getFinal());
        }

        $this->addDocumentation($simpleType, $type->getDocumentation());

        $restriction = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:restriction');
        $restriction->setAttribute('base', $type->getBase());

        if ($type->getMinLength() !== null) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:minLength');
            $el->setAttribute('value', (string) $type->getMinLength());
            $restriction->appendChild($el);
        }

        if ($type->getMaxLength() !== null) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:maxLength');
            $el->setAttribute('value', (string) $type->getMaxLength());
            $restriction->appendChild($el);
        }

        if ($type->getPattern() !== null) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:pattern');
            $el->setAttribute('value', $type->getPattern());
            $restriction->appendChild($el);
        }

        foreach ($type->getEnumeration() as $value) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:enumeration');
            $el->setAttribute('value', $value);
            $restriction->appendChild($el);
        }

        if ($type->getMinInclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:minInclusive');
            $el->setAttribute('value', $type->getMinInclusive());
            $restriction->appendChild($el);
        }

        if ($type->getMaxInclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:maxInclusive');
            $el->setAttribute('value', $type->getMaxInclusive());
            $restriction->appendChild($el);
        }

        if ($type->getMinExclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:minExclusive');
            $el->setAttribute('value', $type->getMinExclusive());
            $restriction->appendChild($el);
        }

        if ($type->getMaxExclusive() !== null) {
            $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:maxExclusive');
            $el->setAttribute('value', $type->getMaxExclusive());
            $restriction->appendChild($el);
        }

        $simpleType->appendChild($restriction);
        $schema->appendChild($simpleType);
    }

    private function addListType(DOMElement $schema, ListType $type): void
    {
        $simpleType = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:simpleType');
        $simpleType->setAttribute('name', $type->getName());

        $list = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:list');
        $list->setAttribute('itemType', $type->getItemType());

        // If there are restrictions, wrap in restriction
        if ($type->getMinLength() !== null || $type->getMaxLength() !== null
            || $type->getPattern() !== null || $type->getEnumeration() !== []) {
            $innerSimple = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:simpleType');
            $innerSimple->appendChild($list);

            $restriction = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:restriction');
            $restriction->appendChild($innerSimple);

            if ($type->getMinLength() !== null) {
                $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:minLength');
                $el->setAttribute('value', (string) $type->getMinLength());
                $restriction->appendChild($el);
            }

            if ($type->getMaxLength() !== null) {
                $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:maxLength');
                $el->setAttribute('value', (string) $type->getMaxLength());
                $restriction->appendChild($el);
            }

            if ($type->getPattern() !== null) {
                $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:pattern');
                $el->setAttribute('value', $type->getPattern());
                $restriction->appendChild($el);
            }

            foreach ($type->getEnumeration() as $value) {
                $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:enumeration');
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
        $simpleType = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:simpleType');
        $simpleType->setAttribute('name', $type->getName());

        $union = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:union');
        $union->setAttribute('memberTypes', implode(' ', $type->getMemberTypes()));

        $simpleType->appendChild($union);
        $schema->appendChild($simpleType);
    }

    private function addComplexType(DOMElement $schema, ComplexType $type): void
    {
        $complexType = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:complexType');
        $complexType->setAttribute('name', $type->getName());

        if ($type->isAbstract()) {
            $complexType->setAttribute('abstract', 'true');
        }

        if ($type->isMixed()) {
            $complexType->setAttribute('mixed', 'true');
        }

        if ($type->getBlock() !== null) {
            $complexType->setAttribute('block', $type->getBlock());
        }

        if ($type->getFinal() !== null) {
            $complexType->setAttribute('final', $type->getFinal());
        }

        $this->addXsdAnnotation($complexType, $type->getAnnotation());
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
            $complexContent = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:complexContent');
            $extension = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:extension');
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
            $sequence = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:sequence');

            foreach ($elements as $element) {
                $this->addElement($sequence, $element);
            }

            // Add group references
            foreach ($groupRefs as $ref) {
                $groupRef = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:group');
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
            $attrGroupRef = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:attributeGroup');
            $attrGroupRef->setAttribute('ref', 'tns:'.$ref);
            $sequenceParent->appendChild($attrGroupRef);
        }

        // Add anyAttribute
        $anyAttr = $type->getAnyAttribute();

        if ($anyAttr !== null) {
            $this->addAnyAttribute($sequenceParent, $anyAttr);
        }

        // Add identity constraints
        $this->addIdentityConstraints($complexType, $type);

        $schema->appendChild($complexType);
    }

    private function addSimpleContent(DOMElement $complexType, SimpleContent $simpleContent): void
    {
        $simpleContentEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:simpleContent');

        $derivationType = $simpleContent->getDerivationType();
        $base = $simpleContent->getBase();

        if ($derivationType !== null && $base !== null) {
            $derivationEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:'.$derivationType);

            // Determine if base is a built-in XSD type or custom type
            if (str_starts_with($base, 'xsd:')) {
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

    private function addElement(DOMElement $parent, Xsd\Types\Element $element): void
    {
        $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:element');
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

        if ($element->substitutionGroup !== null) {
            $el->setAttribute('substitutionGroup', $element->substitutionGroup);
        }

        if ($element->block !== null) {
            $el->setAttribute('block', $element->block);
        }

        $parent->appendChild($el);
    }

    /**
     * @param array{name: string, type: string, nullable: bool, minOccurs: ?int, maxOccurs: ?int} $element
     */
    private function addElementFromArray(DOMElement $parent, array $element): void
    {
        $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:element');
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
        $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:attribute');
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
        $el = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:anyAttribute');
        $el->setAttribute('namespace', $anyAttr->getNamespace());
        $el->setAttribute('processContents', $anyAttr->getProcessContents());
        $parent->appendChild($el);
    }

    private function addChoice(DOMElement $parent, Choice $choice): void
    {
        $choiceEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:choice');

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
        $allEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:all');

        foreach ($all->getElements() as $element) {
            $this->addElement($allEl, $element);
        }

        $parent->appendChild($allEl);
    }

    private function addAny(DOMElement $parent, Any $any): void
    {
        $anyEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:any');
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

    private function createMessages(): void
    {
        foreach ($this->wsdl->getMessages() as $message) {
            $this->addMessage($message);
        }
    }

    private function addMessage(Message $message): void
    {
        $el = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:message');
        $el->setAttribute('name', $message->getName());

        $this->addDocumentation($el, $message->getDocumentation());

        foreach ($message->getParts() as $part) {
            $partEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:part');
            $partEl->setAttribute('name', $part->name);

            // Check if type is in tns namespace
            if (str_starts_with($part->type, 'tns:')) {
                $partEl->setAttribute('element', $part->type);
            } else {
                $partEl->setAttribute('type', $part->type);
            }

            $el->appendChild($partEl);
        }

        $this->definitions->appendChild($el);
    }

    private function createPortTypes(): void
    {
        foreach ($this->wsdl->getPortTypes() as $portType) {
            $this->addPortType($portType);
        }
    }

    private function addPortType(PortType $portType): void
    {
        $el = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:portType');
        $el->setAttribute('name', $portType->getName());

        $this->addDocumentation($el, $portType->getDocumentation());

        foreach ($portType->getOperations() as $operation) {
            $opEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:operation');
            $opEl->setAttribute('name', $operation->name);

            // Input (may be null for notification operations)
            if ($operation->input !== null) {
                $inputEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:input');
                $inputEl->setAttribute('message', 'tns:'.$operation->input);
                $opEl->appendChild($inputEl);
            }

            // Output (may be null for one-way operations)
            if ($operation->output !== null) {
                $outputEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:output');
                $outputEl->setAttribute('message', 'tns:'.$operation->output);
                $opEl->appendChild($outputEl);
            }

            if ($operation->fault !== null) {
                $faultEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:fault');
                $faultEl->setAttribute('name', $operation->fault);
                $faultEl->setAttribute('message', 'tns:'.$operation->fault);
                $opEl->appendChild($faultEl);
            }

            $el->appendChild($opEl);
        }

        // Add WS-Addressing attributes after all operations are added
        $this->addAddressingAttributes($el, $portType);

        $this->definitions->appendChild($el);
    }

    private function createBindings(): void
    {
        foreach ($this->wsdl->getBindings() as $binding) {
            $this->addBinding($binding);
        }
    }

    private function addBinding(Binding $binding): void
    {
        $el = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:binding');
        $el->setAttribute('name', $binding->getName());
        $el->setAttribute('type', 'tns:'.$binding->getPortType());

        $this->addDocumentation($el, $binding->getDocumentation());

        // Add WS-Addressing attribute if enabled
        if ($binding->isUsingAddressing()) {
            $el->setAttributeNS(self::WSA_NS, 'wsaw:UsingAddressing', 'required');
        }

        // Add inline policies
        foreach ($binding->getPolicies() as $policy) {
            $this->addPolicy($el, $policy);
        }

        // Add policy references
        foreach ($binding->getPolicyReferences() as $reference) {
            $this->addPolicyReference($el, $reference);
        }

        // Check if HTTP binding is used instead of SOAP
        $httpBinding = $binding->getHttpBinding();

        if ($httpBinding !== null) {
            // HTTP binding element
            $httpBindingEl = $this->dom->createElementNS(self::HTTP_BINDING_NS, 'http:binding');
            $httpBindingEl->setAttribute('verb', $httpBinding->verb);
            $el->appendChild($httpBindingEl);
        } else {
            // SOAP binding element
            $soapBinding = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:binding');
            $soapBinding->setAttribute('style', $binding->getStyle()->value);
            $soapBinding->setAttribute('transport', $binding->getTransport());
            $el->appendChild($soapBinding);
        }

        // Render operations differently for HTTP vs SOAP bindings
        if ($httpBinding !== null) {
            // HTTP binding operations
            foreach ($binding->getOperations() as $operation) {
                $opEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:operation');
                $opEl->setAttribute('name', $operation->name);

                // Add http:operation with location if available
                $httpOperation = $operation->getHttpOperation();

                if ($httpOperation !== null) {
                    $httpOpEl = $this->dom->createElementNS(self::HTTP_BINDING_NS, 'http:operation');
                    $httpOpEl->setAttribute('location', $httpOperation->location);
                    $opEl->appendChild($httpOpEl);
                }

                // HTTP input
                $inputEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:input');

                // Add http:urlEncoded or http:urlReplacement if specified
                $urlEncoded = $operation->getHttpUrlEncoded();
                $urlReplacement = $operation->getHttpUrlReplacement();

                if ($urlEncoded !== null) {
                    $urlEncodedEl = $this->dom->createElementNS(self::HTTP_BINDING_NS, 'http:urlEncoded');
                    $inputEl->appendChild($urlEncodedEl);
                } elseif ($urlReplacement !== null) {
                    $urlReplacementEl = $this->dom->createElementNS(self::HTTP_BINDING_NS, 'http:urlReplacement');
                    $inputEl->appendChild($urlReplacementEl);
                }

                $opEl->appendChild($inputEl);

                // HTTP output
                $outputEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:output');
                $opEl->appendChild($outputEl);

                $el->appendChild($opEl);
            }
        } else {
            // SOAP binding operations
            foreach ($binding->getOperations() as $operation) {
                $opEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:operation');
                $opEl->setAttribute('name', $operation->name);

                $soapOp = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:operation');
                $soapOp->setAttribute('soapAction', $operation->soapAction);
                $soapOp->setAttribute('style', $operation->style->value);
                $opEl->appendChild($soapOp);

                $inputEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:input');

                // Add headers to input
                foreach ($operation->getHeaders() as $header) {
                    $headerEl = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:header');
                    $headerEl->setAttribute('message', 'tns:'.$header->getMessage());
                    $headerEl->setAttribute('part', $header->getPart());
                    $headerEl->setAttribute('use', $header->getUse()->value);

                    if ($header->getNamespace() !== null) {
                        $headerEl->setAttribute('namespace', $header->getNamespace());
                    }

                    if ($header->getEncodingStyle() !== null) {
                        $headerEl->setAttribute('encodingStyle', $header->getEncodingStyle());
                    }

                    // Add header faults
                    foreach ($header->getHeaderFaults() as $fault) {
                        $faultEl = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:headerfault');
                        $faultEl->setAttribute('message', 'tns:'.$fault->getMessage());
                        $faultEl->setAttribute('part', $fault->getPart());
                        $faultEl->setAttribute('use', $fault->getUse()->value);

                        if ($fault->getNamespace() !== null) {
                            $faultEl->setAttribute('namespace', $fault->getNamespace());
                        }

                        if ($fault->getEncodingStyle() !== null) {
                            $faultEl->setAttribute('encodingStyle', $fault->getEncodingStyle());
                        }

                        $headerEl->appendChild($faultEl);
                    }

                    $inputEl->appendChild($headerEl);
                }

                // Check if operation has MIME multipart for input
                if ($operation->getInputMime() !== null) {
                    $this->addMimeMultipart($inputEl, $operation->getInputMime());
                } else {
                    $soapBody = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:body');
                    $soapBody->setAttribute('use', $operation->use->value);
                    $inputEl->appendChild($soapBody);
                }
                $opEl->appendChild($inputEl);

                $outputEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:output');

                // Check if operation has MIME multipart for output
                if ($operation->getOutputMime() !== null) {
                    $this->addMimeMultipart($outputEl, $operation->getOutputMime());
                } else {
                    $soapBody = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:body');
                    $soapBody->setAttribute('use', $operation->use->value);
                    $outputEl->appendChild($soapBody);
                }
                $opEl->appendChild($outputEl);

                $el->appendChild($opEl);
            }
        }

        $this->definitions->appendChild($el);
    }

    private function createServices(): void
    {
        foreach ($this->wsdl->getServices() as $service) {
            $this->addService($service);
        }
    }

    private function addService(Service $service): void
    {
        $el = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:service');
        $el->setAttribute('name', $service->getName());

        $this->addDocumentation($el, $service->getDocumentation());

        foreach ($service->getPorts() as $port) {
            $portEl = $this->dom->createElementNS(Wsdl::WSDL_NS, 'wsdl:port');
            $portEl->setAttribute('name', $port->name);
            $portEl->setAttribute('binding', 'tns:'.$port->binding);

            $addressEl = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:address');
            $addressEl->setAttribute('location', $port->address);
            $portEl->appendChild($addressEl);

            $el->appendChild($portEl);
        }

        $this->definitions->appendChild($el);
    }

    /**
     * Add identity constraints (key, keyref, unique) to a complex type element.
     */
    private function addIdentityConstraints(DOMElement $complexType, ComplexType $type): void
    {
        // Add key constraints
        foreach ($type->getKeys() as $key) {
            $keyEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:key');
            $keyEl->setAttribute('name', $key->getName());

            // Add selector
            $selector = $key->getSelector();

            if ($selector !== null) {
                $selectorEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:selector');
                $selectorEl->setAttribute('xpath', $selector->getXpath());
                $keyEl->appendChild($selectorEl);
            }

            // Add fields
            foreach ($key->getFields() as $field) {
                $fieldEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:field');
                $fieldEl->setAttribute('xpath', $field->getXpath());
                $keyEl->appendChild($fieldEl);
            }

            $complexType->appendChild($keyEl);
        }

        // Add keyref constraints
        foreach ($type->getKeyRefs() as $keyRef) {
            $keyRefEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:keyref');
            $keyRefEl->setAttribute('name', $keyRef->getName());

            if ($keyRef->getRefer() !== null) {
                $keyRefEl->setAttribute('refer', $keyRef->getRefer());
            }

            // Add selector
            $selector = $keyRef->getSelector();

            if ($selector !== null) {
                $selectorEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:selector');
                $selectorEl->setAttribute('xpath', $selector->getXpath());
                $keyRefEl->appendChild($selectorEl);
            }

            // Add fields
            foreach ($keyRef->getFields() as $field) {
                $fieldEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:field');
                $fieldEl->setAttribute('xpath', $field->getXpath());
                $keyRefEl->appendChild($fieldEl);
            }

            $complexType->appendChild($keyRefEl);
        }

        // Add unique constraints
        foreach ($type->getUniques() as $unique) {
            $uniqueEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:unique');
            $uniqueEl->setAttribute('name', $unique->getName());

            // Add selector
            $selector = $unique->getSelector();

            if ($selector !== null) {
                $selectorEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:selector');
                $selectorEl->setAttribute('xpath', $selector->getXpath());
                $uniqueEl->appendChild($selectorEl);
            }

            // Add fields
            foreach ($unique->getFields() as $field) {
                $fieldEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:field');
                $fieldEl->setAttribute('xpath', $field->getXpath());
                $uniqueEl->appendChild($fieldEl);
            }

            $complexType->appendChild($uniqueEl);
        }
    }

    /**
     * Add schema redefine element.
     */
    private function addSchemaRedefine(DOMElement $schema, SchemaRedefine $redefine): void
    {
        $redefineEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:redefine');
        $redefineEl->setAttribute('schemaLocation', $redefine->getSchemaLocation());

        // Add redefined simple types
        foreach ($redefine->getSimpleTypes() as $simpleType) {
            $this->addSimpleType($redefineEl, $simpleType);
        }

        // Add redefined complex types
        foreach ($redefine->getComplexTypes() as $complexType) {
            $this->addComplexType($redefineEl, $complexType);
        }

        // Add redefined attribute groups
        foreach ($redefine->getAttributeGroups() as $attributeGroup) {
            $this->addAttributeGroup($redefineEl, $attributeGroup);
        }

        // Add redefined element groups
        foreach ($redefine->getGroups() as $group) {
            $this->addElementGroup($redefineEl, $group);
        }

        $schema->appendChild($redefineEl);
    }

    /**
     * Add XSD annotation with documentation and appinfo elements.
     */
    private function addXsdAnnotation(DOMElement $parent, ?Annotation $annotation): void
    {
        if ($annotation === null) {
            return;
        }

        $annotationEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:annotation');

        // Add documentation elements
        foreach ($annotation->getDocumentations() as $doc) {
            $docEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:documentation');
            $docEl->textContent = $doc->content;

            if ($doc->lang !== null) {
                $docEl->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:lang', $doc->lang);
            }

            if ($doc->source !== null) {
                $docEl->setAttribute('source', $doc->source);
            }

            $annotationEl->appendChild($docEl);
        }

        // Add appinfo elements
        foreach ($annotation->getAppInfos() as $appInfo) {
            $appInfoEl = $this->dom->createElementNS(Wsdl::XSD_NS, 'xsd:appinfo');
            $appInfoEl->textContent = $appInfo->content;

            if ($appInfo->source !== null) {
                $appInfoEl->setAttribute('source', $appInfo->source);
            }

            $annotationEl->appendChild($appInfoEl);
        }

        // Insert annotation as first child (after attributes but before content)
        if ($parent->firstChild !== null) {
            $parent->insertBefore($annotationEl, $parent->firstChild);
        } else {
            $parent->appendChild($annotationEl);
        }
    }

    /**
     * Check if WSDL or any bindings have policies.
     */
    private function hasPolicies(): bool
    {
        if ($this->wsdl->getPolicies() !== []) {
            return true;
        }

        foreach ($this->wsdl->getBindings() as $binding) {
            if ($binding->getPolicies() !== [] || $binding->getPolicyReferences() !== []) {
                return true;
            }
        }

        foreach ($this->wsdl->getServices() as $service) {
            if ($service->getPolicies() !== [] || $service->getPolicyReferences() !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if any bindings or port types use WS-Addressing.
     */
    private function hasAddressing(): bool
    {
        foreach ($this->wsdl->getBindings() as $binding) {
            if ($binding->isUsingAddressing() || $binding->getActions() !== []) {
                return true;
            }
        }

        foreach ($this->wsdl->getPortTypes() as $portType) {
            if ($portType->isUsingAddressing() || $portType->getActions() !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if any policies contain security policy assertions.
     */
    private function hasSecurityPolicy(): bool
    {
        // Check WSDL-level policies
        foreach ($this->wsdl->getPolicies() as $policy) {
            if ($this->policyHasSecurityAssertion($policy)) {
                return true;
            }
        }

        // Check binding policies
        foreach ($this->wsdl->getBindings() as $binding) {
            foreach ($binding->getPolicies() as $policy) {
                if ($this->policyHasSecurityAssertion($policy)) {
                    return true;
                }
            }
        }

        // Check service policies
        foreach ($this->wsdl->getServices() as $service) {
            foreach ($service->getPolicies() as $policy) {
                if ($this->policyHasSecurityAssertion($policy)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a policy contains security policy assertions.
     */
    private function policyHasSecurityAssertion(Policy $policy): bool
    {
        // Check direct assertions
        foreach ($policy->getAssertions() as $assertion) {
            if ($assertion->namespace === self::SP_NS) {
                return true;
            }
        }

        // Check operators
        foreach ($policy->getOperators() as $operator) {
            if ($this->operatorHasSecurityAssertion($operator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a policy operator contains security policy assertions.
     */
    private function operatorHasSecurityAssertion(PolicyOperator $operator): bool
    {
        // Check assertions in this operator
        foreach ($operator->getAssertions() as $assertion) {
            if ($assertion->namespace === self::SP_NS) {
                return true;
            }
        }

        // Check nested operators recursively
        foreach ($operator->getNestedOperators() as $nested) {
            if ($this->operatorHasSecurityAssertion($nested)) {
                return true;
            }
        }

        // Check nested policies
        foreach ($operator->getNestedPolicies() as $nested) {
            if ($this->policyHasSecurityAssertion($nested)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render wsp:Policy element.
     */
    private function addPolicy(DOMElement $parent, Policy $policy): void
    {
        $policyEl = $this->dom->createElementNS(self::WSP_NS, 'wsp:Policy');

        // Add optional Id attribute
        if ($policy->getId() !== null) {
            $policyEl->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:id', $policy->getId());
        }

        // Add optional Name attribute
        if ($policy->getName() !== null) {
            $policyEl->setAttribute('Name', $policy->getName());
        }

        // Add policy operators (All/ExactlyOne)
        foreach ($policy->getOperators() as $operator) {
            $this->addPolicyOperator($policyEl, $operator);
        }

        // Add direct assertions
        foreach ($policy->getAssertions() as $assertion) {
            $this->addPolicyAssertion($policyEl, $assertion);
        }

        // Add policy references
        foreach ($policy->getReferences() as $reference) {
            $this->addPolicyReference($policyEl, $reference);
        }

        $parent->appendChild($policyEl);
    }

    /**
     * Render wsp:All or wsp:ExactlyOne operator.
     */
    private function addPolicyOperator(DOMElement $parent, PolicyOperator $operator): void
    {
        $type = $operator->getType();
        $elementName = $type === 'all' ? 'wsp:All' : 'wsp:ExactlyOne';
        $operatorEl = $this->dom->createElementNS(self::WSP_NS, $elementName);

        // Add nested operators recursively
        foreach ($operator->getNestedOperators() as $nested) {
            $this->addPolicyOperator($operatorEl, $nested);
        }

        // Add assertions
        foreach ($operator->getAssertions() as $assertion) {
            $this->addPolicyAssertion($operatorEl, $assertion);
        }

        // Add nested policies
        foreach ($operator->getNestedPolicies() as $nested) {
            $this->addPolicy($operatorEl, $nested);
        }

        $parent->appendChild($operatorEl);
    }

    /**
     * Render policy assertion element.
     */
    private function addPolicyAssertion(DOMElement $parent, PolicyAssertion $assertion): void
    {
        $assertionEl = $this->dom->createElementNS($assertion->namespace, $assertion->localName);

        // Add attributes if present
        if ($assertion->attributes !== null) {
            foreach ($assertion->attributes as $name => $value) {
                $assertionEl->setAttribute($name, $value);
            }
        }

        $parent->appendChild($assertionEl);
    }

    /**
     * Render wsp:PolicyReference element.
     */
    private function addPolicyReference(DOMElement $parent, PolicyReference $reference): void
    {
        $refEl = $this->dom->createElementNS(self::WSP_NS, 'wsp:PolicyReference');
        $refEl->setAttribute('URI', $reference->uri);

        if ($reference->digest !== null) {
            $refEl->setAttribute('Digest', $reference->digest);
        }

        if ($reference->digestAlgorithm !== null) {
            $refEl->setAttribute('DigestAlgorithm', $reference->digestAlgorithm);
        }

        $parent->appendChild($refEl);
    }

    /**
     * Add WS-Addressing attributes to port type operations.
     */
    private function addAddressingAttributes(DOMElement $portTypeEl, PortType $portType): void
    {
        // Add wsaw:UsingAddressing attribute if enabled
        if ($portType->isUsingAddressing()) {
            $portTypeEl->setAttributeNS(self::WSA_NS, 'wsaw:UsingAddressing', 'true');
        }

        // Add wsaw:Action elements for each operation
        $actions = $portType->getActions();

        if ($actions === []) {
            return;
        }

        foreach ($portTypeEl->childNodes as $child) {
            if (!($child instanceof DOMElement) || $child->localName !== 'operation') {
                continue;
            }

            $operationName = $child->getAttribute('name');

            if (!isset($actions[$operationName])) {
                continue;
            }

            $action = $actions[$operationName];

            // Add action to input
            foreach ($child->childNodes as $opChild) {
                if (!($opChild instanceof DOMElement) || $opChild->localName !== 'input') {
                    continue;
                }

                $inputActionEl = $this->dom->createElementNS(self::WSA_NS, 'wsaw:Action');
                $inputActionEl->textContent = $action->inputAction;
                $opChild->appendChild($inputActionEl);
            }

            // Add action to output if present
            if ($action->outputAction !== null) {
                foreach ($child->childNodes as $opChild) {
                    if (!($opChild instanceof DOMElement) || $opChild->localName !== 'output') {
                        continue;
                    }

                    $outputActionEl = $this->dom->createElementNS(self::WSA_NS, 'wsaw:Action');
                    $outputActionEl->textContent = $action->outputAction;
                    $opChild->appendChild($outputActionEl);
                }
            }

            // Add fault actions if present
            if ($action->faultActions === null) {
                continue;
            }

            foreach ($action->faultActions as $faultName => $faultAction) {
                foreach ($child->childNodes as $opChild) {
                    if (!($opChild instanceof DOMElement)
                        || $opChild->localName !== 'fault'
                        || $opChild->getAttribute('name') !== $faultName) {
                        continue;
                    }

                    $faultActionEl = $this->dom->createElementNS(self::WSA_NS, 'wsaw:Action');
                    $faultActionEl->textContent = $faultAction;
                    $opChild->appendChild($faultActionEl);
                }
            }
        }
    }

    /**
     * Check if any bindings use HTTP binding instead of SOAP.
     */
    private function hasMimeBindings(): bool
    {
        foreach ($this->wsdl->getBindings() as $binding) {
            foreach ($binding->getOperations() as $operation) {
                if ($operation->getInputMime() !== null || $operation->getOutputMime() !== null) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add MIME multipart related element to a message.
     */
    private function addMimeMultipart(DOMElement $parent, MimeMultipartRelated $multipart): void
    {
        $multipartEl = $this->dom->createElementNS(self::MIME_NS, 'mime:multipartRelated');

        foreach ($multipart->getParts() as $part) {
            $partEl = $this->dom->createElementNS(self::MIME_NS, 'mime:part');

            if ($part->getName() !== null) {
                $partEl->setAttribute('name', $part->getName());
            }

            // Add MIME content if present
            if ($part->getMimeContent() !== null) {
                $content = $part->getMimeContent();
                $contentEl = $this->dom->createElementNS(self::MIME_NS, 'mime:content');

                if ($content->getPart() !== null) {
                    $contentEl->setAttribute('part', $content->getPart());
                }

                if ($content->getType() !== null) {
                    $contentEl->setAttribute('type', $content->getType());
                }

                $partEl->appendChild($contentEl);
            }

            // Add SOAP body reference if present
            if ($part->hasSoapBody()) {
                $soapBody = $this->dom->createElementNS($this->wsdl->getSoapNamespace(), 'soap:body');
                $soapBody->setAttribute('use', 'literal');
                $partEl->appendChild($soapBody);
            }

            $multipartEl->appendChild($partEl);
        }

        $parent->appendChild($multipartEl);
    }

    private function hasHttpBinding(): bool
    {
        foreach ($this->wsdl->getBindings() as $binding) {
            if ($binding->getHttpBinding() !== null) {
                return true;
            }
        }

        return false;
    }
}
