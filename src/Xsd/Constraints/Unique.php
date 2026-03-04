<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Constraints;

use Cline\WsdlBuilder\Xsd\Types\ComplexType;

/**
 * Represents an XSD unique identity constraint.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Unique
{
    private ?Selector $selector = null;

    /** @var array<int, Field> */
    private array $fields = [];

    public function __construct(
        private readonly ComplexType $parent,
        private readonly string $name,
    ) {}

    /**
     * Set the selector for this unique constraint.
     */
    public function selector(string $xpath): Selector
    {
        $this->selector = new Selector($this, $xpath);

        return $this->selector;
    }

    /**
     * Add a field to this unique constraint.
     */
    public function field(string $xpath): self
    {
        $this->fields[] = new Field($this, $xpath);

        return $this;
    }

    /**
     * Return to the parent ComplexType.
     */
    public function end(): ComplexType
    {
        return $this->parent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSelector(): ?Selector
    {
        return $this->selector;
    }

    /**
     * @return array<int, Field>
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
