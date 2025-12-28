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
 * Represents a WS-Notification producer interface.
 *
 * Defines the notification capabilities of a producer including
 * supported topics, topic expressions, and dialects.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class NotificationProducer
{
    private ?TopicExpression $topicExpression = null;

    private bool $fixedTopicSet = false;

    /** @var array<int, TopicDialect> */
    private array $topicExpressionDialects = [];

    /**
     * Set the topic expression for this producer.
     */
    public function topicExpression(TopicDialect $dialect, string $value): self
    {
        $this->topicExpression = new TopicExpression($dialect, $value);

        return $this;
    }

    /**
     * Set whether the topic set is fixed.
     */
    public function fixedTopicSet(bool $fixed = true): self
    {
        $this->fixedTopicSet = $fixed;

        return $this;
    }

    /**
     * Add a supported topic expression dialect.
     */
    public function addTopicExpressionDialect(TopicDialect $dialect): self
    {
        $this->topicExpressionDialects[] = $dialect;

        return $this;
    }

    public function getTopicExpression(): ?TopicExpression
    {
        return $this->topicExpression;
    }

    public function isFixedTopicSet(): bool
    {
        return $this->fixedTopicSet;
    }

    /**
     * @return array<int, TopicDialect>
     */
    public function getTopicExpressionDialects(): array
    {
        return $this->topicExpressionDialects;
    }
}
