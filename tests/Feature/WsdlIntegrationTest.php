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

describe('WSDL Integration', function (): void {
    describe('Complete Service Definition', function (): void {
        test('builds a complete user service WSDL', function (): void {
            $wsdl = Wsdl::create('UserService', 'http://example.com/user')
                ->soapVersion(SoapVersion::Soap11)
                ->defaultStyle(BindingStyle::Document)
                ->defaultUse(BindingUse::Literal)

                // Define simple types
                ->simpleType('EmailType')
                ->base(XsdType::String)
                ->pattern('[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}')
                ->maxLength(255)
                ->end()

                ->simpleType('StatusType')
                ->base(XsdType::String)
                ->enumeration('active', 'inactive', 'pending')
                ->end()

                // Define complex types
                ->complexType('UserType')
                ->element('id', XsdType::Int)
                ->element('email', 'tns:EmailType')
                ->element('name', XsdType::String)
                ->element('status', 'tns:StatusType')
                ->element('createdAt', XsdType::DateTime)
                ->end()

                ->complexType('GetUserRequest')
                ->element('userId', XsdType::Int)
                ->end()

                ->complexType('GetUserResponse')
                ->element('user', 'tns:UserType', true)
                ->element('found', XsdType::Boolean)
                ->end()

                // Define messages
                ->message('GetUserInput')
                ->part('parameters', 'tns:GetUserRequest')
                ->end()

                ->message('GetUserOutput')
                ->part('parameters', 'tns:GetUserResponse')
                ->end()

                // Define port type
                ->portType('UserServicePortType')
                ->operation('GetUser', 'GetUserInput', 'GetUserOutput')
                ->end()

                // Define binding
                ->binding('UserServiceBinding', 'UserServicePortType')
                ->operation('GetUser', 'http://example.com/user/GetUser')
                ->end()

                // Define service
                ->service('UserService')
                ->port('UserServicePort', 'UserServiceBinding', 'http://example.com/soap/user')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toMatchSnapshot()
                ->toContain('<?xml version="1.0" encoding="UTF-8"?>')
                ->toContain('wsdl:definitions')
                ->toContain('name="UserService"')
                ->toContain('targetNamespace="http://example.com/user"')

                // Verify namespaces
                ->toContain('xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"')
                ->toContain('xmlns:xsd="http://www.w3.org/2001/XMLSchema"')
                ->toContain('xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"')
                ->toContain('xmlns:tns="http://example.com/user"')

                // Verify simple types
                ->toContain('name="EmailType"')
                ->toContain('name="StatusType"')
                ->toContain('xsd:enumeration')

                // Verify complex types
                ->toContain('name="UserType"')
                ->toContain('name="GetUserRequest"')
                ->toContain('name="GetUserResponse"')

                // Verify messages
                ->toContain('name="GetUserInput"')
                ->toContain('name="GetUserOutput"')

                // Verify port type
                ->toContain('name="UserServicePortType"')

                // Verify binding
                ->toContain('name="UserServiceBinding"')
                ->toContain('soap:binding')
                ->toContain('style="document"')

                // Verify service
                ->toContain('name="UserServicePort"')
                ->toContain('location="http://example.com/soap/user"');
        });

        test('builds WSDL using high-level operation API', function (): void {
            $wsdl = Wsdl::create('CalculatorService', 'http://example.com/calc')
                ->operation('Add')
                ->input('a', XsdType::Int)
                ->input('b', XsdType::Int)
                ->output('result', XsdType::Int)
                ->end()
                ->operation('Subtract')
                ->input('a', XsdType::Int)
                ->input('b', XsdType::Int)
                ->output('result', XsdType::Int)
                ->end()
                ->operation('Divide')
                ->input('dividend', XsdType::Decimal)
                ->input('divisor', XsdType::Decimal)
                ->output('quotient', XsdType::Decimal)
                ->fault('code', XsdType::Int)
                ->fault('message', XsdType::String)
                ->end()
                ->service('CalculatorService')
                ->port('CalculatorPort', 'CalculatorServiceBinding', 'http://example.com/soap/calc')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toMatchSnapshot()
                ->toContain('name="Add"')
                ->toContain('name="Subtract"')
                ->toContain('name="Divide"')

                // Verify request/response types were auto-generated
                ->toContain('name="AddRequest"')
                ->toContain('name="AddResponse"')
                ->toContain('name="SubtractRequest"')
                ->toContain('name="SubtractResponse"')
                ->toContain('name="DivideRequest"')
                ->toContain('name="DivideResponse"')

                // Verify fault type was created
                ->toContain('name="DivideFault"')

                // Verify messages
                ->toContain('name="AddInput"')
                ->toContain('name="AddOutput"')

                // Verify service port
                ->toContain('name="CalculatorPort"');
        });

        test('builds WSDL with type inheritance', function (): void {
            $wsdl = Wsdl::create('EntityService', 'http://example.com/entity')
                ->complexType('BaseEntity')
                ->abstract()
                ->element('id', XsdType::Int)
                ->element('createdAt', XsdType::DateTime)
                ->element('updatedAt', XsdType::DateTime, true)
                ->end()

                ->complexType('PersonEntity')
                ->extends('BaseEntity')
                ->element('firstName', XsdType::String)
                ->element('lastName', XsdType::String)
                ->end()

                ->complexType('EmployeeEntity')
                ->extends('PersonEntity')
                ->element('employeeId', XsdType::String)
                ->element('department', XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toMatchSnapshot()
                ->toContain('abstract="true"')
                ->toContain('xsd:complexContent')
                ->toContain('xsd:extension')
                ->toContain('base="tns:BaseEntity"')
                ->toContain('base="tns:PersonEntity"');
        });

        test('builds WSDL with SOAP 1.2', function (): void {
            $wsdl = Wsdl::create('ModernService', 'http://example.com/modern')
                ->soapVersion(SoapVersion::Soap12)
                ->binding('ModernBinding', 'ModernPortType')
                ->operation('DoSomething', 'urn:DoSomething')
                ->end()
                ->service('ModernService')
                ->port('ModernPort', 'ModernBinding', 'http://example.com/soap12')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toMatchSnapshot()
                ->toContain('xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap12/"');
        });

        test('builds WSDL with RPC style', function (): void {
            $wsdl = Wsdl::create('RpcService', 'http://example.com/rpc')
                ->defaultStyle(BindingStyle::Rpc)
                ->defaultUse(BindingUse::Encoded)
                ->binding('RpcBinding', 'RpcPortType')
                ->style(BindingStyle::Rpc)
                ->operation('CallMethod', 'urn:CallMethod')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toMatchSnapshot()
                ->toContain('style="rpc"');
        });

        test('builds WSDL with array elements', function (): void {
            $wsdl = Wsdl::create('ListService', 'http://example.com/list')
                ->complexType('StringListType')
                ->element('items', XsdType::String, false, 0, -1)
                ->end()

                ->complexType('OptionalListType')
                ->element('values', XsdType::Int, true, 0, 10)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toMatchSnapshot()
                ->toContain('minOccurs="0"')
                ->toContain('maxOccurs="unbounded"')
                ->toContain('maxOccurs="10"');
        });
    });

    describe('XML Validity', function (): void {
        test('generates well-formed XML', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('TestType')
                ->element('value', XsdType::String)
                ->end()
                ->message('TestMessage')
                ->part('body', 'tns:TestType')
                ->end();

            $xml = $wsdl->build();
            $dom = new DOMDocument();
            $result = $dom->loadXML($xml);

            expect($result)->toBeTrue();
        });

        test('DOM output can be further manipulated', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $dom = $wsdl->buildDom();

            expect($dom->documentElement)->not->toBeNull()
                ->and($dom->documentElement->localName)->toBe('definitions');
        });
    });
});
