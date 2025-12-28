<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Core\Binding;
use Cline\WsdlBuilder\Core\Service;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Addressing\Action;
use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;
use Cline\WsdlBuilder\WsExtensions\Addressing\Enums\AddressingVersion;
use Cline\WsdlBuilder\WsExtensions\Addressing\Metadata;
use Cline\WsdlBuilder\WsExtensions\Addressing\ReferenceParameters;
use Cline\WsdlBuilder\WsExtensions\Policy\Policy;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyAssertion;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyOperator;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyReference;
use Cline\WsdlBuilder\WsExtensions\Security\Enums\AlgorithmSuite;
use Cline\WsdlBuilder\WsExtensions\Security\Enums\SecurityTokenInclusion;
use Cline\WsdlBuilder\WsExtensions\Security\SecurityPolicy;
use Cline\WsdlBuilder\WsExtensions\Security\TokenAssertion;
use Cline\WsdlBuilder\WsExtensions\Security\TransportBinding;
use Cline\WsdlBuilder\WsExtensions\Security\TransportToken;

describe('WS-Policy', function (): void {
    describe('Policy', function (): void {
        describe('Happy Paths', function (): void {
            test('creates policy with id and name', function (): void {
                // Arrange & Act
                $policy = new Policy('TestPolicy', 'TestPolicyName');

                // Assert
                expect($policy->getId())->toBe('TestPolicy')
                    ->and($policy->getName())->toBe('TestPolicyName');
            });

            test('creates policy without id and name', function (): void {
                // Arrange & Act
                $policy = new Policy();

                // Assert
                expect($policy->getId())->toBeNull()
                    ->and($policy->getName())->toBeNull();
            });

            test('adds all operator to policy', function (): void {
                // Arrange
                $policy = new Policy();

                // Act
                $operator = $policy->all();

                // Assert
                expect($operator)->toBeInstanceOf(PolicyOperator::class)
                    ->and($operator->getType())->toBe('all')
                    ->and($policy->getOperators())->toHaveCount(1);
            });

            test('adds exactlyOne operator to policy', function (): void {
                // Arrange
                $policy = new Policy();

                // Act
                $operator = $policy->exactlyOne();

                // Assert
                expect($operator)->toBeInstanceOf(PolicyOperator::class)
                    ->and($operator->getType())->toBe('exactlyOne')
                    ->and($policy->getOperators())->toHaveCount(1);
            });

            test('adds policy assertion without attributes', function (): void {
                // Arrange
                $policy = new Policy();

                // Act
                $result = $policy->assertion('http://example.com', 'TestAssertion');

                // Assert
                expect($result)->toBe($policy)
                    ->and($policy->getAssertions())->toHaveCount(1)
                    ->and($policy->getAssertions()[0]->namespace)->toBe('http://example.com')
                    ->and($policy->getAssertions()[0]->localName)->toBe('TestAssertion')
                    ->and($policy->getAssertions()[0]->attributes)->toBeNull();
            });

            test('adds policy assertion with attributes', function (): void {
                // Arrange
                $policy = new Policy();
                $attributes = ['attr1' => 'value1', 'attr2' => 'value2'];

                // Act
                $result = $policy->assertion('http://example.com', 'TestAssertion', $attributes);

                // Assert
                expect($result)->toBe($policy)
                    ->and($policy->getAssertions()[0]->attributes)->toBe($attributes);
            });

            test('adds policy reference', function (): void {
                // Arrange
                $policy = new Policy();

                // Act
                $result = $policy->reference('#MyPolicy');

                // Assert
                expect($result)->toBe($policy)
                    ->and($policy->getReferences())->toHaveCount(1)
                    ->and($policy->getReferences()[0]->uri)->toBe('#MyPolicy');
            });

            test('adds multiple operators and assertions', function (): void {
                // Arrange
                $policy = new Policy();

                // Act
                $policy->all();
                $policy->exactlyOne();
                $policy->assertion('http://example.com', 'Assertion1');
                $policy->assertion('http://example.com', 'Assertion2');

                // Assert
                expect($policy->getOperators())->toHaveCount(2)
                    ->and($policy->getAssertions())->toHaveCount(2);
            });

            test('end returns parent object', function (): void {
                // Arrange
                $parent = new stdClass();
                $policy = new Policy('TestPolicy', 'TestPolicyName', $parent);

                // Act
                $result = $policy->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('end returns null when no parent object', function (): void {
                // Arrange
                $policy = new Policy();

                // Act
                $result = $policy->end();

                // Assert
                expect($result)->toBeNull();
            });
        });
    });

    describe('PolicyOperator', function (): void {
        describe('Happy Paths', function (): void {
            test('creates nested all operator', function (): void {
                // Arrange
                $policy = new Policy();
                $operator = $policy->all();

                // Act
                $nestedOperator = $operator->all();

                // Assert
                expect($nestedOperator)->toBeInstanceOf(PolicyOperator::class)
                    ->and($nestedOperator->getType())->toBe('all')
                    ->and($operator->getNestedOperators())->toHaveCount(1);
            });

            test('creates nested exactlyOne operator', function (): void {
                // Arrange
                $policy = new Policy();
                $operator = $policy->all();

                // Act
                $nestedOperator = $operator->exactlyOne();

                // Assert
                expect($nestedOperator)->toBeInstanceOf(PolicyOperator::class)
                    ->and($nestedOperator->getType())->toBe('exactlyOne')
                    ->and($operator->getNestedOperators())->toHaveCount(1);
            });

            test('adds assertion to operator', function (): void {
                // Arrange
                $policy = new Policy();
                $operator = $policy->all();

                // Act
                $result = $operator->assertion('http://example.com', 'TestAssertion');

                // Assert
                expect($result)->toBe($operator)
                    ->and($operator->getAssertions())->toHaveCount(1)
                    ->and($operator->getAssertions()[0]->namespace)->toBe('http://example.com')
                    ->and($operator->getAssertions()[0]->localName)->toBe('TestAssertion');
            });

            test('creates nested policy within operator', function (): void {
                // Arrange
                $policy = new Policy();
                $operator = $policy->all();

                // Act
                $nestedPolicy = $operator->policy('NestedPolicy', 'NestedName');

                // Assert
                expect($nestedPolicy)->toBeInstanceOf(Policy::class)
                    ->and($nestedPolicy->getId())->toBe('NestedPolicy')
                    ->and($nestedPolicy->getName())->toBe('NestedName')
                    ->and($operator->getNestedPolicies())->toHaveCount(1);
            });

            test('end returns parent policy', function (): void {
                // Arrange
                $policy = new Policy();
                $operator = $policy->all();

                // Act
                $result = $operator->end();

                // Assert
                expect($result)->toBe($policy);
            });

            test('end returns parent operator when nested', function (): void {
                // Arrange
                $policy = new Policy();
                $operator = $policy->all();
                $nestedOperator = $operator->exactlyOne();

                // Act
                $result = $nestedOperator->end();

                // Assert
                expect($result)->toBe($operator);
            });

            test('supports complex nested structures', function (): void {
                // Arrange
                $policy = new Policy();

                // Act
                $all = $policy->all();
                $exactlyOne = $all->exactlyOne();
                $exactlyOne->assertion('http://example.com', 'Assertion1');
                $nested = $exactlyOne->all();
                $nested->assertion('http://example.com', 'Assertion2');

                $policy->assertion('http://example.com', 'Assertion3');

                // Assert
                $operators = $policy->getOperators();
                expect($operators)->toHaveCount(1)
                    ->and($operators[0]->getNestedOperators())->toHaveCount(1)
                    ->and($policy->getAssertions())->toHaveCount(1);
            });
        });
    });

    describe('PolicyReference', function (): void {
        describe('Happy Paths', function (): void {
            test('creates policy reference with uri only', function (): void {
                // Arrange & Act
                $reference = new PolicyReference('#MyPolicy');

                // Assert
                expect($reference->uri)->toBe('#MyPolicy')
                    ->and($reference->digest)->toBeNull()
                    ->and($reference->digestAlgorithm)->toBeNull();
            });

            test('creates policy reference with digest and algorithm', function (): void {
                // Arrange & Act
                $reference = new PolicyReference(
                    '#MyPolicy',
                    'abc123',
                    'http://www.w3.org/2001/04/xmlenc#sha256',
                );

                // Assert
                expect($reference->uri)->toBe('#MyPolicy')
                    ->and($reference->digest)->toBe('abc123')
                    ->and($reference->digestAlgorithm)->toBe('http://www.w3.org/2001/04/xmlenc#sha256');
            });
        });
    });

    describe('PolicyAssertion', function (): void {
        describe('Happy Paths', function (): void {
            test('creates policy assertion with namespace and local name', function (): void {
                // Arrange & Act
                $assertion = new PolicyAssertion('http://example.com', 'TestAssertion');

                // Assert
                expect($assertion->namespace)->toBe('http://example.com')
                    ->and($assertion->localName)->toBe('TestAssertion')
                    ->and($assertion->attributes)->toBeNull();
            });

            test('creates policy assertion with attributes', function (): void {
                // Arrange
                $attributes = ['attr1' => 'value1', 'attr2' => 'value2'];

                // Act
                $assertion = new PolicyAssertion('http://example.com', 'TestAssertion', $attributes);

                // Assert
                expect($assertion->attributes)->toBe($attributes);
            });
        });
    });

    describe('PolicyAttachment', function (): void {
        describe('Happy Paths', function (): void {
            test('creates inline policy on Wsdl', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $policy = $wsdl->policy('TestPolicy', 'TestPolicyName');

                // Assert
                expect($policy)->toBeInstanceOf(Policy::class)
                    ->and($policy->getId())->toBe('TestPolicy')
                    ->and($policy->getName())->toBe('TestPolicyName')
                    ->and($wsdl->getPolicies())->toHaveCount(1);
            });

            test('creates inline policy on Binding', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $binding = $wsdl->binding('TestBinding', 'TestPortType');

                // Act
                $policy = $binding->policy('TestPolicy');

                // Assert
                expect($policy)->toBeInstanceOf(Policy::class)
                    ->and($binding->getPolicies())->toHaveCount(1);
            });

            test('creates inline policy on Service', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $service = $wsdl->service('TestService');

                // Act
                $policy = $service->policy('TestPolicy');

                // Assert
                expect($policy)->toBeInstanceOf(Policy::class)
                    ->and($service->getPolicies())->toHaveCount(1);
            });

            test('adds policy reference to Wsdl', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->policyReference('#ExternalPolicy');

                // Assert
                expect($result)->toBe($wsdl)
                    ->and($wsdl->getPolicyReferences())->toHaveCount(1)
                    ->and($wsdl->getPolicyReferences()[0]->uri)->toBe('#ExternalPolicy');
            });

            test('adds policy reference with digest to Binding', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $binding = $wsdl->binding('TestBinding', 'TestPortType');

                // Act
                $result = $binding->policyReference('#ExternalPolicy', 'digest123', 'sha256');

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->getPolicyReferences())->toHaveCount(1)
                    ->and($binding->getPolicyReferences()[0]->digest)->toBe('digest123');
            });

            test('policy end returns parent element', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $policy = $wsdl->policy('TestPolicy');

                // Act
                $result = $policy->end();

                // Assert
                expect($result)->toBe($wsdl);
            });
        });
    });
});

