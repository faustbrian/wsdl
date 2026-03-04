<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Discovery;

/**
 * Factory for WS-Discovery policy assertions.
 *
 * Provides static methods for common discovery configurations.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class DiscoveryPolicy
{
    public const string NAMESPACE_URI = 'http://docs.oasis-open.org/ws-dd/ns/discovery/2009/01';

    /**
     * Create a discoverable policy assertion.
     *
     * Indicates that the service supports WS-Discovery and can be discovered.
     *
     * @return array<string, mixed>
     */
    public static function discoverable(): array
    {
        return [
            'type' => 'wsd:Discoverable',
            'namespace' => self::NAMESPACE_URI,
            'enabled' => true,
        ];
    }

    /**
     * Create an ad-hoc discovery mode policy assertion.
     *
     * Services operate without a discovery proxy, using multicast.
     *
     * @return array<string, mixed>
     */
    public static function adhoc(): array
    {
        return [
            'type' => 'wsd:DiscoveryMode',
            'namespace' => self::NAMESPACE_URI,
            'mode' => 'adhoc',
        ];
    }

    /**
     * Create a managed discovery mode policy assertion.
     *
     * Services use a discovery proxy for centralized discovery.
     *
     * @param  null|string          $proxyAddress Optional discovery proxy endpoint address
     * @return array<string, mixed>
     */
    public static function managed(?string $proxyAddress = null): array
    {
        $assertion = [
            'type' => 'wsd:DiscoveryMode',
            'namespace' => self::NAMESPACE_URI,
            'mode' => 'managed',
        ];

        if ($proxyAddress !== null) {
            $assertion['proxyAddress'] = $proxyAddress;
        }

        return $assertion;
    }

    /**
     * Create a discovery endpoint assertion.
     *
     * @param  string               $address The discovery endpoint address
     * @return array<string, mixed>
     */
    public static function discoveryEndpoint(string $address): array
    {
        return [
            'type' => 'wsd:DiscoveryEndpoint',
            'namespace' => self::NAMESPACE_URI,
            'address' => $address,
        ];
    }

    /**
     * Create a suppression policy assertion.
     *
     * Controls Hello/Bye message suppression behavior.
     *
     * @param  bool                 $suppressHello Suppress Hello messages
     * @param  bool                 $suppressBye   Suppress Bye messages
     * @return array<string, mixed>
     */
    public static function suppression(bool $suppressHello = false, bool $suppressBye = false): array
    {
        return [
            'type' => 'wsd:Suppression',
            'namespace' => self::NAMESPACE_URI,
            'suppressHello' => $suppressHello,
            'suppressBye' => $suppressBye,
        ];
    }
}
