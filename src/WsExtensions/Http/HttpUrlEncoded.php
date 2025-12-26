<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Http;

/**
 * Represents http:urlEncoded element for HTTP bindings.
 *
 * Indicates that the message parts are encoded as URL-encoded form data
 * (application/x-www-form-urlencoded).
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class HttpUrlEncoded
{
    /**
     * Create an HTTP URL-encoded element.
     */
    public static function create(): self
    {
        return new self();
    }
}
