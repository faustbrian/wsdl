<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Notification\Enums;

/**
 * Topic expression dialects for WS-Notification.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum TopicDialect: string
{
    case Simple = 'http://docs.oasis-open.org/wsn/t-1/TopicExpression/Simple';
    case Concrete = 'http://docs.oasis-open.org/wsn/t-1/TopicExpression/Concrete';
    case Full = 'http://docs.oasis-open.org/wsn/t-1/TopicExpression/Full';
    case XPath = 'http://www.w3.org/TR/1999/REC-xpath-19991116';
}
