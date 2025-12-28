<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Compositors;

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Xsd\Types\Element;
use InvalidArgumentException;

/**
 * Represents an XSD all compositor (unordered elements, each max once).
 * Elements in 'all' can only have minOccurs 0 or 1, maxOccurs 1.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class All
{
    /** @var array<int, Element> */
    private array $elements = [];

    public function __construct(
        private readonly object $parent,
    ) {}

    /**
     * Add an element to this all group.
     * Note: Elements in 'all' can only have minOccurs 0 or 1, maxOccurs 1.
     */
    public function element(
        string $name,
        XsdType|string $type,
        bool $nullable = false,
        ?int $minOccurs = null,
        ?int $maxOccurs = null,
    ): self {
        // Validate XSD 'all' constraints
        if ($minOccurs !== null && $minOccurs > 1) {
            throw new InvalidArgumentException('Elements in <all> can only have minOccurs 0 or 1');
        }

        if ($maxOccurs !== null && $maxOccurs !== 1) {
            throw new InvalidArgumentException('Elements in <all> can only have maxOccurs 1');
        }

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
}
