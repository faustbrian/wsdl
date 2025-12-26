<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\MetadataExchange\Enums;

/**
 * WS-MetadataExchange dialect enumeration.
 *
 * Defines the standard metadata dialect URIs used in WS-MEX.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum MetadataDialect: string
{
    /**
     * WSDL 1.1 metadata dialect.
     */
    case WSDL = 'http://schemas.xmlsoap.org/wsdl/';

    /**
     * XML Schema metadata dialect.
     */
    case XmlSchema = 'http://www.w3.org/2001/XMLSchema';

    /**
     * WS-Policy metadata dialect.
     */
    case Policy = 'http://schemas.xmlsoap.org/ws/2004/09/policy';

    /**
     * WS-MetadataExchange metadata dialect (recursive).
     */
    case MEX = 'http://schemas.xmlsoap.org/ws/2004/09/mex';
}
