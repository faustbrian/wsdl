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

use function array_values;
use function preg_replace;

/**
 * Represents an XSD simple type with restrictions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SimpleType
{
    private string $base = 'string';

    private ?int $minLength = null;

    private ?int $maxLength = null;

    private ?string $pattern = null;

    /** @var array<int, string> */
    private array $enumeration = [];

    private ?string $minInclusive = null;

    private ?string $maxInclusive = null;

    private ?string $minExclusive = null;

    private ?string $maxExclusive = null;

    private ?Documentation $documentation = null;

    private ?string $final = null;

    public function __construct(
        private readonly WsdlBuilderInterface $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Set the base type.
     */
    public function base(XsdType|string $type): self
    {
        if ($type instanceof XsdType) {
            $this->base = $type->value;
        } else {
            // Strip xsd: or xs: prefix if present
            $this->base = preg_replace('/^(?:xsd|xs):/', '', $type);
        }

        return $this;
    }

    /**
     * Set minimum length restriction.
     */
    public function minLength(int $length): self
    {
        $this->minLength = $length;

        return $this;
    }

    /**
     * Set maximum length restriction.
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
     * Set minimum inclusive value.
     */
    public function minInclusive(int|float|string $value): self
    {
        $this->minInclusive = (string) $value;

        return $this;
    }

    /**
     * Set maximum inclusive value.
     */
    public function maxInclusive(int|float|string $value): self
    {
        $this->maxInclusive = (string) $value;

        return $this;
    }

    /**
     * Set minimum exclusive value.
     */
    public function minExclusive(int|float|string $value): self
    {
        $this->minExclusive = (string) $value;

        return $this;
    }

    /**
     * Set maximum exclusive value.
     */
    public function maxExclusive(int|float|string $value): self
    {
        $this->maxExclusive = (string) $value;

        return $this;
    }

    /**
     * Add documentation to this simple type.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Set final derivation controls.
     * Values: #all, list, union, restriction, or space-separated combination.
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

    public function getBase(): string
    {
        return $this->base;
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

    public function getMinInclusive(): ?string
    {
        return $this->minInclusive;
    }

    public function getMaxInclusive(): ?string
    {
        return $this->maxInclusive;
    }

    public function getMinExclusive(): ?string
    {
        return $this->minExclusive;
    }

    public function getMaxExclusive(): ?string
    {
        return $this->maxExclusive;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }

    public function getFinal(): ?string
    {
        return $this->final;
    }
}
