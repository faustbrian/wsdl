<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Notification;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;

/**
 * Represents a WS-Notification subscribe request.
 *
 * Different from WS-Eventing, includes consumer reference, filter,
 * termination time, and subscription policy.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Subscribe
{
    private ?TopicExpression $filter = null;

    private ?\DateTimeInterface $initialTerminationTime = null;

    /**
     * @var array<string, mixed>
     */
    private array $subscriptionPolicy = [];

    public function __construct(
        private readonly EndpointReference $consumerReference,
    ) {}

    /**
     * Set the topic filter for this subscription.
     */
    public function filter(TopicExpression $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Set the initial termination time.
     */
    public function initialTerminationTime(\DateTimeInterface $time): self
    {
        $this->initialTerminationTime = $time;

        return $this;
    }

    /**
     * Add a subscription policy element.
     */
    public function addPolicyElement(string $key, mixed $value): self
    {
        $this->subscriptionPolicy[$key] = $value;

        return $this;
    }

    public function getConsumerReference(): EndpointReference
    {
        return $this->consumerReference;
    }

    public function getFilter(): ?TopicExpression
    {
        return $this->filter;
    }

    public function getInitialTerminationTime(): ?\DateTimeInterface
    {
        return $this->initialTerminationTime;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSubscriptionPolicy(): array
    {
        return $this->subscriptionPolicy;
    }
}
