<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Security\Enums;

/**
 * WS-SecurityPolicy algorithm suite values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum AlgorithmSuite: string
{
    case Basic256 = 'Basic256';
    case Basic192 = 'Basic192';
    case Basic128 = 'Basic128';
    case TripleDes = 'TripleDes';
    case Basic256Sha256 = 'Basic256Sha256';
    case Basic192Sha256 = 'Basic192Sha256';
    case Basic128Sha256 = 'Basic128Sha256';
    case TripleDesSha256 = 'TripleDesSha256';
    case Basic256Rsa15 = 'Basic256Rsa15';
    case Basic192Rsa15 = 'Basic192Rsa15';
    case Basic128Rsa15 = 'Basic128Rsa15';
    case TripleDesRsa15 = 'TripleDesRsa15';
    case Basic256Sha256Rsa15 = 'Basic256Sha256Rsa15';
    case Basic192Sha256Rsa15 = 'Basic192Sha256Rsa15';
    case Basic128Sha256Rsa15 = 'Basic128Sha256Rsa15';
    case TripleDesSha256Rsa15 = 'TripleDesSha256Rsa15';
}
