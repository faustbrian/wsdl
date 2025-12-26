<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Mtom\Enums;

/**
 * Content-Transfer-Encoding values for MIME parts.
 *
 * Defines the encoding mechanism used for binary content in MIME attachments.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum ContentTransferEncoding: string
{
    case Base64 = 'base64';
    case Binary = 'binary';
    case QuotedPrintable = 'quoted-printable';
    case EightBit = '8bit';
    case SevenBit = '7bit';
}
