<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Eventing;

/**
 * Represents a WS-Eventing filter (wse:Filter).
 *
 * Allows subscribers to filter which events they receive based on
 * a filter expression in a specific dialect (e.g., XPath).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Filter
{
    public function __construct(
        private readonly string $dialect,
        private readonly string $content,
    ) {}

    public function getDialect(): string
    {
        return $this->dialect;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
