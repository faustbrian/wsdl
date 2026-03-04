<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Eventing;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Eventing\Enums\DeliveryMode;

/**
 * Represents a WS-Eventing delivery configuration (wse:Delivery).
 *
 * Specifies how events should be delivered to the subscriber, including
 * the delivery mode and the endpoint where events should be sent.
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class Delivery
{
    public function __construct(
        private EndpointReference $notifyTo,
        private DeliveryMode $mode = DeliveryMode::Push,
    ) {}

    public function getNotifyTo(): EndpointReference
    {
        return $this->notifyTo;
    }

    public function getMode(): DeliveryMode
    {
        return $this->mode;
    }
}
