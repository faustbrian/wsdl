<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Types;

/**
 * Represents an XSD element within a complex type.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Element
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $nullable = false,
        public ?int $minOccurs = null,
        public ?int $maxOccurs = null,
        public ?string $substitutionGroup = null,
        public ?string $block = null,
    ) {}
}
