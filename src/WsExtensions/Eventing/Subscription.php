<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Eventing;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;

/**
 * Represents a WS-Eventing subscription (wse:SubscribeResponse).
 *
 * Contains the subscription manager endpoint and expiration information
 * returned when a subscription is successfully created.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Subscription
{
    public function __construct(
        private EndpointReference $subscriptionManager,
        private string $expires,
    ) {}

    public function getSubscriptionManager(): EndpointReference
    {
        return $this->subscriptionManager;
    }

    public function getExpires(): string
    {
        return $this->expires;
    }
}
