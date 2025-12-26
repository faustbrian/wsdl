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
 * Represents a WS-Notification consumer interface.
 *
 * Defines the endpoint where notifications should be delivered.
 * The consumer receives Notify operations from the producer.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class NotificationConsumer
{
    public function __construct(
        private readonly EndpointReference $endpointReference,
    ) {}

    public function getEndpointReference(): EndpointReference
    {
        return $this->endpointReference;
    }
}
