<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Core;

/**
 * Represents an operation within a port type.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class PortTypeOperation
{
    public function __construct(
        public string $name,
        public ?string $input,
        public ?string $output,
        public ?string $fault = null,
    ) {}
}