describe('WS-Addressing', function (): void {
    describe('Action', function (): void {
        describe('Happy Paths', function (): void {
            test('creates action with input action only', function (): void {
                // Arrange & Act
                $action = new Action('http://example.com/GetUser');

                // Assert
                expect($action->inputAction)->toBe('http://example.com/GetUser')
                    ->and($action->outputAction)->toBeNull()
                    ->and($action->faultActions)->toBeNull();
            });

            test('creates action with input and output actions', function (): void {
                // Arrange & Act
                $action = new Action(
                    'http://example.com/GetUser',
                    'http://example.com/GetUserResponse',
                );

                // Assert
                expect($action->inputAction)->toBe('http://example.com/GetUser')
                    ->and($action->outputAction)->toBe('http://example.com/GetUserResponse');
            });

            test('creates action with fault actions', function (): void {
                // Arrange
                $faultActions = [
                    'InvalidUserFault' => 'http://example.com/InvalidUserFault',
                    'NotFoundFault' => 'http://example.com/NotFoundFault',
                ];

                // Act
                $action = new Action(
                    'http://example.com/GetUser',
                    'http://example.com/GetUserResponse',
                    $faultActions,
                );

                // Assert
                expect($action->faultActions)->toBe($faultActions)
                    ->and($action->faultActions)->toHaveCount(2);
            });
        });
    });

    describe('EndpointReference', function (): void {
        describe('Happy Paths', function (): void {
            test('creates endpoint reference with address', function (): void {
                // Arrange & Act
                $epr = new EndpointReference('http://example.com/service');

                // Assert
                expect($epr->getAddress())->toBe('http://example.com/service')
                    ->and($epr->getReferenceParameters())->toBeNull()
                    ->and($epr->getMetadata())->toBeNull();
            });

            test('creates reference parameters for endpoint', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');

                // Act
                $params = $epr->referenceParameters();

                // Assert
                expect($params)->toBeInstanceOf(ReferenceParameters::class)
                    ->and($epr->getReferenceParameters())->toBe($params);
            });

            test('returns same reference parameters instance on subsequent calls', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');

                // Act
                $params1 = $epr->referenceParameters();
                $params2 = $epr->referenceParameters();

                // Assert
                expect($params1)->toBe($params2);
            });

            test('creates metadata for endpoint', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');

                // Act
                $metadata = $epr->metadata();

                // Assert
                expect($metadata)->toBeInstanceOf(Metadata::class)
                    ->and($epr->getMetadata())->toBe($metadata);
            });

            test('returns same metadata instance on subsequent calls', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');

                // Act
                $metadata1 = $epr->metadata();
                $metadata2 = $epr->metadata();

                // Assert
                expect($metadata1)->toBe($metadata2);
            });
        });
    });

    describe('ReferenceParameters', function (): void {
        describe('Happy Paths', function (): void {
            test('adds parameter to reference parameters', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');
                $params = $epr->referenceParameters();

                // Act
                $result = $params->parameter('http://example.com', 'SessionId', 'abc123');

                // Assert
                expect($result)->toBe($params)
                    ->and($params->getParameters())->toHaveCount(1)
                    ->and($params->getParameters()[0])->toBe([
                        'namespace' => 'http://example.com',
                        'localName' => 'SessionId',
                        'value' => 'abc123',
                    ]);
            });

            test('adds multiple parameters to reference parameters', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');
                $params = $epr->referenceParameters();

                // Act
                $params->parameter('http://example.com', 'SessionId', 'abc123')
                    ->parameter('http://example.com', 'UserId', 'user456');

                // Assert
                expect($params->getParameters())->toHaveCount(2)
                    ->and($params->getParameters()[1]['localName'])->toBe('UserId');
            });

            test('end returns parent endpoint reference', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');
                $params = $epr->referenceParameters();

                // Act
                $result = $params->end();

                // Assert
                expect($result)->toBe($epr);
            });
        });
    });

    describe('Metadata', function (): void {
        describe('Happy Paths', function (): void {
            test('adds metadata item', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');
                $metadata = $epr->metadata();

                // Act
                $result = $metadata->add('http://example.com', 'ServiceVersion', '1.0');

                // Assert
                expect($result)->toBe($metadata)
                    ->and($metadata->getItems())->toHaveCount(1)
                    ->and($metadata->getItems()[0])->toBe([
                        'namespace' => 'http://example.com',
                        'localName' => 'ServiceVersion',
                        'content' => '1.0',
                    ]);
            });

            test('adds multiple metadata items', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');
                $metadata = $epr->metadata();

                // Act
                $metadata->add('http://example.com', 'ServiceVersion', '1.0')
                    ->add('http://example.com', 'Environment', 'production');

                // Assert
                expect($metadata->getItems())->toHaveCount(2)
                    ->and($metadata->getItems()[1]['localName'])->toBe('Environment');
            });

            test('adds metadata item with complex content', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');
                $metadata = $epr->metadata();
                $complexContent = ['key1' => 'value1', 'key2' => 'value2'];

                // Act
                $metadata->add('http://example.com', 'Config', $complexContent);

                // Assert
                expect($metadata->getItems()[0]['content'])->toBe($complexContent);
            });

            test('end returns parent endpoint reference', function (): void {
                // Arrange
                $epr = new EndpointReference('http://example.com/service');
                $metadata = $epr->metadata();

                // Act
                $result = $metadata->end();

                // Assert
                expect($result)->toBe($epr);
            });
        });
    });

    describe('AddressingVersion', function (): void {
        describe('Happy Paths', function (): void {
            test('provides Addressing2004 version URI', function (): void {
                // Arrange & Act
                $version = AddressingVersion::Addressing2004;

                // Assert
                expect($version->value)->toBe('http://schemas.xmlsoap.org/ws/2004/08/addressing');
            });

            test('provides Addressing2005 version URI', function (): void {
                // Arrange & Act
                $version = AddressingVersion::Addressing2005;

                // Assert
                expect($version->value)->toBe('http://www.w3.org/2005/08/addressing');
            });

            test('provides AddressingWsdl version URI', function (): void {
                // Arrange & Act
                $version = AddressingVersion::AddressingWsdl;

                // Assert
                expect($version->value)->toBe('http://www.w3.org/2006/05/addressing/wsdl');
            });

            test('enum contains all three versions', function (): void {
                // Arrange & Act
                $versions = AddressingVersion::cases();

                // Assert
                expect($versions)->toHaveCount(3)
                    ->and($versions)->toContain(AddressingVersion::Addressing2004)
                    ->and($versions)->toContain(AddressingVersion::Addressing2005)
                    ->and($versions)->toContain(AddressingVersion::AddressingWsdl);
            });
        });
    });

    describe('AddressingSupport', function (): void {
        describe('Happy Paths', function (): void {
            test('enables WS-Addressing on Binding', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $binding = $wsdl->binding('TestBinding', 'TestPortType');

                // Act
                $result = $binding->usingAddressing();

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->isUsingAddressing())->toBeTrue();
            });

            test('disables WS-Addressing on Binding', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $binding = $wsdl->binding('TestBinding', 'TestPortType')
                    ->usingAddressing();

                // Act
                $result = $binding->usingAddressing(false);

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->isUsingAddressing())->toBeFalse();
            });

            test('sets action on Binding operation', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $binding = $wsdl->binding('TestBinding', 'TestPortType')
                    ->operation('GetUser', 'urn:GetUser');

                // Act
                $result = $binding->action(
                    'GetUser',
                    'http://example.com/GetUser',
                    'http://example.com/GetUserResponse',
                );

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->getActions())->toHaveCount(1)
                    ->and($binding->getActions()['GetUser']->inputAction)->toBe('http://example.com/GetUser')
                    ->and($binding->getActions()['GetUser']->outputAction)->toBe('http://example.com/GetUserResponse');
            });

            test('sets fault action on Binding operation', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $binding = $wsdl->binding('TestBinding', 'TestPortType')
                    ->operation('GetUser', 'urn:GetUser')
                    ->action('GetUser', 'http://example.com/GetUser');

                // Act
                $result = $binding->faultAction('GetUser', 'InvalidUserFault', 'http://example.com/InvalidUserFault');

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->getActions()['GetUser']->faultActions)->toHaveCount(1)
                    ->and($binding->getActions()['GetUser']->faultActions['InvalidUserFault'])->toBe('http://example.com/InvalidUserFault');
            });
        });

        describe('Sad Paths', function (): void {
            test('throws exception when setting fault action without action defined', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $binding = $wsdl->binding('TestBinding', 'TestPortType')
                    ->operation('GetUser', 'urn:GetUser');

                // Act & Assert
                expect(fn () => $binding->faultAction('GetUser', 'InvalidUserFault', 'http://example.com/InvalidUserFault'))
                    ->toThrow(RuntimeException::class, "No action defined for operation 'GetUser'. Call action() first.");
            });
        });
    });
});

