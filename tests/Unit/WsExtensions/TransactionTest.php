<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\WsExtensions\Transaction\AtomicTransaction;
use Cline\WsdlBuilder\WsExtensions\Transaction\BusinessActivity;
use Cline\WsdlBuilder\WsExtensions\Transaction\Enums\TransactionFlowType;
use Cline\WsdlBuilder\WsExtensions\Transaction\TransactionFlow;
use Cline\WsdlBuilder\WsExtensions\Transaction\TransactionPolicy;

describe('WS-Transaction', function (): void {
    describe('TransactionFlowType', function (): void {
        describe('Happy Paths', function (): void {
            test('provides Mandatory flow type', function (): void {
                // Arrange & Act
                $flowType = TransactionFlowType::Mandatory;

                // Assert
                expect($flowType->value)->toBe('Mandatory');
            });

            test('provides Supported flow type', function (): void {
                // Arrange & Act
                $flowType = TransactionFlowType::Supported;

                // Assert
                expect($flowType->value)->toBe('Supported');
            });

            test('provides Allowed flow type', function (): void {
                // Arrange & Act
                $flowType = TransactionFlowType::Allowed;

                // Assert
                expect($flowType->value)->toBe('Allowed');
            });

            test('provides NotAllowed flow type', function (): void {
                // Arrange & Act
                $flowType = TransactionFlowType::NotAllowed;

                // Assert
                expect($flowType->value)->toBe('NotAllowed');
            });

            test('enum contains all four flow types', function (): void {
                // Arrange & Act
                $flowTypes = TransactionFlowType::cases();

                // Assert
                expect($flowTypes)->toHaveCount(4)
                    ->and($flowTypes)->toContain(TransactionFlowType::Mandatory)
                    ->and($flowTypes)->toContain(TransactionFlowType::Supported)
                    ->and($flowTypes)->toContain(TransactionFlowType::Allowed)
                    ->and($flowTypes)->toContain(TransactionFlowType::NotAllowed);
            });
        });
    });

    describe('AtomicTransaction', function (): void {
        describe('Happy Paths', function (): void {
            test('creates atomic transaction with default version', function (): void {
                // Arrange & Act
                $at = new AtomicTransaction();

                // Assert
                expect($at->getVersion())->toBe('1.0');
            });

            test('sets version to 1.1', function (): void {
                // Arrange
                $at = new AtomicTransaction();

                // Act
                $result = $at->version('1.1');

                // Assert
                expect($result)->toBe($at)
                    ->and($at->getVersion())->toBe('1.1');
            });

            test('sets version to 1.2', function (): void {
                // Arrange
                $at = new AtomicTransaction();

                // Act
                $result = $at->version('1.2');

                // Assert
                expect($result)->toBe($at)
                    ->and($at->getVersion())->toBe('1.2');
            });

            test('gets configuration as array', function (): void {
                // Arrange
                $at = new AtomicTransaction();
                $at->version('1.1');

                // Act
                $config = $at->getConfig();

                // Assert
                expect($config)->toBe([
                    'version' => '1.1',
                ]);
            });

            test('end returns parent when parent exists', function (): void {
                // Arrange
                $parent = new \stdClass();
                $at = new AtomicTransaction($parent);

                // Act
                $result = $at->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('end returns config array when no parent exists', function (): void {
                // Arrange
                $at = new AtomicTransaction();
                $at->version('1.2');

                // Act
                $result = $at->end();

                // Assert
                expect($result)->toBeArray()
                    ->and($result)->toHaveKey('version', '1.2');
            });

            test('supports fluent chaining', function (): void {
                // Arrange & Act
                $config = (new AtomicTransaction())
                    ->version('1.2')
                    ->getConfig();

                // Assert
                expect($config['version'])->toBe('1.2');
            });
        });
    });

    describe('BusinessActivity', function (): void {
        describe('Happy Paths', function (): void {
            test('creates business activity with default version', function (): void {
                // Arrange & Act
                $ba = new BusinessActivity();

                // Assert
                expect($ba->getVersion())->toBe('1.0');
            });

            test('sets version to 1.1', function (): void {
                // Arrange
                $ba = new BusinessActivity();

                // Act
                $result = $ba->version('1.1');

                // Assert
                expect($result)->toBe($ba)
                    ->and($ba->getVersion())->toBe('1.1');
            });

            test('sets version to 1.2', function (): void {
                // Arrange
                $ba = new BusinessActivity();

                // Act
                $result = $ba->version('1.2');

                // Assert
                expect($result)->toBe($ba)
                    ->and($ba->getVersion())->toBe('1.2');
            });

            test('gets configuration as array', function (): void {
                // Arrange
                $ba = new BusinessActivity();
                $ba->version('1.1');

                // Act
                $config = $ba->getConfig();

                // Assert
                expect($config)->toBe([
                    'version' => '1.1',
                ]);
            });

            test('end returns parent when parent exists', function (): void {
                // Arrange
                $parent = new \stdClass();
                $ba = new BusinessActivity($parent);

                // Act
                $result = $ba->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('end returns config array when no parent exists', function (): void {
                // Arrange
                $ba = new BusinessActivity();
                $ba->version('1.2');

                // Act
                $result = $ba->end();

                // Assert
                expect($result)->toBeArray()
                    ->and($result)->toHaveKey('version', '1.2');
            });

            test('supports fluent chaining', function (): void {
                // Arrange & Act
                $config = (new BusinessActivity())
                    ->version('1.2')
                    ->getConfig();

                // Assert
                expect($config['version'])->toBe('1.2');
            });
        });
    });

    describe('TransactionFlow', function (): void {
        describe('Happy Paths', function (): void {
            test('creates transaction flow with default supported type', function (): void {
                // Arrange & Act
                $flow = new TransactionFlow();

                // Assert
                expect($flow->getFlowType())->toBe(TransactionFlowType::Supported)
                    ->and($flow->isAtAssertion())->toBeFalse()
                    ->and($flow->isAtAlwaysCapability())->toBeFalse();
            });

            test('sets flow type using enum', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $result = $flow->flowType(TransactionFlowType::Mandatory);

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->getFlowType())->toBe(TransactionFlowType::Mandatory);
            });

            test('sets flow type using string', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $result = $flow->flowType('Allowed');

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->getFlowType())->toBe(TransactionFlowType::Allowed);
            });

            test('sets flow type to mandatory using helper method', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $result = $flow->mandatory();

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->getFlowType())->toBe(TransactionFlowType::Mandatory);
            });

            test('sets flow type to supported using helper method', function (): void {
                // Arrange
                $flow = new TransactionFlow();
                $flow->mandatory();

                // Act
                $result = $flow->supported();

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->getFlowType())->toBe(TransactionFlowType::Supported);
            });

            test('sets flow type to allowed using helper method', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $result = $flow->allowed();

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->getFlowType())->toBe(TransactionFlowType::Allowed);
            });

            test('sets flow type to not allowed using helper method', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $result = $flow->notAllowed();

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->getFlowType())->toBe(TransactionFlowType::NotAllowed);
            });

            test('enables AT assertion', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $result = $flow->atAssertion();

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->isAtAssertion())->toBeTrue();
            });

            test('disables AT assertion', function (): void {
                // Arrange
                $flow = new TransactionFlow();
                $flow->atAssertion();

                // Act
                $result = $flow->atAssertion(false);

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->isAtAssertion())->toBeFalse();
            });

            test('enables AT always capability', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $result = $flow->atAlwaysCapability();

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->isAtAlwaysCapability())->toBeTrue();
            });

            test('disables AT always capability', function (): void {
                // Arrange
                $flow = new TransactionFlow();
                $flow->atAlwaysCapability();

                // Act
                $result = $flow->atAlwaysCapability(false);

                // Assert
                expect($result)->toBe($flow)
                    ->and($flow->isAtAlwaysCapability())->toBeFalse();
            });

            test('gets configuration as array with defaults', function (): void {
                // Arrange
                $flow = new TransactionFlow();

                // Act
                $config = $flow->getConfig();

                // Assert
                expect($config)->toBe([
                    'flowType' => 'Supported',
                ]);
            });

            test('gets configuration as array with AT assertion', function (): void {
                // Arrange
                $flow = new TransactionFlow();
                $flow->mandatory()->atAssertion();

                // Act
                $config = $flow->getConfig();

                // Assert
                expect($config)->toBe([
                    'flowType' => 'Mandatory',
                    'atAssertion' => true,
                ]);
            });

            test('gets configuration as array with AT always capability', function (): void {
                // Arrange
                $flow = new TransactionFlow();
                $flow->supported()->atAlwaysCapability();

                // Act
                $config = $flow->getConfig();

                // Assert
                expect($config)->toBe([
                    'flowType' => 'Supported',
                    'atAlwaysCapability' => true,
                ]);
            });

            test('gets configuration as array with all options', function (): void {
                // Arrange
                $flow = new TransactionFlow();
                $flow->mandatory()->atAssertion()->atAlwaysCapability();

                // Act
                $config = $flow->getConfig();

                // Assert
                expect($config)->toBe([
                    'flowType' => 'Mandatory',
                    'atAssertion' => true,
                    'atAlwaysCapability' => true,
                ]);
            });

            test('end returns parent when parent exists', function (): void {
                // Arrange
                $parent = new \stdClass();
                $flow = new TransactionFlow($parent);

                // Act
                $result = $flow->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('end returns config array when no parent exists', function (): void {
                // Arrange
                $flow = new TransactionFlow();
                $flow->mandatory()->atAssertion();

                // Act
                $result = $flow->end();

                // Assert
                expect($result)->toBeArray()
                    ->and($result)->toHaveKey('flowType', 'Mandatory')
                    ->and($result)->toHaveKey('atAssertion', true);
            });

            test('supports fluent chaining', function (): void {
                // Arrange & Act
                $config = (new TransactionFlow())
                    ->mandatory()
                    ->atAssertion()
                    ->atAlwaysCapability()
                    ->getConfig();

                // Assert
                expect($config)->toHaveKey('flowType', 'Mandatory')
                    ->and($config)->toHaveKey('atAssertion', true)
                    ->and($config)->toHaveKey('atAlwaysCapability', true);
            });
        });
    });

    describe('TransactionPolicy', function (): void {
        describe('Happy Paths', function (): void {
            test('creates atomic transaction', function (): void {
                // Arrange & Act
                $at = TransactionPolicy::atomicTransaction();

                // Assert
                expect($at)->toBeInstanceOf(AtomicTransaction::class)
                    ->and($at->getVersion())->toBe('1.0');
            });

            test('creates atomic transaction with parent', function (): void {
                // Arrange
                $parent = new \stdClass();

                // Act
                $at = TransactionPolicy::atomicTransaction($parent);
                $result = $at->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('creates business activity', function (): void {
                // Arrange & Act
                $ba = TransactionPolicy::businessActivity();

                // Assert
                expect($ba)->toBeInstanceOf(BusinessActivity::class)
                    ->and($ba->getVersion())->toBe('1.0');
            });

            test('creates business activity with parent', function (): void {
                // Arrange
                $parent = new \stdClass();

                // Act
                $ba = TransactionPolicy::businessActivity($parent);
                $result = $ba->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('creates transaction flow', function (): void {
                // Arrange & Act
                $flow = TransactionPolicy::transactionFlow();

                // Assert
                expect($flow)->toBeInstanceOf(TransactionFlow::class)
                    ->and($flow->getFlowType())->toBe(TransactionFlowType::Supported);
            });

            test('creates transaction flow with parent', function (): void {
                // Arrange
                $parent = new \stdClass();

                // Act
                $flow = TransactionPolicy::transactionFlow($parent);
                $result = $flow->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('creates AT assertion array with default version', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::at();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wsat:ATAssertion',
                    'namespace' => TransactionPolicy::NAMESPACE_WSAT,
                    'version' => '1.0',
                ]);
            });

            test('creates AT assertion array with custom version', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::at('1.2');

                // Assert
                expect($assertion)->toHaveKey('version', '1.2');
            });

            test('creates BA assertion array with default version', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::ba();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wsba:BAAssertion',
                    'namespace' => TransactionPolicy::NAMESPACE_WSBA,
                    'version' => '1.0',
                ]);
            });

            test('creates BA assertion array with custom version', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::ba('1.2');

                // Assert
                expect($assertion)->toHaveKey('version', '1.2');
            });

            test('creates coordination context assertion', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::coordinationContext();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wscoor:CoordinationContext',
                    'namespace' => TransactionPolicy::NAMESPACE_WSCOOR,
                ]);
            });

            test('creates AT always capability assertion', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::atAlwaysCapability();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wsat:ATAlwaysCapability',
                    'namespace' => TransactionPolicy::NAMESPACE_WSAT,
                ]);
            });

            test('creates BA atomic outcome assertion', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::baAtomicOutcome();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wsba:BAAtomicOutcome',
                    'namespace' => TransactionPolicy::NAMESPACE_WSBA,
                ]);
            });

            test('creates BA mixed outcome assertion', function (): void {
                // Arrange & Act
                $assertion = TransactionPolicy::baMixedOutcome();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'wsba:BAMixedOutcome',
                    'namespace' => TransactionPolicy::NAMESPACE_WSBA,
                ]);
            });

            test('verifies correct namespace constants', function (): void {
                // Assert
                expect(TransactionPolicy::NAMESPACE_WSAT)->toBe('http://docs.oasis-open.org/ws-tx/wsat/2006/06')
                    ->and(TransactionPolicy::NAMESPACE_WSBA)->toBe('http://docs.oasis-open.org/ws-tx/wsba/2006/06')
                    ->and(TransactionPolicy::NAMESPACE_WSCOOR)->toBe('http://docs.oasis-open.org/ws-tx/wscoor/2006/06');
            });
        });
    });

    describe('Integration with WS-Policy', function (): void {
        describe('Happy Paths', function (): void {
            test('creates atomic transaction with version and integrates with policy', function (): void {
                // Arrange & Act
                $config = TransactionPolicy::atomicTransaction()
                    ->version('1.2')
                    ->getConfig();

                // Assert
                expect($config)->toBeArray()
                    ->and($config)->toHaveKey('version', '1.2');
            });

            test('creates business activity with version and integrates with policy', function (): void {
                // Arrange & Act
                $config = TransactionPolicy::businessActivity()
                    ->version('1.1')
                    ->getConfig();

                // Assert
                expect($config)->toBeArray()
                    ->and($config)->toHaveKey('version', '1.1');
            });

            test('creates transaction flow configuration with all options', function (): void {
                // Arrange & Act
                $config = TransactionPolicy::transactionFlow()
                    ->mandatory()
                    ->atAssertion()
                    ->atAlwaysCapability()
                    ->getConfig();

                // Assert
                expect($config)->toBeArray()
                    ->and($config)->toHaveKey('flowType', 'Mandatory')
                    ->and($config)->toHaveKey('atAssertion', true)
                    ->and($config)->toHaveKey('atAlwaysCapability', true);
            });

            test('creates complex transaction scenario with AT and flow', function (): void {
                // Arrange
                $atConfig = TransactionPolicy::atomicTransaction()
                    ->version('1.2')
                    ->getConfig();

                $flowConfig = TransactionPolicy::transactionFlow()
                    ->mandatory()
                    ->atAssertion()
                    ->getConfig();

                // Assert
                expect($atConfig)->toHaveKey('version', '1.2')
                    ->and($flowConfig)->toHaveKey('flowType', 'Mandatory')
                    ->and($flowConfig)->toHaveKey('atAssertion', true);
            });

            test('creates complex transaction scenario with BA and flow', function (): void {
                // Arrange
                $baConfig = TransactionPolicy::businessActivity()
                    ->version('1.1')
                    ->getConfig();

                $flowConfig = TransactionPolicy::transactionFlow()
                    ->supported()
                    ->getConfig();

                // Assert
                expect($baConfig)->toHaveKey('version', '1.1')
                    ->and($flowConfig)->toHaveKey('flowType', 'Supported');
            });

            test('supports multiple assertion types for policy', function (): void {
                // Arrange & Act
                $at = TransactionPolicy::at('1.2');
                $ba = TransactionPolicy::ba('1.1');
                $coor = TransactionPolicy::coordinationContext();
                $atCap = TransactionPolicy::atAlwaysCapability();

                // Assert
                expect($at)->toHaveKey('type', 'wsat:ATAssertion')
                    ->and($ba)->toHaveKey('type', 'wsba:BAAssertion')
                    ->and($coor)->toHaveKey('type', 'wscoor:CoordinationContext')
                    ->and($atCap)->toHaveKey('type', 'wsat:ATAlwaysCapability');
            });
        });
    });
});
