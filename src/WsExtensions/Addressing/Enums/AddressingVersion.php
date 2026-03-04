<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Addressing\Enums;

/**
 * WS-Addressing namespace versions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum AddressingVersion: string
{
    case Addressing2004 = 'http://schemas.xmlsoap.org/ws/2004/08/addressing';
    case Addressing2005 = 'http://www.w3.org/2005/08/addressing';
    case AddressingWsdl = 'http://www.w3.org/2006/05/addressing/wsdl';
}
