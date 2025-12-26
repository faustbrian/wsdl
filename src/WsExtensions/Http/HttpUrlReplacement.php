<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Http;

/**
 * Represents http:urlReplacement element for HTTP bindings.
 *
 * Indicates that message parts are encoded directly in the URL using
 * REST-style parameter replacement (e.g., /users/(id) where (id) is replaced
 * with the actual value).
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class HttpUrlReplacement
{
    /**
     * Create an HTTP URL-replacement element.
     */
    public static function create(): self
    {
        return new self();
    }
}
