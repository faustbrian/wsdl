<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Policy;

/**
 * Represents a policy assertion element.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class PolicyAssertion
{
    /**
     * @param array<string, string>|null $attributes
     */
    public function __construct(
        public string $namespace,
        public string $localName,
        public ?array $attributes = null,
    ) {}
}
