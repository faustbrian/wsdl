<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Wsdl2;

/**
 * Represents a fault within a WSDL 2.0 interface.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class InterfaceFault
{
    public function __construct(
        public string $name,
        public string $element,
    ) {}
}
