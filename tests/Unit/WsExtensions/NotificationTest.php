<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Notification\Enums\TopicDialect;
use Cline\WsdlBuilder\WsExtensions\Notification\NotificationConsumer;
use Cline\WsdlBuilder\WsExtensions\Notification\NotificationPolicy;
use Cline\WsdlBuilder\WsExtensions\Notification\NotificationProducer;
use Cline\WsdlBuilder\WsExtensions\Notification\Subscribe;
use Cline\WsdlBuilder\WsExtensions\Notification\Topic;
use Cline\WsdlBuilder\WsExtensions\Notification\TopicExpression;

describe('WS-Notification', function (): void {
    describe('TopicDialect', function (): void {
        describe('Happy Paths', function (): void {
            test('provides Simple dialect', function (): void {
                // Arrange & Act
                $dialect = TopicDialect::Simple;

                // Assert
                expect($dialect->value)->toBe('http://docs.oasis-open.org/wsn/t-1/TopicExpression/Simple');
            });

            test('provides Concrete dialect', function (): void {
                // Arrange & Act
                $dialect = TopicDialect::Concrete;

                // Assert
                expect($dialect->value)->toBe('http://docs.oasis-open.org/wsn/t-1/TopicExpression/Concrete');
            });

            test('provides Full dialect', function (): void {
                // Arrange & Act
                $dialect = TopicDialect::Full;

                // Assert
                expect($dialect->value)->toBe('http://docs.oasis-open.org/wsn/t-1/TopicExpression/Full');
            });

            test('provides XPath dialect', function (): void {
                // Arrange & Act
                $dialect = TopicDialect::XPath;

                // Assert
                expect($dialect->value)->toBe('http://www.w3.org/TR/1999/REC-xpath-19991116');
            });
        });
    });

    describe('TopicExpression', function (): void {
        describe('Happy Paths', function (): void {
            test('creates topic expression with Simple dialect', function (): void {
                // Arrange & Act
                $expression = new TopicExpression(TopicDialect::Simple, 'stock/ticker');

                // Assert
                expect($expression->getDialect())->toBe(TopicDialect::Simple)
                    ->and($expression->getValue())->toBe('stock/ticker');
            });

            test('creates topic expression with Concrete dialect', function (): void {
                // Arrange & Act
                $expression = new TopicExpression(TopicDialect::Concrete, 'tns:StockTickerTopic');

                // Assert
                expect($expression->getDialect())->toBe(TopicDialect::Concrete)
                    ->and($expression->getValue())->toBe('tns:StockTickerTopic');
            });

            test('creates topic expression with XPath dialect', function (): void {
                // Arrange & Act
                $expression = new TopicExpression(TopicDialect::XPath, '//stock[@symbol="AAPL"]');

                // Assert
                expect($expression->getDialect())->toBe(TopicDialect::XPath)
                    ->and($expression->getValue())->toBe('//stock[@symbol="AAPL"]');
            });
        });
    });

    describe('Topic', function (): void {
        describe('Happy Paths', function (): void {
            test('creates topic with name', function (): void {
                // Arrange & Act
                $topic = new Topic('StockTicker');

                // Assert
                expect($topic->getName())->toBe('StockTicker')
                    ->and($topic->getMessageTypes())->toBeEmpty()
                    ->and($topic->getChildren())->toBeEmpty();
            });

            test('creates topic with message types', function (): void {
                // Arrange & Act
                $topic = new Topic('StockTicker', ['tns:TickerMessage', 'tns:PriceUpdate']);

                // Assert
                expect($topic->getMessageTypes())->toBe(['tns:TickerMessage', 'tns:PriceUpdate']);
            });

            test('adds message type to topic', function (): void {
                // Arrange
                $topic = new Topic('StockTicker');

                // Act
                $topic->addMessageType('tns:TickerMessage');

                // Assert
                expect($topic->getMessageTypes())->toBe(['tns:TickerMessage']);
            });

            test('creates topic hierarchy with children', function (): void {
                // Arrange
                $child1 = new Topic('Technology');
                $child2 = new Topic('Finance');
                $parent = new Topic('News', [], [$child1, $child2]);

                // Assert
                expect($parent->getChildren())->toHaveCount(2)
                    ->and($parent->getChildren()[0]->getName())->toBe('Technology')
                    ->and($parent->getChildren()[1]->getName())->toBe('Finance');
            });

            test('adds child topic', function (): void {
                // Arrange
                $parent = new Topic('News');
                $child = new Topic('Technology');

                // Act
                $parent->addChild($child);

                // Assert
                expect($parent->getChildren())->toHaveCount(1)
                    ->and($parent->getChildren()[0]->getName())->toBe('Technology');
            });

            test('creates deep topic hierarchy', function (): void {
                // Arrange
                $tech = new Topic('Technology');
                $finance = new Topic('Finance');
                $news = new Topic('News', [], [$tech, $finance]);
                $root = new Topic('Root', [], [$news]);

                // Assert
                expect($root->getChildren())->toHaveCount(1)
                    ->and($root->getChildren()[0]->getName())->toBe('News')
                    ->and($root->getChildren()[0]->getChildren())->toHaveCount(2)
                    ->and($root->getChildren()[0]->getChildren()[0]->getName())->toBe('Technology')
                    ->and($root->getChildren()[0]->getChildren()[1]->getName())->toBe('Finance');
            });
        });
    });

    describe('NotificationProducer', function (): void {
        describe('Happy Paths', function (): void {
            test('creates notification producer', function (): void {
                // Arrange & Act
                $producer = new NotificationProducer();

                // Assert
                expect($producer->getTopicExpression())->toBeNull()
                    ->and($producer->isFixedTopicSet())->toBeFalse()
                    ->and($producer->getTopicExpressionDialects())->toBeEmpty();
            });

            test('sets topic expression on producer', function (): void {
                // Arrange
                $producer = new NotificationProducer();

                // Act
                $producer->topicExpression(TopicDialect::Simple, 'stock/ticker');

                // Assert
                expect($producer->getTopicExpression())->not->toBeNull()
                    ->and($producer->getTopicExpression()?->getDialect())->toBe(TopicDialect::Simple)
                    ->and($producer->getTopicExpression()?->getValue())->toBe('stock/ticker');
            });

            test('sets fixed topic set', function (): void {
                // Arrange
                $producer = new NotificationProducer();

                // Act
                $producer->fixedTopicSet(true);

                // Assert
                expect($producer->isFixedTopicSet())->toBeTrue();
            });

            test('adds topic expression dialects', function (): void {
                // Arrange
                $producer = new NotificationProducer();

                // Act
                $producer->addTopicExpressionDialect(TopicDialect::Simple)
                    ->addTopicExpressionDialect(TopicDialect::Concrete);

                // Assert
                expect($producer->getTopicExpressionDialects())->toHaveCount(2)
                    ->and($producer->getTopicExpressionDialects()[0])->toBe(TopicDialect::Simple)
                    ->and($producer->getTopicExpressionDialects()[1])->toBe(TopicDialect::Concrete);
            });

            test('configures complete producer', function (): void {
                // Arrange
                $producer = new NotificationProducer();

                // Act
                $producer->topicExpression(TopicDialect::Concrete, 'tns:StockTopic')
                    ->fixedTopicSet(true)
                    ->addTopicExpressionDialect(TopicDialect::Simple)
                    ->addTopicExpressionDialect(TopicDialect::Concrete)
                    ->addTopicExpressionDialect(TopicDialect::Full);

                // Assert
                expect($producer->getTopicExpression()?->getValue())->toBe('tns:StockTopic')
                    ->and($producer->isFixedTopicSet())->toBeTrue()
                    ->and($producer->getTopicExpressionDialects())->toHaveCount(3);
            });
        });
    });

    describe('NotificationConsumer', function (): void {
        describe('Happy Paths', function (): void {
            test('creates notification consumer with endpoint reference', function (): void {
                // Arrange
                $endpoint = new EndpointReference('http://example.com/notify');

                // Act
                $consumer = new NotificationConsumer($endpoint);

                // Assert
                expect($consumer->getEndpointReference())->toBe($endpoint)
                    ->and($consumer->getEndpointReference()->getAddress())->toBe('http://example.com/notify');
            });
        });
    });

    describe('Subscribe', function (): void {
        describe('Happy Paths', function (): void {
            test('creates subscription with consumer reference', function (): void {
                // Arrange
                $endpoint = new EndpointReference('http://example.com/notify');

                // Act
                $subscribe = new Subscribe($endpoint);

                // Assert
                expect($subscribe->getConsumerReference())->toBe($endpoint)
                    ->and($subscribe->getFilter())->toBeNull()
                    ->and($subscribe->getInitialTerminationTime())->toBeNull()
                    ->and($subscribe->getSubscriptionPolicy())->toBeEmpty();
            });

            test('sets filter on subscription', function (): void {
                // Arrange
                $endpoint = new EndpointReference('http://example.com/notify');
                $subscribe = new Subscribe($endpoint);
                $filter = new TopicExpression(TopicDialect::Simple, 'stock/ticker');

                // Act
                $subscribe->filter($filter);

                // Assert
                expect($subscribe->getFilter())->toBe($filter);
            });

            test('sets initial termination time', function (): void {
                // Arrange
                $endpoint = new EndpointReference('http://example.com/notify');
                $subscribe = new Subscribe($endpoint);
                $time = new \DateTime('+1 hour');

                // Act
                $subscribe->initialTerminationTime($time);

                // Assert
                expect($subscribe->getInitialTerminationTime())->toBe($time);
            });

            test('adds subscription policy elements', function (): void {
                // Arrange
                $endpoint = new EndpointReference('http://example.com/notify');
                $subscribe = new Subscribe($endpoint);

                // Act
                $subscribe->addPolicyElement('MessageRate', 100)
                    ->addPolicyElement('Priority', 'high');

                // Assert
                expect($subscribe->getSubscriptionPolicy())->toBe([
                    'MessageRate' => 100,
                    'Priority' => 'high',
                ]);
            });

            test('creates complete subscription', function (): void {
                // Arrange
                $endpoint = new EndpointReference('http://example.com/notify');
                $subscribe = new Subscribe($endpoint);
                $filter = new TopicExpression(TopicDialect::Concrete, 'tns:StockTopic');
                $time = new \DateTime('+1 day');

                // Act
                $subscribe->filter($filter)
                    ->initialTerminationTime($time)
                    ->addPolicyElement('MessageRate', 50)
                    ->addPolicyElement('UseRaw', true);

                // Assert
                expect($subscribe->getConsumerReference()->getAddress())->toBe('http://example.com/notify')
                    ->and($subscribe->getFilter()?->getValue())->toBe('tns:StockTopic')
                    ->and($subscribe->getInitialTerminationTime())->toBe($time)
                    ->and($subscribe->getSubscriptionPolicy())->toHaveCount(2);
            });
        });
    });

    describe('NotificationPolicy', function (): void {
        describe('Happy Paths', function (): void {
            test('creates notification producer via factory', function (): void {
                // Arrange & Act
                $producer = NotificationPolicy::notificationProducer();

                // Assert
                expect($producer)->toBeInstanceOf(NotificationProducer::class);
            });

            test('creates notification consumer via factory', function (): void {
                // Arrange
                $endpoint = new EndpointReference('http://example.com/notify');

                // Act
                $consumer = NotificationPolicy::notificationConsumer($endpoint);

                // Assert
                expect($consumer)->toBeInstanceOf(NotificationConsumer::class)
                    ->and($consumer->getEndpointReference())->toBe($endpoint);
            });

            test('creates topic via factory', function (): void {
                // Arrange & Act
                $topic = NotificationPolicy::topic('StockTicker');

                // Assert
                expect($topic)->toBeInstanceOf(Topic::class)
                    ->and($topic->getName())->toBe('StockTicker');
            });

            test('creates topic with message types via factory', function (): void {
                // Arrange & Act
                $topic = NotificationPolicy::topic('StockTicker', ['tns:TickerMessage']);

                // Assert
                expect($topic->getMessageTypes())->toBe(['tns:TickerMessage']);
            });

            test('creates topic with children via factory', function (): void {
                // Arrange
                $child = NotificationPolicy::topic('Technology');

                // Act
                $parent = NotificationPolicy::topic('News', [], [$child]);

                // Assert
                expect($parent->getChildren())->toHaveCount(1)
                    ->and($parent->getChildren()[0])->toBe($child);
            });
        });
    });

    describe('Integration', function (): void {
        describe('Happy Paths', function (): void {
            test('builds complete notification scenario', function (): void {
                // Arrange - Create producer
                $producer = NotificationPolicy::notificationProducer();
                $producer->topicExpression(TopicDialect::Simple, 'stock/*')
                    ->fixedTopicSet(true)
                    ->addTopicExpressionDialect(TopicDialect::Simple)
                    ->addTopicExpressionDialect(TopicDialect::Concrete);

                // Arrange - Create topic hierarchy
                $tickerTopic = NotificationPolicy::topic('Ticker', ['tns:TickerMessage']);
                $priceTopic = NotificationPolicy::topic('Price', ['tns:PriceMessage']);
                $stockTopic = NotificationPolicy::topic('Stock', [], [$tickerTopic, $priceTopic]);

                // Arrange - Create consumer
                $endpoint = new EndpointReference('http://subscriber.example.com/notify');
                $consumer = NotificationPolicy::notificationConsumer($endpoint);

                // Arrange - Create subscription
                $subscribe = new Subscribe($endpoint);
                $filter = new TopicExpression(TopicDialect::Simple, 'stock/ticker');
                $subscribe->filter($filter)
                    ->initialTerminationTime(new \DateTime('+1 week'))
                    ->addPolicyElement('MessageRate', 10);

                // Assert
                expect($producer->isFixedTopicSet())->toBeTrue()
                    ->and($producer->getTopicExpressionDialects())->toHaveCount(2)
                    ->and($stockTopic->getChildren())->toHaveCount(2)
                    ->and($consumer->getEndpointReference()->getAddress())->toBe('http://subscriber.example.com/notify')
                    ->and($subscribe->getFilter()?->getDialect())->toBe(TopicDialect::Simple)
                    ->and($subscribe->getSubscriptionPolicy())->toHaveKey('MessageRate');
            });

            test('handles multiple subscribers to same producer', function (): void {
                // Arrange
                $producer = NotificationPolicy::notificationProducer();
                $producer->topicExpression(TopicDialect::Concrete, 'tns:NewsTopic')
                    ->fixedTopicSet(true);

                $endpoint1 = new EndpointReference('http://subscriber1.example.com/notify');
                $endpoint2 = new EndpointReference('http://subscriber2.example.com/notify');

                $subscribe1 = new Subscribe($endpoint1);
                $subscribe1->filter(new TopicExpression(TopicDialect::Simple, 'news/tech'));

                $subscribe2 = new Subscribe($endpoint2);
                $subscribe2->filter(new TopicExpression(TopicDialect::Simple, 'news/finance'));

                // Assert
                expect($producer->getTopicExpression()?->getValue())->toBe('tns:NewsTopic')
                    ->and($subscribe1->getConsumerReference()->getAddress())->toBe('http://subscriber1.example.com/notify')
                    ->and($subscribe2->getConsumerReference()->getAddress())->toBe('http://subscriber2.example.com/notify')
                    ->and($subscribe1->getFilter()?->getValue())->toBe('news/tech')
                    ->and($subscribe2->getFilter()?->getValue())->toBe('news/finance');
            });
        });
    });
});
