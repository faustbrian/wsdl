<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Eventing\Delivery;
use Cline\WsdlBuilder\WsExtensions\Eventing\Enums\DeliveryMode;
use Cline\WsdlBuilder\WsExtensions\Eventing\EventingPolicy;
use Cline\WsdlBuilder\WsExtensions\Eventing\Filter;
use Cline\WsdlBuilder\WsExtensions\Eventing\Subscribe;
use Cline\WsdlBuilder\WsExtensions\Eventing\Subscription;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyOperator;
use Illuminate\Support\Facades\Date;

describe('WS-Eventing Support', function (): void {
    describe('DeliveryMode', function (): void {
        describe('Happy Paths', function (): void {
            test('provides Push delivery mode', function (): void {
                // Arrange & Act
                $mode = DeliveryMode::Push;

                // Assert
                expect($mode->value)->toBe('http://schemas.xmlsoap.org/ws/2004/08/eventing/DeliveryModes/Push');
            });

            test('provides Pull delivery mode', function (): void {
                // Arrange & Act
                $mode = DeliveryMode::Pull;

                // Assert
                expect($mode->value)->toBe('http://schemas.xmlsoap.org/ws/2004/08/eventing/DeliveryModes/Pull');
            });

            test('provides Wrapped delivery mode', function (): void {
                // Arrange & Act
                $mode = DeliveryMode::Wrapped;

                // Assert
                expect($mode->value)->toBe('http://schemas.xmlsoap.org/ws/2004/08/eventing/DeliveryModes/Wrapped');
            });

            test('enum contains all three delivery modes', function (): void {
                // Arrange & Act
                $modes = DeliveryMode::cases();

                // Assert
                expect($modes)->toHaveCount(3)
                    ->and($modes)->toContain(DeliveryMode::Push)
                    ->and($modes)->toContain(DeliveryMode::Pull)
                    ->and($modes)->toContain(DeliveryMode::Wrapped);
            });
        });
    });

    describe('Filter', function (): void {
        describe('Happy Paths', function (): void {
            test('creates filter with dialect and content', function (): void {
                // Arrange & Act
                $filter = new Filter(
                    'http://www.w3.org/TR/1999/REC-xpath-19991116',
                    '//event[@type="critical"]',
                );

                // Assert
                expect($filter->getDialect())->toBe('http://www.w3.org/TR/1999/REC-xpath-19991116')
                    ->and($filter->getContent())->toBe('//event[@type="critical"]');
            });

            test('creates filter with XPath dialect', function (): void {
                // Arrange & Act
                $filter = new Filter(
                    'http://www.w3.org/TR/1999/REC-xpath-19991116',
                    '/events/event[@severity="high"]',
                );

                // Assert
                expect($filter->getDialect())->toStartWith('http://www.w3.org')
                    ->and($filter->getContent())->toStartWith('/events');
            });

            test('creates filter with custom dialect', function (): void {
                // Arrange & Act
                $filter = new Filter(
                    'http://example.com/custom-filter',
                    'priority > 5 AND category = "alert"',
                );

                // Assert
                expect($filter->getDialect())->toBe('http://example.com/custom-filter')
                    ->and($filter->getContent())->toContain('priority')
                    ->and($filter->getContent())->toContain('category');
            });
        });
    });

    describe('Delivery', function (): void {
        describe('Happy Paths', function (): void {
            test('creates delivery with notifyTo endpoint', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');

                // Act
                $delivery = new Delivery($notifyTo);

                // Assert
                expect($delivery->getNotifyTo())->toBe($notifyTo)
                    ->and($delivery->getNotifyTo()->getAddress())->toBe('http://subscriber.example.com/notify')
                    ->and($delivery->getMode())->toBe(DeliveryMode::Push);
            });

            test('creates delivery with Push mode', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');

                // Act
                $delivery = new Delivery($notifyTo, DeliveryMode::Push);

                // Assert
                expect($delivery->getMode())->toBe(DeliveryMode::Push);
            });

            test('creates delivery with Pull mode', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');

                // Act
                $delivery = new Delivery($notifyTo, DeliveryMode::Pull);

                // Assert
                expect($delivery->getMode())->toBe(DeliveryMode::Pull);
            });

            test('creates delivery with Wrapped mode', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');

                // Act
                $delivery = new Delivery($notifyTo, DeliveryMode::Wrapped);

                // Assert
                expect($delivery->getMode())->toBe(DeliveryMode::Wrapped);
            });

            test('defaults to Push mode when not specified', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');

                // Act
                $delivery = new Delivery($notifyTo);

                // Assert
                expect($delivery->getMode())->toBe(DeliveryMode::Push);
            });
        });
    });

    describe('Subscribe', function (): void {
        describe('Happy Paths', function (): void {
            test('creates subscribe request with delivery', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $delivery = new Delivery($notifyTo);

                // Act
                $subscribe = new Subscribe($delivery);

                // Assert
                expect($subscribe->getDelivery())->toBe($delivery)
                    ->and($subscribe->getExpires())->toBeNull()
                    ->and($subscribe->getEndTo())->toBeNull()
                    ->and($subscribe->getFilter())->toBeNull();
            });

            test('creates subscribe request with string expiration', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $delivery = new Delivery($notifyTo);

                // Act
                $subscribe = new Subscribe($delivery, 'PT1H');

                // Assert
                expect($subscribe->getExpires())->toBe('PT1H');
            });

            test('creates subscribe request with DateTime expiration', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $delivery = new Delivery($notifyTo);
                $expires = Date::parse('2025-12-31T23:59:59Z');

                // Act
                $subscribe = new Subscribe($delivery, $expires);

                // Assert
                expect($subscribe->getExpires())->toBe($expires)
                    ->and($subscribe->getExpires())->toBeInstanceOf(DateTimeInterface::class);
            });

            test('creates subscribe request with endTo', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $delivery = new Delivery($notifyTo);
                $endTo = new EndpointReference('http://subscriber.example.com/end-notification');

                // Act
                $subscribe = new Subscribe($delivery)->endTo($endTo);

                // Assert
                expect($subscribe->getEndTo())->toBe($endTo)
                    ->and($subscribe->getEndTo()->getAddress())->toBe('http://subscriber.example.com/end-notification');
            });

            test('creates subscribe request with filter', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $delivery = new Delivery($notifyTo);
                $filter = new Filter(
                    'http://www.w3.org/TR/1999/REC-xpath-19991116',
                    '//event[@type="critical"]',
                );

                // Act
                $subscribe = new Subscribe($delivery)->filter($filter);

                // Assert
                expect($subscribe->getFilter())->toBe($filter)
                    ->and($subscribe->getFilter()->getDialect())->toBe('http://www.w3.org/TR/1999/REC-xpath-19991116')
                    ->and($subscribe->getFilter()->getContent())->toBe('//event[@type="critical"]');
            });

            test('creates complete subscribe request with all properties', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $delivery = new Delivery($notifyTo, DeliveryMode::Push);
                $endTo = new EndpointReference('http://subscriber.example.com/end-notification');
                $filter = new Filter(
                    'http://www.w3.org/TR/1999/REC-xpath-19991116',
                    '//event[@priority="high"]',
                );

                // Act
                $subscribe = new Subscribe($delivery, 'PT2H')
                    ->endTo($endTo)
                    ->filter($filter);

                // Assert
                expect($subscribe->getDelivery())->toBe($delivery)
                    ->and($subscribe->getExpires())->toBe('PT2H')
                    ->and($subscribe->getEndTo())->toBe($endTo)
                    ->and($subscribe->getFilter())->toBe($filter);
            });

            test('fluent interface returns same instance', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $delivery = new Delivery($notifyTo);
                $endTo = new EndpointReference('http://subscriber.example.com/end-notification');
                $filter = new Filter('http://example.com/filter', 'test');

                // Act
                $subscribe = new Subscribe($delivery);
                $result1 = $subscribe->endTo($endTo);
                $result2 = $subscribe->filter($filter);

                // Assert
                expect($result1)->toBe($subscribe)
                    ->and($result2)->toBe($subscribe);
            });
        });
    });

    describe('Subscription', function (): void {
        describe('Happy Paths', function (): void {
            test('creates subscription with manager and expiration', function (): void {
                // Arrange
                $manager = new EndpointReference('http://eventsource.example.com/subscriptions/123');

                // Act
                $subscription = new Subscription($manager, '2025-12-31T23:59:59Z');

                // Assert
                expect($subscription->getSubscriptionManager())->toBe($manager)
                    ->and($subscription->getSubscriptionManager()->getAddress())->toBe('http://eventsource.example.com/subscriptions/123')
                    ->and($subscription->getExpires())->toBe('2025-12-31T23:59:59Z');
            });

            test('creates subscription with relative expiration', function (): void {
                // Arrange
                $manager = new EndpointReference('http://eventsource.example.com/subscriptions/456');

                // Act
                $subscription = new Subscription($manager, 'PT1H');

                // Assert
                expect($subscription->getExpires())->toBe('PT1H');
            });

            test('subscription manager includes reference parameters', function (): void {
                // Arrange
                $manager = new EndpointReference('http://eventsource.example.com/subscriptions/789');
                $manager->referenceParameters()->parameter('http://example.com', 'subscriptionId', '789');

                // Act
                $subscription = new Subscription($manager, 'PT2H');

                // Assert
                expect($subscription->getSubscriptionManager())->toBe($manager)
                    ->and($subscription->getSubscriptionManager()->getReferenceParameters())->not->toBeNull();
            });
        });
    });

    describe('EventingPolicy', function (): void {
        describe('Happy Paths', function (): void {
            test('creates eventSource assertion', function (): void {
                // Arrange & Act
                $assertion = EventingPolicy::eventSource();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wse:EventSource',
                    'namespace' => EventingPolicy::NAMESPACE_URI,
                ])
                    ->and($assertion)->toHaveKey('type')
                    ->and($assertion)->toHaveKey('namespace')
                    ->and($assertion['namespace'])->toBe('http://schemas.xmlsoap.org/ws/2004/08/eventing');
            });

            test('creates subscriptionPolicy assertion without policies', function (): void {
                // Arrange & Act
                $assertion = EventingPolicy::subscriptionPolicy();

                // Assert
                expect($assertion)->toHaveKey('type')
                    ->and($assertion)->toHaveKey('namespace')
                    ->and($assertion)->toHaveKey('policies')
                    ->and($assertion['type'])->toBe('wse:SubscriptionPolicy')
                    ->and($assertion['namespace'])->toBe('http://schemas.xmlsoap.org/ws/2004/08/eventing')
                    ->and($assertion['policies'])->toBeArray()
                    ->and($assertion['policies'])->toBeEmpty();
            });

            test('creates subscriptionPolicy assertion with policies', function (): void {
                // Arrange & Act
                $assertion = EventingPolicy::subscriptionPolicy([
                    'deliveryModes' => ['Push', 'Pull'],
                    'filterDialects' => ['http://www.w3.org/TR/1999/REC-xpath-19991116'],
                ]);

                // Assert
                expect($assertion['policies'])->toHaveKey('deliveryModes')
                    ->and($assertion['policies'])->toHaveKey('filterDialects')
                    ->and($assertion['policies']['deliveryModes'])->toContain('Push')
                    ->and($assertion['policies']['deliveryModes'])->toContain('Pull')
                    ->and($assertion['policies']['filterDialects'])->toContain('http://www.w3.org/TR/1999/REC-xpath-19991116');
            });

            test('namespace URI constant is correct', function (): void {
                // Arrange & Act & Assert
                expect(EventingPolicy::NAMESPACE_URI)->toBe('http://schemas.xmlsoap.org/ws/2004/08/eventing');
            });

            test('assertion type uses wse prefix', function (): void {
                // Arrange & Act
                $eventSource = EventingPolicy::eventSource();
                $subscriptionPolicy = EventingPolicy::subscriptionPolicy();

                // Assert
                expect($eventSource['type'])->toStartWith('wse:')
                    ->and($eventSource['type'])->toBe('wse:EventSource')
                    ->and($subscriptionPolicy['type'])->toStartWith('wse:')
                    ->and($subscriptionPolicy['type'])->toBe('wse:SubscriptionPolicy');
            });
        });
    });

    describe('Eventing policy integration with WS-Policy', function (): void {
        describe('Happy Paths', function (): void {
            test('adds EventSource policy to WSDL', function (): void {
                // Arrange
                $wsdl = Wsdl::create('EventingService', 'http://test.example.com/');

                // Act
                $policy = $wsdl->policy('EventSourcePolicy')
                    ->all()
                    ->assertion(
                        EventingPolicy::NAMESPACE_URI,
                        'wse:EventSource',
                    );

                // Assert
                expect($wsdl->getPolicies())->toHaveCount(1)
                    ->and($policy)->toBeInstanceOf(PolicyOperator::class);
            });

            test('adds SubscriptionPolicy to WSDL', function (): void {
                // Arrange
                $wsdl = Wsdl::create('EventingService', 'http://test.example.com/');

                // Act
                $policy = $wsdl->policy('SubscriptionPolicy')
                    ->all()
                    ->assertion(
                        EventingPolicy::NAMESPACE_URI,
                        'wse:SubscriptionPolicy',
                    );

                // Assert
                expect($wsdl->getPolicies())->toHaveCount(1)
                    ->and($policy)->toBeInstanceOf(PolicyOperator::class);
            });

            test('creates EventSource assertion using factory method', function (): void {
                // Arrange
                $wsdl = Wsdl::create('EventingService', 'http://test.example.com/');
                $eventSourceAssertion = EventingPolicy::eventSource();

                // Act
                $policy = $wsdl->policy('EventSourcePolicy')
                    ->all()
                    ->assertion(
                        $eventSourceAssertion['namespace'],
                        $eventSourceAssertion['type'],
                    );

                // Assert
                expect($wsdl->getPolicies())->toHaveCount(1)
                    ->and($policy)->toBeInstanceOf(PolicyOperator::class);
            });

            test('creates SubscriptionPolicy assertion using factory method', function (): void {
                // Arrange
                $wsdl = Wsdl::create('EventingService', 'http://test.example.com/');
                $subscriptionPolicyAssertion = EventingPolicy::subscriptionPolicy([
                    'deliveryModes' => ['Push'],
                ]);

                // Act
                $policy = $wsdl->policy('SubPolicy')
                    ->all()
                    ->assertion(
                        $subscriptionPolicyAssertion['namespace'],
                        $subscriptionPolicyAssertion['type'],
                    );

                // Assert
                expect($wsdl->getPolicies())->toHaveCount(1)
                    ->and($policy)->toBeInstanceOf(PolicyOperator::class);
            });
        });
    });

    describe('WS-Eventing integration', function (): void {
        describe('Happy Paths', function (): void {
            test('creates complete event source service', function (): void {
                // Arrange
                $wsdl = Wsdl::create('AlertService', 'http://example.com/alerts');

                // Add EventSource policy
                $wsdl->policy('AlertEventSourcePolicy')
                    ->all()
                    ->assertion(
                        EventingPolicy::NAMESPACE_URI,
                        'wse:EventSource',
                    );

                // Add SubscriptionPolicy
                $wsdl->policy('AlertSubscriptionPolicy')
                    ->all()
                    ->assertion(
                        EventingPolicy::NAMESPACE_URI,
                        'wse:SubscriptionPolicy',
                    );

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)->toBeString()
                    ->and($xml)->toContain('wsp:Policy')
                    ->and($xml)->toContain('AlertEventSourcePolicy')
                    ->and($xml)->toContain('AlertSubscriptionPolicy')
                    ->and($xml)->toContain('wse:EventSource')
                    ->and($xml)->toContain('wse:SubscriptionPolicy')
                    ->and($xml)->toContain('xmlns:wsp')
                    ->and($xml)->toContain('wsdl:definitions');
            });

            test('creates subscription with all features', function (): void {
                // Arrange
                $notifyTo = new EndpointReference('http://subscriber.example.com/notify');
                $notifyTo->referenceParameters()->parameter('http://example.com', 'subscriberId', 'sub-123');

                $delivery = new Delivery($notifyTo, DeliveryMode::Push);

                $endTo = new EndpointReference('http://subscriber.example.com/end');
                $endTo->referenceParameters()->parameter('http://example.com', 'endReason', 'unsubscribe');

                $filter = new Filter(
                    'http://www.w3.org/TR/1999/REC-xpath-19991116',
                    '//alert[@severity="critical" or @severity="high"]',
                );

                // Act
                $subscribe = new Subscribe($delivery, 'PT24H')
                    ->endTo($endTo)
                    ->filter($filter);

                // Assert
                expect($subscribe->getDelivery()->getNotifyTo()->getAddress())
                    ->toBe('http://subscriber.example.com/notify')
                    ->and($subscribe->getDelivery()->getMode())->toBe(DeliveryMode::Push)
                    ->and($subscribe->getExpires())->toBe('PT24H')
                    ->and($subscribe->getEndTo()->getAddress())->toBe('http://subscriber.example.com/end')
                    ->and($subscribe->getFilter()->getDialect())->toBe('http://www.w3.org/TR/1999/REC-xpath-19991116')
                    ->and($subscribe->getFilter()->getContent())->toContain('critical')
                    ->and($subscribe->getFilter()->getContent())->toContain('high');
            });
        });
    });
});
