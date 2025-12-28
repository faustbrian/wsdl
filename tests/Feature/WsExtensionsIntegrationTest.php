<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\BindingStyle;
use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Enums\SoapVersion;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Policy\Policy;
use Cline\WsdlBuilder\WsExtensions\Security\Enums\AlgorithmSuite;
use Cline\WsdlBuilder\WsExtensions\Security\SecurityPolicy;

describe('WS-Extensions Integration', function (): void {
    describe('WS-Policy Integration', function (): void {
        describe('Happy Paths', function (): void {
            test('builds WSDL with inline policy on binding', function (): void {
                // Arrange
                $wsdl = Wsdl::create('SecureService', 'http://example.com/secure')
                    ->soapVersion(SoapVersion::Soap11)
                    ->defaultStyle(BindingStyle::Document)
                    ->defaultUse(BindingUse::Literal)

                    // Define message types
                    ->complexType('GetUserRequest')
                    ->element('userId', XsdType::Int)
                    ->end()

                    ->complexType('GetUserResponse')
                    ->element('userName', XsdType::String)
                    ->end()

                    // Define messages
                    ->message('GetUserInput')
                    ->part('parameters', 'tns:GetUserRequest')
                    ->end()

                    ->message('GetUserOutput')
                    ->part('parameters', 'tns:GetUserResponse')
                    ->end()

                    // Define port type
                    ->portType('SecureServicePortType')
                    ->operation('GetUser', 'GetUserInput', 'GetUserOutput')
                    ->end()

                    // Define binding with inline policy
                    ->binding('SecureServiceBinding', 'SecureServicePortType')
                    ->policy('SecureBindingPolicy')
                    ->all()
                    ->assertion('http://example.com/policies', 'RequireAuthentication', ['level' => 'strong'])
                    ->end()
                    ->end()
                    ->operation('GetUser', 'http://example.com/secure/GetUser')
                    ->end();

                // Define service
                $wsdl->service('SecureService')
                    ->port('SecureServicePort', 'SecureServiceBinding', 'http://example.com/soap/secure')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('<?xml version="1.0" encoding="UTF-8"?>')
                    ->toContain('xmlns:wsp="http://www.w3.org/ns/ws-policy"')
                    ->toContain('wsp:Policy')
                    ->toContain('xml:id="SecureBindingPolicy"')
                    ->toContain('wsp:All')
                    ->toContain('level="strong"');
            });

            test('builds WSDL with wsp:All and wsp:ExactlyOne operators', function (): void {
                // Arrange
                $wsdl = Wsdl::create('PolicyService', 'http://example.com/policy')
                    ->binding('PolicyServiceBinding', 'PolicyServicePortType')
                    ->policy('ComplexPolicy')
                    ->exactlyOne()
                    ->all()
                    ->assertion('http://example.com/policies', 'Option1')
                    ->assertion('http://example.com/policies', 'Option2')
                    ->end()
                    ->all()
                    ->assertion('http://example.com/policies', 'Option3')
                    ->end()
                    ->end()
                    ->end()
                    ->operation('DoSomething', 'urn:DoSomething')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('wsp:ExactlyOne')
                    ->toContain('wsp:All');

                // Verify XML is well-formed
                $dom = new DOMDocument();
                expect($dom->loadXML($xml))->toBeTrue();
            });

            test('builds WSDL with policy assertions that render with namespace', function (): void {
                // Arrange
                $wsdl = Wsdl::create('NamespaceService', 'http://example.com/ns')
                    ->binding('NamespaceServiceBinding', 'NamespaceServicePortType')
                    ->policy('NamespacePolicy')
                    ->all()
                    ->assertion('http://schemas.xmlsoap.org/ws/2005/07/securitypolicy', 'TransportBinding')
                    ->assertion('http://example.com/custom', 'CustomAssertion', ['foo' => 'bar'])
                    ->end()
                    ->end()
                    ->operation('Execute', 'urn:Execute')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert - Check that the policy assertions are rendered
                expect($xml)
                    ->toContain('TransportBinding')
                    ->toContain('CustomAssertion')
                    ->toContain('foo="bar"');
            });

            test('builds WSDL with policy references', function (): void {
                // Arrange
                $wsdl = Wsdl::create('RefService', 'http://example.com/ref')
                    // Define WSDL-level policy
                    ->policy('GlobalSecurityPolicy', 'GlobalSecurity')
                    ->all()
                    ->assertion('http://example.com/security', 'RequireEncryption')
                    ->end()
                    ->end()

                    // Define binding with policy reference
                    ->binding('RefServiceBinding', 'RefServicePortType')
                    ->policyReference('#GlobalSecurityPolicy')
                    ->operation('Process', 'urn:Process')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('wsp:PolicyReference')
                    ->toContain('URI="#GlobalSecurityPolicy"')
                    ->toContain('xml:id="GlobalSecurityPolicy"');
            });

            test('builds WSDL with WSDL-level policies', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TopLevelPolicyService', 'http://example.com/toplevel')
                    ->policy('ServiceLevelPolicy', 'ServicePolicy')
                    ->all()
                    ->assertion('http://example.com/policies', 'GlobalRequirement')
                    ->end()
                    ->end()

                    ->binding('TopLevelPolicyServiceBinding', 'TopLevelPolicyServicePortType')
                    ->operation('Execute', 'urn:Execute')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('wsp:Policy')
                    ->toContain('xml:id="ServiceLevelPolicy"')
                    ->toContain('GlobalRequirement');

                // Verify policy appears at WSDL level (before bindings)
                $dom = new DOMDocument();
                $dom->loadXML($xml);
                $xpath = new DOMXPath($dom);
                $xpath->registerNamespace('wsdl', 'http://schemas.xmlsoap.org/wsdl/');
                $xpath->registerNamespace('wsp', 'http://www.w3.org/ns/ws-policy');

                $policies = $xpath->query('/wsdl:definitions/wsp:Policy');
                expect($policies->length)->toBeGreaterThan(0);
            });
        });
    });

    describe('WS-Addressing Integration', function (): void {
        describe('Happy Paths', function (): void {
            test('builds WSDL with addressing enabled on portType', function (): void {
                // Arrange
                $wsdl = Wsdl::create('AddressingService', 'http://example.com/addressing')
                    ->portType('AddressingServicePortType')
                    ->usingAddressing()
                    ->operation('SendMessage', 'SendMessageInput', 'SendMessageOutput')
                    ->action('SendMessage', 'http://example.com/SendMessage', 'http://example.com/SendMessageResponse')
                    ->end()

                    ->binding('AddressingServiceBinding', 'AddressingServicePortType')
                    ->operation('SendMessage', 'urn:SendMessage')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('xmlns:wsaw="http://www.w3.org/2006/05/addressing/wsdl"')
                    ->toContain('wsaw:UsingAddressing');
            });

            test('builds WSDL with wsaw:Action elements on operations', function (): void {
                // Arrange
                $wsdl = Wsdl::create('ActionService', 'http://example.com/action')
                    ->portType('ActionServicePortType')
                    ->usingAddressing()
                    ->operation('GetData', 'GetDataInput', 'GetDataOutput')
                    ->action('GetData', 'http://example.com/GetData', 'http://example.com/GetDataResponse')
                    ->operation('SetData', 'SetDataInput', 'SetDataOutput')
                    ->action('SetData', 'http://example.com/SetData', 'http://example.com/SetDataResponse')
                    ->end()

                    ->binding('ActionServiceBinding', 'ActionServicePortType')
                    ->operation('GetData', 'urn:GetData')
                    ->operation('SetData', 'urn:SetData')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert - Verify addressing is enabled and portType has actions defined
                expect($xml)
                    ->toContain('wsaw:UsingAddressing')
                    ->toContain('name="GetData"')
                    ->toContain('name="SetData"');

                // Verify actions are stored in the model
                $portTypes = $wsdl->getPortTypes();
                expect($portTypes)->toHaveKey('ActionServicePortType');
                $actions = $portTypes['ActionServicePortType']->getActions();
                expect($actions)->toHaveKey('GetData')
                    ->and($actions)->toHaveKey('SetData')
                    ->and($actions['GetData']->inputAction)->toBe('http://example.com/GetData')
                    ->and($actions['GetData']->outputAction)->toBe('http://example.com/GetDataResponse');
            });

            test('builds WSDL with addressing fault actions', function (): void {
                // Arrange
                $wsdl = Wsdl::create('FaultActionService', 'http://example.com/faultaction')
                    ->complexType('ValidationFault')
                    ->element('errorCode', XsdType::Int)
                    ->element('errorMessage', XsdType::String)
                    ->end()

                    ->message('ValidationFaultMessage')
                    ->part('fault', 'tns:ValidationFault')
                    ->end()

                    ->portType('FaultActionServicePortType')
                    ->usingAddressing()
                    ->operation('ValidateData', 'ValidateDataInput', 'ValidateDataOutput', 'ValidationFaultMessage')
                    ->action('ValidateData', 'http://example.com/ValidateData', 'http://example.com/ValidateDataResponse')
                    ->faultAction('ValidateData', 'ValidationFault', 'http://example.com/ValidationFault')
                    ->end()

                    ->binding('FaultActionServiceBinding', 'FaultActionServicePortType')
                    ->operation('ValidateData', 'urn:ValidateData')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert - Verify addressing is enabled and fault actions are stored
                expect($xml)
                    ->toContain('wsaw:UsingAddressing')
                    ->toContain('name="ValidateData"')
                    ->toContain('ValidationFaultMessage');

                // Verify fault actions are stored in the model
                $portTypes = $wsdl->getPortTypes();
                $actions = $portTypes['FaultActionServicePortType']->getActions();
                expect($actions['ValidateData']->faultActions)->toHaveKey('ValidationFault')
                    ->and($actions['ValidateData']->faultActions['ValidationFault'])->toBe('http://example.com/ValidationFault');
            });
        });
    });

    describe('WS-Security Integration', function (): void {
        describe('Happy Paths', function (): void {
            test('builds WSDL with security policy assertions via TransportBinding', function (): void {
                // Arrange
                $wsdl = Wsdl::create('SecurityService', 'http://example.com/security')
                    ->binding('SecurityServiceBinding', 'SecurityServicePortType')
                    ->policy('SecurityPolicy')
                    ->all()
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'TransportBinding',
                    )
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'HttpsToken',
                    )
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'AlgorithmSuite',
                        ['algorithm' => 'Basic256'],
                    )
                    ->end()
                    ->end()
                    ->operation('SecureOperation', 'urn:SecureOperation')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('xmlns:sp="http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702"')
                    ->toContain('wsp:Policy')
                    ->toContain('sp:TransportBinding');
            });

            test('builds WSDL with username token assertion', function (): void {
                // Arrange
                $wsdl = Wsdl::create('UsernameTokenService', 'http://example.com/username')
                    ->binding('UsernameTokenServiceBinding', 'UsernameTokenServicePortType')
                    ->policy('UsernameTokenPolicy')
                    ->all()
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'UsernameToken',
                        ['passwordType' => 'PasswordText'],
                    )
                    ->end()
                    ->end()
                    ->operation('Authenticate', 'urn:Authenticate')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('sp:UsernameToken')
                    ->toContain('passwordType="PasswordText"');
            });

            test('builds WSDL with signed and encrypted parts assertions', function (): void {
                // Arrange
                $wsdl = Wsdl::create('SignEncryptService', 'http://example.com/signencrypt')
                    ->binding('SignEncryptServiceBinding', 'SignEncryptServicePortType')
                    ->policy('SignEncryptPolicy')
                    ->all()
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'SignedParts',
                    )
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'EncryptedParts',
                    )
                    ->end()
                    ->end()
                    ->operation('Process', 'urn:Process')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)
                    ->toContain('sp:SignedParts')
                    ->toContain('sp:EncryptedParts');
            });
        });
    });

    describe('Combined Integration', function (): void {
        describe('Happy Paths', function (): void {
            test('builds complete WSDL with policy, addressing, and security', function (): void {
                // Arrange
                $wsdl = Wsdl::create('CompleteSecureService', 'http://example.com/complete')
                    ->soapVersion(SoapVersion::Soap11)
                    ->defaultStyle(BindingStyle::Document)
                    ->defaultUse(BindingUse::Literal)

                    // Define types
                    ->complexType('SecureRequest')
                    ->element('requestId', XsdType::String)
                    ->element('data', XsdType::String)
                    ->end()

                    ->complexType('SecureResponse')
                    ->element('responseId', XsdType::String)
                    ->element('result', XsdType::String)
                    ->end()

                    ->complexType('SecurityFault')
                    ->element('faultCode', XsdType::Int)
                    ->element('faultString', XsdType::String)
                    ->end()

                    // Define messages
                    ->message('SecureRequestMessage')
                    ->part('parameters', 'tns:SecureRequest')
                    ->end()

                    ->message('SecureResponseMessage')
                    ->part('parameters', 'tns:SecureResponse')
                    ->end()

                    ->message('SecurityFaultMessage')
                    ->part('fault', 'tns:SecurityFault')
                    ->end()

                    // Define port type with addressing
                    ->portType('CompleteSecureServicePortType')
                    ->usingAddressing()
                    ->operation('ProcessSecure', 'SecureRequestMessage', 'SecureResponseMessage', 'SecurityFaultMessage')
                    ->action('ProcessSecure', 'http://example.com/ProcessSecure', 'http://example.com/ProcessSecureResponse')
                    ->faultAction('ProcessSecure', 'SecurityFault', 'http://example.com/SecurityFault')
                    ->end();

                // Define WSDL-level security policy
                $wsdl->policy('GlobalSecurityPolicy', 'GlobalSecurity')
                    ->all()
                    ->assertion('http://example.com/security', 'RequireSecureTransport')
                    ->end()
                    ->end()

                    // Define binding with security policy
                    ->binding('CompleteSecureServiceBinding', 'CompleteSecureServicePortType')
                    ->policy('BindingSecurityPolicy')
                    ->exactlyOne()
                    ->all()
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'TransportBinding',
                    )
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'HttpsToken',
                    )
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'SignedParts',
                    )
                    ->assertion(
                        'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702',
                        'AlgorithmSuite',
                        ['algorithm' => 'Basic256Sha256'],
                    )
                    ->end()
                    ->end()
                    ->end()
                    ->policyReference('#GlobalSecurityPolicy')
                    ->operation('ProcessSecure', 'http://example.com/complete/ProcessSecure')
                    ->end();

                // Define service
                $wsdl->service('CompleteSecureService')
                    ->port('CompleteSecureServicePort', 'CompleteSecureServiceBinding', 'https://example.com/soap/complete')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert - Verify main namespaces are declared
                expect($xml)
                    ->toContain('xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"')
                    ->toContain('xmlns:xsd="http://www.w3.org/2001/XMLSchema"')
                    ->toContain('xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"')
                    ->toContain('xmlns:tns="http://example.com/complete"')
                    ->toContain('xmlns:wsp="http://www.w3.org/ns/ws-policy"')
                    ->toContain('xmlns:wsaw="http://www.w3.org/2006/05/addressing/wsdl"');

                // Verify policy elements
                expect($xml)
                    ->toContain('wsp:Policy')
                    ->toContain('wsp:All')
                    ->toContain('wsp:ExactlyOne')
                    ->toContain('wsp:PolicyReference')
                    ->toContain('xml:id="GlobalSecurityPolicy"')
                    ->toContain('xml:id="BindingSecurityPolicy"')
                    ->toContain('URI="#GlobalSecurityPolicy"');

                // Verify addressing elements
                expect($xml)
                    ->toContain('wsaw:UsingAddressing');

                // Verify security assertions are present
                expect($xml)
                    ->toContain('TransportBinding')
                    ->toContain('SignedParts');

                // Verify XML is well-formed
                $dom = new DOMDocument();
                $result = $dom->loadXML($xml);
                expect($result)->toBeTrue();

                // Verify structure with XPath
                $xpath = new DOMXPath($dom);
                $xpath->registerNamespace('wsdl', 'http://schemas.xmlsoap.org/wsdl/');
                $xpath->registerNamespace('wsp', 'http://www.w3.org/ns/ws-policy');
                $xpath->registerNamespace('wsaw', 'http://www.w3.org/2006/05/addressing/wsdl');

                // Verify WSDL-level policy exists
                $wsdlPolicies = $xpath->query('/wsdl:definitions/wsp:Policy');
                expect($wsdlPolicies->length)->toBeGreaterThan(0);

                // Verify binding has policy
                $bindingPolicies = $xpath->query('//wsdl:binding/wsp:Policy');
                expect($bindingPolicies->length)->toBeGreaterThan(0);

                // Verify portType has addressing
                $addressingAttr = $xpath->query('//wsdl:portType[@name="CompleteSecureServicePortType"]/@wsaw:UsingAddressing');
                expect($addressingAttr->length)->toBe(1);
            });

            test('generates full document snapshot with all WS extensions', function (): void {
                // Arrange
                $wsdl = Wsdl::create('SnapshotService', 'http://example.com/snapshot')
                    ->complexType('OperationRequest')
                    ->element('id', XsdType::Int)
                    ->end()

                    ->complexType('OperationResponse')
                    ->element('status', XsdType::String)
                    ->end()

                    ->message('OperationInput')
                    ->part('parameters', 'tns:OperationRequest')
                    ->end()

                    ->message('OperationOutput')
                    ->part('parameters', 'tns:OperationResponse')
                    ->end()

                    ->portType('SnapshotServicePortType')
                    ->usingAddressing()
                    ->operation('Execute', 'OperationInput', 'OperationOutput')
                    ->action('Execute', 'http://example.com/Execute', 'http://example.com/ExecuteResponse')
                    ->end();

                $wsdl->policy('SnapshotPolicy', 'SnapshotPolicyName')
                    ->all()
                    ->assertion('http://example.com/policies', 'CustomAssertion')
                    ->end()
                    ->end();

                $wsdl->binding('SnapshotServiceBinding', 'SnapshotServicePortType')
                    ->policyReference('#SnapshotPolicy')
                    ->operation('Execute', 'urn:Execute')
                    ->end();

                $wsdl->service('SnapshotService')
                    ->port('SnapshotServicePort', 'SnapshotServiceBinding', 'http://example.com/soap/snapshot')
                    ->end();

                // Act
                $xml = $wsdl->build();

                // Assert
                expect($xml)->toMatchSnapshot();
            });
        });
    });

    describe('XML Validity', function (): void {
        describe('Happy Paths', function (): void {
            test('generates well-formed XML with WS-Policy', function (): void {
                // Arrange
                $wsdl = Wsdl::create('ValidPolicyService', 'http://example.com/validpolicy')
                    ->binding('ValidPolicyServiceBinding', 'ValidPolicyServicePortType')
                    ->policy('TestPolicy')
                    ->all()
                    ->assertion('http://example.com', 'TestAssertion')
                    ->end()
                    ->end()
                    ->operation('Test', 'urn:Test')
                    ->end();

                // Act
                $xml = $wsdl->build();
                $dom = new DOMDocument();
                $result = $dom->loadXML($xml);

                // Assert
                expect($result)->toBeTrue()
                    ->and($dom->documentElement)->not->toBeNull()
                    ->and($dom->documentElement->localName)->toBe('definitions');
            });

            test('generates well-formed XML with WS-Addressing', function (): void {
                // Arrange
                $wsdl = Wsdl::create('ValidAddressingService', 'http://example.com/validaddressing')
                    ->portType('ValidAddressingServicePortType')
                    ->usingAddressing()
                    ->operation('Test', 'TestInput', 'TestOutput')
                    ->action('Test', 'http://example.com/Test')
                    ->end()

                    ->binding('ValidAddressingServiceBinding', 'ValidAddressingServicePortType')
                    ->operation('Test', 'urn:Test')
                    ->end();

                // Act
                $xml = $wsdl->build();
                $dom = new DOMDocument();
                $result = $dom->loadXML($xml);

                // Assert
                expect($result)->toBeTrue()
                    ->and($dom->documentElement)->not->toBeNull();
            });

            test('generates well-formed XML with combined extensions', function (): void {
                // Arrange
                $wsdl = Wsdl::create('ValidCombinedService', 'http://example.com/validcombined')
                    ->portType('ValidCombinedServicePortType')
                    ->usingAddressing()
                    ->operation('Execute', 'ExecuteInput', 'ExecuteOutput')
                    ->action('Execute', 'http://example.com/Execute')
                    ->end()

                    ->binding('ValidCombinedServiceBinding', 'ValidCombinedServicePortType')
                    ->policy('CombinedPolicy')
                    ->all()
                    ->assertion('http://example.com', 'TestAssertion')
                    ->end()
                    ->end()
                    ->operation('Execute', 'urn:Execute')
                    ->end();

                // Act
                $xml = $wsdl->build();
                $dom = new DOMDocument();
                $result = $dom->loadXML($xml);

                // Assert
                expect($result)->toBeTrue();

                // Verify both extension namespaces are present
                $xpath = new DOMXPath($dom);
                $definitions = $xpath->query('//*[local-name()="definitions"]');
                expect($definitions->length)->toBe(1);

                $defElement = $definitions->item(0);
                expect($defElement->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'wsp'))->toBeTrue()
                    ->and($defElement->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'wsaw'))->toBeTrue();
            });
        });
    });
});
