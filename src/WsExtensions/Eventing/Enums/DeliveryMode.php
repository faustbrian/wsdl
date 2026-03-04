<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Eventing\Enums;

/**
 * Delivery mode values for WS-Eventing.
 *
 * Defines how events are delivered to subscribers.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum DeliveryMode: string
{
    case Push = 'http://schemas.xmlsoap.org/ws/2004/08/eventing/DeliveryModes/Push';
    case Pull = 'http://schemas.xmlsoap.org/ws/2004/08/eventing/DeliveryModes/Pull';
    case Wrapped = 'http://schemas.xmlsoap.org/ws/2004/08/eventing/DeliveryModes/Wrapped';
}
