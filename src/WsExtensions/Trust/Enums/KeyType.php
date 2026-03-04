<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Trust\Enums;

/**
 * WS-Trust key type enumeration.
 *
 * Defines the types of cryptographic keys used in WS-Trust token requests.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum KeyType: string
{
    /**
     * Public key cryptography.
     */
    case PublicKey = 'http://docs.oasis-open.org/ws-sx/ws-trust/200512/PublicKey';

    /**
     * Symmetric key cryptography.
     */
    case SymmetricKey = 'http://docs.oasis-open.org/ws-sx/ws-trust/200512/SymmetricKey';

    /**
     * Bearer token (no proof of possession).
     */
    case Bearer = 'http://docs.oasis-open.org/ws-sx/ws-trust/200512/Bearer';
}
