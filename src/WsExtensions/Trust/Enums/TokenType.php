<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Trust\Enums;

/**
 * Common WS-Trust token type URIs.
 *
 * Defines standard token types used in WS-Trust requests and responses.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum TokenType: string
{
    /**
     * SAML 1.1 token.
     */
    case SAML11 = 'http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.1#SAMLV1.1';

    /**
     * SAML 2.0 token.
     */
    case SAML20 = 'http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.1#SAMLV2.0';

    /**
     * JWT (JSON Web Token).
     */
    case JWT = 'urn:ietf:params:oauth:token-type:jwt';

    /**
     * Kerberos token.
     */
    case Kerberos = 'http://docs.oasis-open.org/wss/oasis-wss-kerberos-token-profile-1.1#GSS_Kerberosv5_AP_REQ';

    /**
     * X.509 certificate token.
     */
    case X509 = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3';

    /**
     * Username token.
     */
    case Username = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#UsernameToken';

    /**
     * Opaque token.
     */
    case Opaque = 'http://docs.oasis-open.org/wss/oasis-wss-saml-token-profile-1.1#Opaque';
}
