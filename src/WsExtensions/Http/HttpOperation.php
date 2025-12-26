<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Http;

/**
 * Represents http:operation element for HTTP bindings.
 *
 * Specifies the URL path template for an HTTP operation.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class HttpOperation
{
    /**
     * @param string $location URL path template (e.g., "/users/(id)")
     */
    public function __construct(
        public string $location,
    ) {}

    /**
     * Create an HTTP operation with a location.
     */
    public static function create(string $location): self
    {
        return new self($location);
    }
}
