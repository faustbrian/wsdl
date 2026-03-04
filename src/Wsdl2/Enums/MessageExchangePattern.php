<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Wsdl2\Enums;

/**
 * WSDL 2.0 Message Exchange Patterns.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum MessageExchangePattern: string
{
    case InOut = 'http://www.w3.org/ns/wsdl/in-out';
    case InOnly = 'http://www.w3.org/ns/wsdl/in-only';
    case RobustInOnly = 'http://www.w3.org/ns/wsdl/robust-in-only';
    case OutOnly = 'http://www.w3.org/ns/wsdl/out-only';
    case OutIn = 'http://www.w3.org/ns/wsdl/out-in';
    case OutOptionalIn = 'http://www.w3.org/ns/wsdl/out-opt-in';
    case InOptionalOut = 'http://www.w3.org/ns/wsdl/in-opt-out';
    case RobustOutOnly = 'http://www.w3.org/ns/wsdl/robust-out-only';
}
