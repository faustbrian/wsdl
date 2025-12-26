<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Constraints;

/**
 * Represents an XSD selector for identity constraints.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Selector
{
    public function __construct(
        private readonly Key|KeyRef|Unique $parent,
        private readonly string $xpath,
    ) {}

    /**
     * Return to the parent constraint.
     */
    public function end(): Key|KeyRef|Unique
    {
        return $this->parent;
    }

    public function getXpath(): string
    {
        return $this->xpath;
    }
}
