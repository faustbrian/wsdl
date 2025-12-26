<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Imports;

/**
 * Represents an XSD schema import element for importing definitions from another namespace.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class SchemaImport
{
    public function __construct(
        public string $namespace,
        public ?string $schemaLocation = null,
    ) {}
}