describe('WS-Security', function (): void {
    describe('SecurityTokenInclusion', function (): void {
        describe('Happy Paths', function (): void {
            test('provides Never inclusion value', function (): void {
                // Arrange & Act
                $inclusion = SecurityTokenInclusion::Never;

                // Assert
                expect($inclusion->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/Never');
            });

            test('provides Once inclusion value', function (): void {
                // Arrange & Act
                $inclusion = SecurityTokenInclusion::Once;

                // Assert
                expect($inclusion->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/Once');
            });

            test('provides AlwaysToRecipient inclusion value', function (): void {
                // Arrange & Act
                $inclusion = SecurityTokenInclusion::AlwaysToRecipient;

                // Assert
                expect($inclusion->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/AlwaysToRecipient');
            });

            test('provides AlwaysToInitiator inclusion value', function (): void {
                // Arrange & Act
                $inclusion = SecurityTokenInclusion::AlwaysToInitiator;

                // Assert
                expect($inclusion->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/AlwaysToInitiator');
            });

            test('provides Always inclusion value', function (): void {
                // Arrange & Act
                $inclusion = SecurityTokenInclusion::Always;

                // Assert
                expect($inclusion->value)->toBe('http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/Always');
            });

            test('enum contains all five inclusion values', function (): void {
                // Arrange & Act
                $inclusions = SecurityTokenInclusion::cases();

                // Assert
                expect($inclusions)->toHaveCount(5)
                    ->and($inclusions)->toContain(SecurityTokenInclusion::Never)
                    ->and($inclusions)->toContain(SecurityTokenInclusion::Once)
                    ->and($inclusions)->toContain(SecurityTokenInclusion::AlwaysToRecipient)
                    ->and($inclusions)->toContain(SecurityTokenInclusion::AlwaysToInitiator)
                    ->and($inclusions)->toContain(SecurityTokenInclusion::Always);
            });
        });
    });

    describe('AlgorithmSuite', function (): void {
        describe('Happy Paths', function (): void {
            test('provides Basic256 algorithm suite', function (): void {
                // Arrange & Act
                $suite = AlgorithmSuite::Basic256;

                // Assert
                expect($suite->value)->toBe('Basic256');
            });

            test('provides Basic192 algorithm suite', function (): void {
                // Arrange & Act
                $suite = AlgorithmSuite::Basic192;

                // Assert
                expect($suite->value)->toBe('Basic192');
            });

            test('provides Basic128 algorithm suite', function (): void {
                // Arrange & Act
                $suite = AlgorithmSuite::Basic128;

                // Assert
                expect($suite->value)->toBe('Basic128');
            });

            test('provides TripleDes algorithm suite', function (): void {
                // Arrange & Act
                $suite = AlgorithmSuite::TripleDes;

                // Assert
                expect($suite->value)->toBe('TripleDes');
            });

            test('provides Basic256Sha256 algorithm suite', function (): void {
                // Arrange & Act
                $suite = AlgorithmSuite::Basic256Sha256;

                // Assert
                expect($suite->value)->toBe('Basic256Sha256');
            });

            test('provides all SHA256 algorithm suites', function (): void {
                // Arrange & Act & Assert
                expect(AlgorithmSuite::Basic192Sha256->value)->toBe('Basic192Sha256')
                    ->and(AlgorithmSuite::Basic128Sha256->value)->toBe('Basic128Sha256')
                    ->and(AlgorithmSuite::TripleDesSha256->value)->toBe('TripleDesSha256');
            });

            test('provides all RSA15 algorithm suites', function (): void {
                // Arrange & Act & Assert
                expect(AlgorithmSuite::Basic256Rsa15->value)->toBe('Basic256Rsa15')
                    ->and(AlgorithmSuite::Basic192Rsa15->value)->toBe('Basic192Rsa15')
                    ->and(AlgorithmSuite::Basic128Rsa15->value)->toBe('Basic128Rsa15')
                    ->and(AlgorithmSuite::TripleDesRsa15->value)->toBe('TripleDesRsa15');
            });

            test('provides all SHA256 RSA15 algorithm suites', function (): void {
                // Arrange & Act & Assert
                expect(AlgorithmSuite::Basic256Sha256Rsa15->value)->toBe('Basic256Sha256Rsa15')
                    ->and(AlgorithmSuite::Basic192Sha256Rsa15->value)->toBe('Basic192Sha256Rsa15')
                    ->and(AlgorithmSuite::Basic128Sha256Rsa15->value)->toBe('Basic128Sha256Rsa15')
                    ->and(AlgorithmSuite::TripleDesSha256Rsa15->value)->toBe('TripleDesSha256Rsa15');
            });

            test('enum contains all sixteen algorithm suites', function (): void {
                // Arrange & Act
                $suites = AlgorithmSuite::cases();

                // Assert
                expect($suites)->toHaveCount(16);
            });
        });
    });

    describe('TokenAssertion', function (): void {
        describe('Happy Paths', function (): void {
            test('creates token assertion with token type', function (): void {
                // Arrange & Act
                $token = new TokenAssertion('HttpsToken');

                // Assert
                expect($token->getTokenType())->toBe('HttpsToken')
                    ->and($token->getIncludeToken())->toBeNull();
            });

            test('sets include token policy', function (): void {
                // Arrange
                $token = new TokenAssertion('HttpsToken');

                // Act
                $result = $token->includeToken(SecurityTokenInclusion::Always);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->getIncludeToken())->toBe(SecurityTokenInclusion::Always);
            });

            test('converts to array without include token', function (): void {
                // Arrange
                $token = new TokenAssertion('HttpsToken');

                // Act
                $array = $token->toArray();

                // Assert
                expect($array)->toBe(['tokenType' => 'HttpsToken']);
            });

            test('converts to array with include token', function (): void {
                // Arrange
                $token = new TokenAssertion('HttpsToken');
                $token->includeToken(SecurityTokenInclusion::Always);

                // Act
                $array = $token->toArray();

                // Assert
                expect($array)->toBe([
                    'tokenType' => 'HttpsToken',
                    'includeToken' => 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702/IncludeToken/Always',
                ]);
            });
        });
    });

    describe('TransportToken', function (): void {
        describe('Happy Paths', function (): void {
            test('creates transport token with parent binding', function (): void {
                // Arrange
                $binding = new TransportBinding();

                // Act
                $token = $binding->transportToken();

                // Assert
                expect($token)->toBeInstanceOf(TransportToken::class)
                    ->and($token->isHttpsToken())->toBeFalse()
                    ->and($token->isClientCertificateRequired())->toBeFalse();
            });

            test('sets https token', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $token = $binding->transportToken();

                // Act
                $result = $token->httpsToken();

                // Assert
                expect($result)->toBe($token)
                    ->and($token->isHttpsToken())->toBeTrue();
            });

            test('requires client certificate', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $token = $binding->transportToken();

                // Act
                $result = $token->requireClientCertificate();

                // Assert
                expect($result)->toBe($token)
                    ->and($token->isClientCertificateRequired())->toBeTrue();
            });

            test('optionally requires client certificate with false', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $token = $binding->transportToken()
                    ->requireClientCertificate();

                // Act
                $result = $token->requireClientCertificate(false);

                // Assert
                expect($result)->toBe($token)
                    ->and($token->isClientCertificateRequired())->toBeFalse();
            });

            test('end returns parent transport binding', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $token = $binding->transportToken();

                // Act
                $result = $token->end();

                // Assert
                expect($result)->toBe($binding);
            });

            test('gets configuration as array', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $token = $binding->transportToken()
                    ->httpsToken()
                    ->requireClientCertificate();

                // Act
                $config = $token->getConfig();

                // Assert
                expect($config)->toBe([
                    'httpsToken' => true,
                    'requireClientCertificate' => true,
                ]);
            });
        });
    });

    describe('TransportBinding', function (): void {
        describe('Happy Paths', function (): void {
            test('creates transport binding without parent', function (): void {
                // Arrange & Act
                $binding = new TransportBinding();

                // Assert
                expect($binding->getTransportToken())->toBeNull()
                    ->and($binding->getAlgorithmSuite())->toBeNull()
                    ->and($binding->isTimestampIncluded())->toBeFalse()
                    ->and($binding->getLayout())->toBeNull();
            });

            test('creates transport binding with parent', function (): void {
                // Arrange
                $parent = new stdClass();

                // Act
                $binding = new TransportBinding($parent);

                // Assert
                expect($binding)->toBeInstanceOf(TransportBinding::class);
            });

            test('creates transport token', function (): void {
                // Arrange
                $binding = new TransportBinding();

                // Act
                $token = $binding->transportToken();

                // Assert
                expect($token)->toBeInstanceOf(TransportToken::class)
                    ->and($binding->getTransportToken())->toBe($token);
            });

            test('sets algorithm suite from enum', function (): void {
                // Arrange
                $binding = new TransportBinding();

                // Act
                $result = $binding->algorithmSuite(AlgorithmSuite::Basic256);

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->getAlgorithmSuite())->toBe(AlgorithmSuite::Basic256);
            });

            test('sets algorithm suite from string', function (): void {
                // Arrange
                $binding = new TransportBinding();

                // Act
                $result = $binding->algorithmSuite('Basic256');

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->getAlgorithmSuite())->toBe(AlgorithmSuite::Basic256);
            });

            test('includes timestamp', function (): void {
                // Arrange
                $binding = new TransportBinding();

                // Act
                $result = $binding->includeTimestamp();

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->isTimestampIncluded())->toBeTrue();
            });

            test('excludes timestamp with false', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $binding->includeTimestamp();

                // Act
                $result = $binding->includeTimestamp(false);

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->isTimestampIncluded())->toBeFalse();
            });

            test('sets layout policy', function (): void {
                // Arrange
                $binding = new TransportBinding();

                // Act
                $result = $binding->layout('Strict');

                // Assert
                expect($result)->toBe($binding)
                    ->and($binding->getLayout())->toBe('Strict');
            });

            test('gets configuration as array without optional values', function (): void {
                // Arrange
                $binding = new TransportBinding();

                // Act
                $config = $binding->getConfig();

                // Assert
                expect($config)->toBe(['includeTimestamp' => false]);
            });

            test('gets configuration as array with all values', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $binding->transportToken()->httpsToken()->requireClientCertificate()->end()
                    ->algorithmSuite(AlgorithmSuite::Basic256)
                    ->includeTimestamp()
                    ->layout('Strict');

                // Act
                $config = $binding->getConfig();

                // Assert
                expect($config)->toHaveKey('includeTimestamp', true)
                    ->and($config)->toHaveKey('transportToken')
                    ->and($config)->toHaveKey('algorithmSuite', 'Basic256')
                    ->and($config)->toHaveKey('layout', 'Strict');
            });

            test('end returns parent when parent exists', function (): void {
                // Arrange
                $parent = new stdClass();
                $binding = new TransportBinding($parent);

                // Act
                $result = $binding->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('end returns config array when no parent exists', function (): void {
                // Arrange
                $binding = new TransportBinding();
                $binding->includeTimestamp();

                // Act
                $result = $binding->end();

                // Assert
                expect($result)->toBeArray()
                    ->and($result)->toHaveKey('includeTimestamp', true);
            });
        });
    });

    describe('SecurityPolicy', function (): void {
        describe('Happy Paths', function (): void {
            test('creates transport binding', function (): void {
                // Arrange & Act
                $binding = SecurityPolicy::transportBinding();

                // Assert
                expect($binding)->toBeInstanceOf(TransportBinding::class);
            });

            test('creates transport binding with parent', function (): void {
                // Arrange
                $parent = new stdClass();

                // Act
                $binding = SecurityPolicy::transportBinding($parent);
                $result = $binding->end();

                // Assert
                expect($result)->toBe($parent);
            });

            test('creates symmetric binding assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::symmetricBinding();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:SymmetricBinding',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates asymmetric binding assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::asymmetricBinding();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:AsymmetricBinding',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates username token assertion without password type', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::usernameToken();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:UsernameToken',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates username token assertion with password type', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::usernameToken('PasswordText');

                // Assert
                expect($assertion)->toHaveKey('passwordType', 'PasswordText');
            });

            test('creates X509 token assertion without token type', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::x509Token();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:X509Token',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates X509 token assertion with token type', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::x509Token('WssX509V3Token10');

                // Assert
                expect($assertion)->toHaveKey('tokenType', 'WssX509V3Token10');
            });

            test('creates SAML token assertion without token type', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::samlToken();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:SamlToken',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates SAML token assertion with token type', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::samlToken('WssSamlV11Token10');

                // Assert
                expect($assertion)->toHaveKey('tokenType', 'WssSamlV11Token10');
            });

            test('creates signed parts assertion without parts', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::signedParts();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:SignedParts',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates signed parts assertion with parts', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::signedParts(['Body', 'Header']);

                // Assert
                expect($assertion)->toHaveKey('parts', ['Body', 'Header']);
            });

            test('creates encrypted parts assertion without parts', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::encryptedParts();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:EncryptedParts',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates encrypted parts assertion with parts', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::encryptedParts(['Body']);

                // Assert
                expect($assertion)->toHaveKey('parts', ['Body']);
            });

            test('creates signed elements assertion without xpaths', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::signedElements();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:SignedElements',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates signed elements assertion with xpaths', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::signedElements(['//*[@Id="myId"]']);

                // Assert
                expect($assertion)->toHaveKey('xpaths', ['//*[@Id="myId"]']);
            });

            test('creates encrypted elements assertion without xpaths', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::encryptedElements();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:EncryptedElements',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates encrypted elements assertion with xpaths', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::encryptedElements(['//*[@sensitive="true"]']);

                // Assert
                expect($assertion)->toHaveKey('xpaths', ['//*[@sensitive="true"]']);
            });

            test('creates issued token assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::issuedToken();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:IssuedToken',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates secure conversation token assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::secureConversationToken();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:SecureConversationToken',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates Kerberos token assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::kerberosToken();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:KerberosToken',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates SPNEGO context token assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::spnegoContextToken();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:SpnegoContextToken',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates WSS10 assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::wss10();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:Wss10',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates WSS11 assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::wss11();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:Wss11',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates Trust10 assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::trust10();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:Trust10',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });

            test('creates Trust13 assertion', function (): void {
                // Arrange & Act
                $assertion = SecurityPolicy::trust13();

                // Assert
                expect($assertion)->toBe([
                    'type' => 'sp:Trust13',
                    'namespace' => SecurityPolicy::NAMESPACE_URI,
                ]);
            });
        });
    });
});
