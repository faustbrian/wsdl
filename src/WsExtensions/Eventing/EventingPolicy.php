<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Eventing;

/**
 * Factory for WS-Eventing policy assertions.
 *
 * Provides static methods that return assertion arrays for use with WS-Policy
 * to describe eventing capabilities and requirements.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class EventingPolicy
{
    public const string NAMESPACE_URI = 'http://schemas.xmlsoap.org/ws/2004/08/eventing';

    /**
     * Create an EventSource assertion.
     *
     * This assertion indicates that the service is an event source
     * and supports WS-Eventing subscriptions.
     *
     * @return array<string, mixed>
     */
    public static function eventSource(): array
    {
        return [
            'type' => 'wse:EventSource',
            'namespace' => self::NAMESPACE_URI,
        ];
    }

    /**
     * Create a SubscriptionPolicy assertion.
     *
     * This assertion describes the subscription policies supported
     * by the event source, such as delivery modes and filter dialects.
     *
     * @param  array<string, mixed>  $policies
     * @return array<string, mixed>
     */
    public static function subscriptionPolicy(array $policies = []): array
    {
        return [
            'type' => 'wse:SubscriptionPolicy',
            'namespace' => self::NAMESPACE_URI,
            'policies' => $policies,
        ];
    }
}
