<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Policy;

/**
 * Represents a WS-Policy wsp:PolicyReference element.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class PolicyReference
{
    public function __construct(
        public string $uri,
        public ?string $digest = null,
        public ?string $digestAlgorithm = null,
    ) {}
}
