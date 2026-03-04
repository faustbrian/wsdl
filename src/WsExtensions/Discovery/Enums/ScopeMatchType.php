<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Discovery\Enums;

/**
 * WS-Discovery scope matching algorithm values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum ScopeMatchType: string
{
    case RFC3986 = 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/rfc3986';
    case UUID = 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/uuid';
    case LDAP = 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/ldap';
    case Strcmp0 = 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/strcmp0';
    case None = 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01/none';
}
