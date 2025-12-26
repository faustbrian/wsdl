<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Transaction\Enums;

/**
 * Transaction flow types for WS-Transaction.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum TransactionFlowType: string
{
    case Mandatory = 'Mandatory';
    case Supported = 'Supported';
    case Allowed = 'Allowed';
    case NotAllowed = 'NotAllowed';
}
