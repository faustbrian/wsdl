<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\DerivedTypes;

use Cline\WsdlBuilder\Contracts\WsdlBuilderInterface;
use Cline\WsdlBuilder\Enums\XsdType;

use function array_values;

/**
 * Represents an XSD list type (space-separated values).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ListType
{
    private string $itemTypeValue = 'string';

    private ?int $minLength = null;

    private ?int $maxLength = null;

    private ?string $pattern = null;

    /** @var array<int, string> */
    private array $enumeration = [];

    public function __construct(
        private readonly WsdlBuilderInterface $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Set the item type for the list.
     */
    public function itemType(XsdType|string $type): self
    {
        if ($type instanceof XsdType) {
            $this->itemTypeValue = $type->value;
        } else {
            // Strip xsd: or xs: prefix if present
            $this->itemTypeValue = preg_replace('/^(?:xsd|xs):/', '', $type);
        }

        return $this;
    }

    /**
     * Set minimum length restriction (number of items in list).
     */
    public function minLength(int $length): self
    {
        $this->minLength = $length;

        return $this;
    }

    /**
     * Set maximum length restriction (number of items in list).
     */
    public function maxLength(int $length): self
    {
        $this->maxLength = $length;

        return $this;
    }

    /**
     * Set pattern restriction (regex).
     */
    public function pattern(string $regex): self
    {
        $this->pattern = $regex;

        return $this;
    }

    /**
     * Add enumeration values.
     */
    public function enumeration(string ...$values): self
    {
        $this->enumeration = array_values([...$this->enumeration, ...$values]);

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

    public function getItemType(): string
    {
        return $this->itemTypeValue;
    }

    public function getMinLength(): ?int
    {
        return $this->minLength;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return array<int, string>
     */
    public function getEnumeration(): array
    {
        return $this->enumeration;
    }
}
