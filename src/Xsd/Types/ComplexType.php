<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Types;

use Cline\WsdlBuilder\Contracts\WsdlBuilderInterface;
use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Xsd\Annotations\Annotation;
use Cline\WsdlBuilder\Xsd\Attributes\AnyAttribute;
use Cline\WsdlBuilder\Xsd\Attributes\Attribute;
use Cline\WsdlBuilder\Xsd\Compositors\All;
use Cline\WsdlBuilder\Xsd\Compositors\Any;
use Cline\WsdlBuilder\Xsd\Compositors\Choice;
use Cline\WsdlBuilder\Xsd\Constraints\Key;
use Cline\WsdlBuilder\Xsd\Constraints\KeyRef;
use Cline\WsdlBuilder\Xsd\Constraints\Unique;
use Cline\WsdlBuilder\Xsd\SimpleContent;

/**
 * Represents an XSD complex type with elements.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ComplexType
{
    /** @var array<int, Element> */
    private array $elements = [];

    /** @var array<int, Attribute> */
    private array $attributes = [];

    /** @var array<int, string> */
    private array $attributeGroupRefs = [];

    /** @var array<int, string> */
    private array $groupRefs = [];

    private ?AnyAttribute $anyAttribute = null;

    private ?string $extends = null;

    private bool $abstract = false;

    /** @var array<int, All|Any|Choice> */
    private array $compositors = [];

    private ?Annotation $annotation = null;

    private ?Documentation $documentation = null;

    /** @var array<int, Key> */
    private array $keys = [];

    /** @var array<int, KeyRef> */
    private array $keyRefs = [];

    /** @var array<int, Unique> */
    private array $uniques = [];

    private ?string $block = null;

    private ?string $final = null;

    private ?SimpleContent $simpleContent = null;

    private bool $mixed = false;

    public function __construct(
        private readonly WsdlBuilderInterface $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add an element to this complex type.
     */
    public function element(
        string $name,
        XsdType|string $type,
        bool $nullable = false,
        ?int $minOccurs = null,
        ?int $maxOccurs = null,
        ?string $substitutionGroup = null,
        ?string $block = null,
    ): self {
        $this->elements[] = new Element(
            $name,
            $type instanceof XsdType ? $type->value : $type,
            $nullable,
            $minOccurs,
            $maxOccurs,
            $substitutionGroup,
            $block,
        );

        return $this;
    }

    /**
     * Extend another complex type.
     */
    public function extends(string $typeName): self
    {
        $this->extends = $typeName;

        return $this;
    }

    /**
     * Mark this type as abstract.
     */
    public function abstract(bool $abstract = true): self
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Add an attribute to this complex type.
     */
    public function attribute(string $name, XsdType|string $type): Attribute
    {
        $attribute = Attribute::create($name, $type);
        $this->attributes[] = $attribute;

        return $attribute;
    }

    /**
     * Reference an attribute group by name.
     */
    public function attributeGroup(string $ref): self
    {
        $this->attributeGroupRefs[] = $ref;

        return $this;
    }

    /**
     * Reference an element group by name.
     */
    public function group(string $ref): self
    {
        $this->groupRefs[] = $ref;

        return $this;
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
     * Create a choice compositor.
     */
    public function choice(): Choice
    {
        $choice = new Choice($this);
        $this->compositors[] = $choice;

        return $choice;
    }

    /**
     * Create an all compositor.
     */
    public function all(): All
    {
        $all = new All($this);
        $this->compositors[] = $all;

        return $all;
    }

    /**
     * Add a wildcard any element.
     */
    public function any(): Any
    {
        $any = new Any($this);
        $this->compositors[] = $any;

        return $any;
    }

    /**
     * Add a key identity constraint.
     */
    public function key(string $name): Key
    {
        $key = new Key($this, $name);
        $this->keys[] = $key;

        return $key;
    }

    /**
     * Add a keyref identity constraint.
     */
    public function keyRef(string $name): KeyRef
    {
        $keyRef = new KeyRef($this, $name);
        $this->keyRefs[] = $keyRef;

        return $keyRef;
    }

    /**
     * Add a unique identity constraint.
     */
    public function unique(string $name): Unique
    {
        $unique = new Unique($this, $name);
        $this->uniques[] = $unique;

        return $unique;
    }

    /**
     * Create a simpleContent element for this complex type.
     * SimpleContent allows the type to have simple content (text) plus attributes.
     */
    public function simpleContent(): SimpleContent
    {
        $this->simpleContent = new SimpleContent($this);

        return $this->simpleContent;
    }

    /**
     * Enable mixed content for this complex type.
     * Mixed content allows text to appear between child elements.
     */
    public function mixed(bool $mixed = true): self
    {
        $this->mixed = $mixed;

        return $this;
    }

    /**
     * Add documentation to this complex type.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Create or return the annotation for this complex type.
     */
    public function annotation(): Annotation
    {
        if (!$this->annotation instanceof Annotation) {
            $this->annotation = new Annotation($this);
        }

        return $this->annotation;
    }

    /**
     * Set block derivation controls.
     * Values: #all, extension, restriction, or space-separated combination.
     */
    public function block(?string $block): self
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Set final derivation controls.
     * Values: #all, extension, restriction, or space-separated combination.
     */
    public function final(?string $final): self
    {
        $this->final = $final;

        return $this;
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
     * @return array<int, Element>
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function getExtends(): ?string
    {
        return $this->extends;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    /**
     * @return array<int, Attribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<int, string>
     */
    public function getAttributeGroupRefs(): array
    {
        return $this->attributeGroupRefs;
    }

    /**
     * @return array<int, string>
     */
    public function getGroupRefs(): array
    {
        return $this->groupRefs;
    }

    public function getAnyAttribute(): ?AnyAttribute
    {
        return $this->anyAttribute;
    }

    /**
     * @return array<int, All|Any|Choice>
     */
    public function getCompositors(): array
    {
        return $this->compositors;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }

    public function getAnnotation(): ?Annotation
    {
        return $this->annotation;
    }

    /**
     * @return array<int, Key>
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * @return array<int, KeyRef>
     */
    public function getKeyRefs(): array
    {
        return $this->keyRefs;
    }

    /**
     * @return array<int, Unique>
     */
    public function getUniques(): array
    {
        return $this->uniques;
    }

    public function getBlock(): ?string
    {
        return $this->block;
    }

    public function getFinal(): ?string
    {
        return $this->final;
    }

    public function getSimpleContent(): ?SimpleContent
    {
        return $this->simpleContent;
    }

    public function isMixed(): bool
    {
        return $this->mixed;
    }
}
