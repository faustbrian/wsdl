<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Trust;

/**
 * Factory for WS-Trust policy assertions.
 *
 * Provides static methods that return policy assertion objects compatible
 * with the existing WS-Policy infrastructure.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TrustPolicy
{
    public const string NAMESPACE_URI = 'http://docs.oasis-open.org/ws-sx/ws-trust/200512';

    public const string FEDERATION_NAMESPACE_URI = 'http://docs.oasis-open.org/wsfed/federation/200706';

    /**
     * Create an IssuedToken assertion.
     */
    public static function issuedToken(): IssuedToken
    {
        return new IssuedToken();
    }

    /**
     * Create a SecureConversation token assertion.
     */
    public static function secureConversationToken(): SecureConversation
    {
        return new SecureConversation();
    }

    /**
     * Create a RequestSecurityToken template.
     */
    public static function requestSecurityToken(): RequestSecurityToken
    {
        return new RequestSecurityToken();
    }

    /**
     * Create a Claims assertion.
     *
     * @param null|string $dialectUri Optional dialect URI (defaults to WS-Federation auth claims)
     */
    public static function claims(?string $dialectUri = null): Claims
    {
        return new Claims($dialectUri ?? Claims::DEFAULT_DIALECT_URI);
    }
}
