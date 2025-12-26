<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Annotations;

/**
 * Represents an XSD appinfo element for machine-readable information.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class AppInfo
{
    public function __construct(
        public string $content,
        public ?string $source = null,
    ) {}
}
