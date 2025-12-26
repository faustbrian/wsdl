<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Eventing;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use DateTimeInterface;

/**
 * Represents a WS-Eventing subscribe request (wse:Subscribe).
 *
 * Contains all the information needed to create a subscription to an event source,
 * including delivery configuration, expiration, and optional filtering.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Subscribe
{
    private ?EndpointReference $endTo = null;

    private ?Filter $filter = null;

    public function __construct(
        private readonly Delivery $delivery,
        private readonly string|DateTimeInterface|null $expires = null,
    ) {}

    /**
     * Set the endpoint where subscription end notifications should be sent.
     */
    public function endTo(EndpointReference $endTo): self
    {
        $this->endTo = $endTo;

        return $this;
    }

    /**
     * Set the filter for this subscription.
     */
    public function filter(Filter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getDelivery(): Delivery
    {
        return $this->delivery;
    }

    public function getExpires(): string|DateTimeInterface|null
    {
        return $this->expires;
    }

    public function getEndTo(): ?EndpointReference
    {
        return $this->endTo;
    }

    public function getFilter(): ?Filter
    {
        return $this->filter;
    }
}
