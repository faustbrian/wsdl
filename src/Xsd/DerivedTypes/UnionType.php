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

use function array_map;
use function array_values;

/**
 * Represents an XSD union type (value can be one of several types).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class UnionType
{
    /** @var array<int, string> */
    private array $memberTypesArray = [];

    public function __construct(
        private readonly WsdlBuilderInterface $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Set the member types for the union.
     */
    public function memberTypes(XsdType|string ...$types): self
    {
        $this->memberTypesArray = array_values(array_map(
            fn (XsdType|string $type): string => $type instanceof XsdType
                ? $type->value
                : preg_replace('/^(?:xsd|xs):/', '', $type),
            $types,
        ));

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
     * @return array<int, string>
     */
    public function getMemberTypes(): array
    {
        return $this->memberTypesArray;
    }
}
