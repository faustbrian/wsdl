<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Security;

/**
 * Factory for common WS-SecurityPolicy assertions.
 *
 * Provides static methods that return assertion arrays for use with WS-Policy.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class SecurityPolicy
{
    public const string NAMESPACE_URI = 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702';

    /**
     * Create a TransportBinding assertion.
     *
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public static function transportBinding(mixed $parent = null): TransportBinding
    {
        return new TransportBinding($parent);
    }

    /**
     * Create a SymmetricBinding assertion.
     *
     * @return array<string, mixed>
     */
    public static function symmetricBinding(): array
    {
        return [
            'type' => 'sp:SymmetricBinding',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create an AsymmetricBinding assertion.
     *
     * @return array<string, mixed>
     */
    public static function asymmetricBinding(): array
    {
        return [
            'type' => 'sp:AsymmetricBinding',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a UsernameToken assertion.
     *
     * @param  null|string          $passwordType PasswordText, PasswordDigest, or null for no password
     * @return array<string, mixed>
     */
    public static function usernameToken(?string $passwordType = null): array
    {
        $assertion = [
            'type' => 'sp:UsernameToken',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($passwordType !== null) {
            $assertion['passwordType'] = $passwordType;
        }

        return $assertion;
    }

    /**
     * Create an X509Token assertion.
     *
     * @param  null|string          $tokenType WssX509V3Token10, WssX509Pkcs7Token10, WssX509PkiPathV1Token10, etc.
     * @return array<string, mixed>
     */
    public static function x509Token(?string $tokenType = null): array
    {
        $assertion = [
            'type' => 'sp:X509Token',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($tokenType !== null) {
            $assertion['tokenType'] = $tokenType;
        }

        return $assertion;
    }

    /**
     * Create a SamlToken assertion.
     *
     * @param  null|string          $tokenType WssSamlV11Token10, WssSamlV20Token11, etc.
     * @return array<string, mixed>
     */
    public static function samlToken(?string $tokenType = null): array
    {
        $assertion = [
            'type' => 'sp:SamlToken',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($tokenType !== null) {
            $assertion['tokenType'] = $tokenType;
        }

        return $assertion;
    }

    /**
     * Create a SignedParts assertion.
     *
     * @param  null|array<string>   $parts Array of part names to sign (e.g., ['Body', 'Header'])
     * @return array<string, mixed>
     */
    public static function signedParts(?array $parts = null): array
    {
        $assertion = [
            'type' => 'sp:SignedParts',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($parts !== null) {
            $assertion['parts'] = $parts;
        }

        return $assertion;
    }

    /**
     * Create an EncryptedParts assertion.
     *
     * @param  null|array<string>   $parts Array of part names to encrypt (e.g., ['Body'])
     * @return array<string, mixed>
     */
    public static function encryptedParts(?array $parts = null): array
    {
        $assertion = [
            'type' => 'sp:EncryptedParts',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($parts !== null) {
            $assertion['parts'] = $parts;
        }

        return $assertion;
    }

    /**
     * Create a SignedElements assertion.
     *
     * @param  null|array<string>   $xpaths Array of XPath expressions for elements to sign
     * @return array<string, mixed>
     */
    public static function signedElements(?array $xpaths = null): array
    {
        $assertion = [
            'type' => 'sp:SignedElements',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($xpaths !== null) {
            $assertion['xpaths'] = $xpaths;
        }

        return $assertion;
    }

    /**
     * Create an EncryptedElements assertion.
     *
     * @param  null|array<string>   $xpaths Array of XPath expressions for elements to encrypt
     * @return array<string, mixed>
     */
    public static function encryptedElements(?array $xpaths = null): array
    {
        $assertion = [
            'type' => 'sp:EncryptedElements',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($xpaths !== null) {
            $assertion['xpaths'] = $xpaths;
        }

        return $assertion;
    }

    /**
     * Create an IssuedToken assertion.
     *
     * @return array<string, mixed>
     */
    public static function issuedToken(): array
    {
        return [
            'type' => 'sp:IssuedToken',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a SecureConversationToken assertion.
     *
     * @return array<string, mixed>
     */
    public static function secureConversationToken(): array
    {
        return [
            'type' => 'sp:SecureConversationToken',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a KerberosToken assertion.
     *
     * @return array<string, mixed>
     */
    public static function kerberosToken(): array
    {
        return [
            'type' => 'sp:KerberosToken',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a SpnegoContextToken assertion.
     *
     * @return array<string, mixed>
     */
    public static function spnegoContextToken(): array
    {
        return [
            'type' => 'sp:SpnegoContextToken',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a Wss10 assertion (WS-Security 1.0).
     *
     * @return array<string, mixed>
     */
    public static function wss10(): array
    {
        return [
            'type' => 'sp:Wss10',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a Wss11 assertion (WS-Security 1.1).
     *
     * @return array<string, mixed>
     */
    public static function wss11(): array
    {
        return [
            'type' => 'sp:Wss11',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a Trust10 assertion (WS-Trust 1.0).
     *
     * @return array<string, mixed>
     */
    public static function trust10(): array
    {
        return [
            'type' => 'sp:Trust10',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a Trust13 assertion (WS-Trust 1.3).
     *
     * @return array<string, mixed>
     */
    public static function trust13(): array
    {
        return [
            'type' => 'sp:Trust13',
            'namespace' => self::NAMESPACE_URI,
        ];
    }
}
