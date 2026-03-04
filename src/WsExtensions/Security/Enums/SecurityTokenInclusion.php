<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Security\Enums;

/**
 * WS-SecurityPolicy token inclusion values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum SecurityTokenInclusion: string
{
    case Never = 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/Never';
    case Once = 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/Once';
    case AlwaysToRecipient = 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/AlwaysToRecipient';
    case AlwaysToInitiator = 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/AlwaysToInitiator';
    case Always = 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/Always';
}
