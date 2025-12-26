<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\MetadataExchange;

/**
 * Factory for WS-MetadataExchange policy assertions.
 *
 * Provides static methods that return policy assertions for advertising MEX capability.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MetadataExchangePolicy
{
    public const string NAMESPACE_URI = 'http://schemas.xmlsoap.org/ws/2004/09/mex';

    /**
     * Create a GetMetadataSupported assertion.
     *
     * Indicates that the service supports the GetMetadata operation.
     *
     * @return array<string, mixed>
     */
    public static function getMetadataSupported(): array
    {
        return [
            'type' => 'mex:GetMetadataSupported',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a MetadataExchange assertion.
     *
     * Indicates that the service supports WS-MetadataExchange protocol.
     *
     * @return array<string, mixed>
     */
    public static function metadataExchange(): array
    {
        return [
            'type' => 'mex:MetadataExchange',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a GetMetadataRequest assertion with specific dialects.
     *
     * Advertises which metadata dialects the service can provide.
     *
     * @param array<string> $dialects Array of supported dialect URIs
     * @return array<string, mixed>
     */
    public static function getMetadataRequest(array $dialects = []): array
    {
        $assertion = [
            'type' => 'mex:GetMetadataRequest',
            'namespace' => self::NAMESPACE_URI,
        ];

        if ($dialects !== []) {
            $assertion['dialects'] = $dialects;
        }

        return $assertion;
    }
}
