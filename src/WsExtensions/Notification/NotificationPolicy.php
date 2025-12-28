<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Notification;

/**
 * Factory for WS-Notification policy assertions.
 *
 * Provides static methods for creating notification producers, consumers,
 * and topic definitions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class NotificationPolicy
{
    public const string NAMESPACE_WSN = 'http://docs.oasis-open.org/wsn/b-2';

    public const string NAMESPACE_WST = 'http://docs.oasis-open.org/wsn/t-1';

    /**
     * Create a NotificationProducer.
     */
    public static function notificationProducer(): NotificationProducer
    {
        return new NotificationProducer();
    }

    /**
     * Create a NotificationConsumer with an endpoint reference.
     */
    public static function notificationConsumer(\Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference $endpoint): NotificationConsumer
    {
        return new NotificationConsumer($endpoint);
    }

    /**
     * Create a Topic definition.
     *
     * @param array<int, string> $messageTypes
     * @param array<int, Topic>  $children
     */
    public static function topic(string $name, array $messageTypes = [], array $children = []): Topic
    {
        return new Topic($name, $messageTypes, $children);
    }
}
