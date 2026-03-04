<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\SoapVersion;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

describe('WsdlGenerator', function (): void {
    describe('Happy Paths', function (): void {
        test('generates valid XML', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('TestRequest')
                ->element('id', XsdType::Int)
                ->end()
                ->complexType('TestResponse')
                ->element('result', XsdType::String)
                ->end()
                ->message('TestInput')
                ->part('parameters', 'tns:TestRequest')
                ->end()
                ->message('TestOutput')
                ->part('parameters', 'tns:TestResponse')
                ->end()
                ->portType('TestPortType')
                ->operation('Test', 'TestInput', 'TestOutput')
                ->end()
                ->binding('TestBinding', 'TestPortType')
                ->operation('Test', 'http://test.example.com/Test')
                ->end()
                ->service('TestService')
                ->port('TestPort', 'TestBinding', 'http://test.example.com/soap')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('<?xml version="1.0" encoding="UTF-8"?>')
                ->and($xml)->toContain('wsdl:definitions')
                ->and($xml)->toContain('name="TestService"')
                ->and($xml)->toContain('targetNamespace="http://test.example.com/"');
        });

        test('generates DOMDocument', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $dom = $wsdl->buildDom();

            expect($dom)->toBeInstanceOf(DOMDocument::class);
        });

        test('includes types section with complex types', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('Person')
                ->element('firstName', XsdType::String)
                ->element('lastName', XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('wsdl:types')
                ->and($xml)->toContain('xsd:schema')
                ->and($xml)->toContain('xsd:complexType')
                ->and($xml)->toContain('name="Person"')
                ->and($xml)->toContain('name="firstName"')
                ->and($xml)->toContain('type="xsd:string"');
        });

        test('includes types section with simple types', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->simpleType('PhoneNumber')
                ->base(XsdType::String)
                ->pattern('[0-9]{10}')
                ->minLength(10)
                ->maxLength(10)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('xsd:simpleType')
                ->and($xml)->toContain('name="PhoneNumber"')
                ->and($xml)->toContain('xsd:restriction')
                ->and($xml)->toContain('base="xsd:string"')
                ->and($xml)->toContain('xsd:pattern')
                ->and($xml)->toContain('xsd:minLength')
                ->and($xml)->toContain('xsd:maxLength');
        });

        test('includes messages', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->message('GetUserRequest')
                ->part('userId', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('wsdl:message')
                ->and($xml)->toContain('name="GetUserRequest"')
                ->and($xml)->toContain('wsdl:part')
                ->and($xml)->toContain('name="userId"');
        });

        test('includes port types', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->message('TestInput')->end()
                ->message('TestOutput')->end()
                ->portType('TestPortType')
                ->operation('DoSomething', 'TestInput', 'TestOutput')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('wsdl:portType')
                ->and($xml)->toContain('name="TestPortType"')
                ->and($xml)->toContain('wsdl:operation')
                ->and($xml)->toContain('name="DoSomething"')
                ->and($xml)->toContain('wsdl:input')
                ->and($xml)->toContain('wsdl:output');
        });

        test('includes bindings', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->binding('TestBinding', 'TestPortType')
                ->operation('DoSomething', 'http://test.example.com/DoSomething')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('wsdl:binding')
                ->and($xml)->toContain('name="TestBinding"')
                ->and($xml)->toContain('type="tns:TestPortType"')
                ->and($xml)->toContain('soap:binding')
                ->and($xml)->toContain('soap:operation');
        });

        test('includes services', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->service('MyService')
                ->port('TestPort', 'TestBinding', 'http://example.com/soap')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('wsdl:service')
                ->and($xml)->toContain('name="MyService"')
                ->and($xml)->toContain('wsdl:port')
                ->and($xml)->toContain('name="TestPort"')
                ->and($xml)->toContain('soap:address')
                ->and($xml)->toContain('location="http://example.com/soap"');
        });

        test('uses SOAP 1.2 namespace when configured', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->soapVersion(SoapVersion::Soap12)
                ->binding('TestBinding', 'TestPortType')
                ->operation('Test', 'urn:test')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toContain('xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap12/"');
        });

        test('handles complex type inheritance', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('BaseType')
                ->element('id', XsdType::Int)
                ->end()
                ->complexType('ExtendedType')
                ->extends('BaseType')
                ->element('name', XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('xsd:complexContent')
                ->and($xml)->toContain('xsd:extension')
                ->and($xml)->toContain('base="tns:BaseType"');
        });

        test('handles abstract complex types', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('AbstractType')
                ->abstract()
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toContain('abstract="true"');
        });

        test('handles nullable elements', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('PersonType')
                ->element('middleName', XsdType::String, true)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toContain('nillable="true"');
        });

        test('handles element cardinality', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('ArrayType')
                ->element('items', XsdType::String, false, 0, -1)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('minOccurs="0"')
                ->and($xml)->toContain('maxOccurs="unbounded"');
        });
    });

    describe('Simple Type Restrictions', function (): void {
        test('generates enumeration restrictions', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->simpleType('Status')
                ->base(XsdType::String)
                ->enumeration('active', 'inactive', 'pending')
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('xsd:enumeration')
                ->and($xml)->toContain('value="active"')
                ->and($xml)->toContain('value="inactive"')
                ->and($xml)->toContain('value="pending"');
        });

        test('generates min/max inclusive restrictions', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->simpleType('Age')
                ->base(XsdType::Int)
                ->minInclusive(0)
                ->maxInclusive(150)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('xsd:minInclusive')
                ->and($xml)->toContain('value="0"')
                ->and($xml)->toContain('xsd:maxInclusive')
                ->and($xml)->toContain('value="150"');
        });

        test('generates min/max exclusive restrictions', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->simpleType('Percentage')
                ->base(XsdType::Decimal)
                ->minExclusive(0)
                ->maxExclusive(100)
                ->end();

            $xml = $wsdl->build();

            expect($xml)
                ->toContain('xsd:minExclusive')
                ->and($xml)->toContain('xsd:maxExclusive');
        });
    });

    describe('Edge Cases', function (): void {
        test('generates empty WSDL without types', function (): void {
            $wsdl = Wsdl::create('EmptyService', 'http://test.example.com/');
            $xml = $wsdl->build();

            expect($xml)
                ->toContain('wsdl:definitions')
                ->not->toContain('wsdl:types');
        });

        test('handles tns prefix for custom types in messages', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('PersonRequest')->end()
                ->message('GetPersonMessage')
                ->part('parameters', 'tns:PersonRequest')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toContain('element="tns:PersonRequest"');
        });
    });
});
