<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Compositors;

use Cline\WsdlBuilder\Xsd\Types\Element;
use Cline\WsdlBuilder\Enums\XsdType;

/**
 * Represents an XSD choice compositor (one of many elements).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Choice
{
    /** @var array<int, Element> */
    private array $elements = [];

    private ?int $minOccurs = null;

    private ?int $maxOccurs = null;

    public function __construct(
        private readonly object $parent,
    ) {}

    /**
     * Add an element to this choice.
     */
    public function element(
        string $name,
        XsdType|string $type,
        bool $nullable = false,
        ?int $minOccurs = null,
        ?int $maxOccurs = null,
    ): self {
        $this->elements[] = new Element(
            $name,
            $type instanceof XsdType ? $type->value : $type,
            $nullable,
            $minOccurs,
            $maxOccurs,
        );

        return $this;
    }

    /**
     * Set minimum occurrences for the choice.
     */
    public function minOccurs(int $min): self
    {
        $this->minOccurs = $min;

        return $this;
    }

    /**
     * Set maximum occurrences for the choice.
     */
    public function maxOccurs(int $max): self
    {
        $this->maxOccurs = $max;

        return $this;
    }

    /**
     * Return to the parent builder.
     */
    public function end(): object
    {
        return $this->parent;
    }

    /**
     * @return array<int, Element>
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function getMinOccurs(): ?int
    {
        return $this->minOccurs;
    }

    public function getMaxOccurs(): ?int
    {
        return $this->maxOccurs;
    }
}
