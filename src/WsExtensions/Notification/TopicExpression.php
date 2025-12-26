<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Notification;

use Cline\WsdlBuilder\WsExtensions\Notification\Enums\TopicDialect;

/**
 * Represents a WS-Notification topic expression.
 *
 * Topic expressions define how topics are referenced in subscriptions,
 * using different dialects (Simple, Concrete, Full, or XPath).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TopicExpression
{
    public function __construct(
        private readonly TopicDialect $dialect,
        private readonly string $value,
    ) {}

    public function getDialect(): TopicDialect
    {
        return $this->dialect;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
