<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Addressing;

/**
 * Represents a WS-Addressing action (wsaw:Action).
 *
 * Used to specify explicit WS-Addressing action URIs for operations.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Action
{
    /**
     * @param string                     $inputAction  Input message action URI
     * @param null|string                $outputAction Output message action URI (optional)
     * @param null|array<string, string> $faultActions Fault name => action URI mapping (optional)
     */
    public function __construct(
        public string $inputAction,
        public ?string $outputAction = null,
        public ?array $faultActions = null,
    ) {}
}
