<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

describe('WsdlGenerator Coverage Edge Cases', function (): void {
    test('generates documentation as first child when parent has existing children', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $type = $wsdl->complexType('DocumentedType');
        $type->element('field1', XsdType::String);
        $type->documentation('Type documentation');

        $xml = $wsdl->build();

        expect($xml)->toContain('<xsd:complexType name="DocumentedType">');
        expect($xml)->toContain('<xsd:documentation>Type documentation</xsd:documentation>');
    });

    test('generates simple content extension with prefixed custom base', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->complexType('CustomBase')
            ->simpleContent()
            ->extension('tns:CustomBaseType')
            ->attribute('id', XsdType::String);

        $xml = $wsdl->build();

        expect($xml)->toContain('base="tns:CustomBaseType"');
    });

    test('generates annotation as first child when parent has existing children', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $type = $wsdl->simpleType('AnnotatedType')
            ->base(XsdType::String)
            ->pattern('[A-Z]+');

        $type->annotation()
            ->documentation('Pattern explanation')
            ->appInfo('Custom app info');

        $xml = $wsdl->build();

        expect($xml)->toContain('<xsd:annotation>');
        expect($xml)->toContain('<xsd:documentation>Pattern explanation</xsd:documentation>');
        expect($xml)->toContain('<xsd:appinfo>Custom app info</xsd:appinfo>');
    });

    test('generates element group with nullable and occurrence constraints', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->elementGroup('OptionalFields')
            ->element('name', XsdType::String, true, 0, 1)
            ->element('tags', XsdType::String, false, 1, -1);

        $xml = $wsdl->build();

        expect($xml)->toContain('nillable="true"');
        expect($xml)->toContain('minOccurs="0"');
        expect($xml)->toContain('minOccurs="1"');
        expect($xml)->toContain('maxOccurs="unbounded"');
    });

    test('generates SOAP header fault with namespace and encodingStyle', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');

        $wsdl->message('RequestMessage')->part('body', 'tns:RequestType');
        $wsdl->message('ResponseMessage')->part('body', 'tns:ResponseType');
        $wsdl->message('AuthHeader')->part('credentials', XsdType::String);
        $wsdl->message('AuthFault')->part('error', XsdType::String);

        $wsdl->portType('TestPort')
            ->operation('TestOp', 'RequestMessage', 'ResponseMessage');

        $binding = $wsdl->binding('TestBinding', 'TestPort')
            ->soap()->document();

        $binding->operation('TestOp')
            ->soapAction('http://example.com/test')
            ->input()
            ->soapBody()
            ->header('AuthHeader', 'credentials')
            ->headerFault('AuthFault', 'error')
            ->namespace('http://example.com/auth')
            ->encodingStyle('http://schemas.xmlsoap.org/soap/encoding/');

        $xml = $wsdl->build();

        expect($xml)->toContain('namespace="http://example.com/auth"');
        expect($xml)->toContain('encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"');
    });

    test('generates WS-Policy with security assertions and references', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');

        $securityPolicy = $wsdl->policy('SecurityPolicy');
        $securityPolicy->exactlyOne()
            ->assertion('wsp:UsernameToken', 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702')
            ->attribute('IncludeToken', 'AlwaysToRecipient');

        $servicePolicy = $wsdl->policy('ServicePolicy');
        $servicePolicy->policyReference('#SecurityPolicy');

        $wsdl->service('TestService')
            ->policy($servicePolicy);

        $xml = $wsdl->build();

        expect($xml)->toContain('wsp:PolicyReference');
        expect($xml)->toContain('URI="#SecurityPolicy"');
    });

    test('generates WS-Addressing with actions for operations without explicit actions', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');

        $wsdl->message('Request1')->part('data', XsdType::String);
        $wsdl->message('Response1')->part('result', XsdType::String);
        $wsdl->message('Request2')->part('data', XsdType::String);
        $wsdl->message('Response2')->part('result', XsdType::String);

        $portType = $wsdl->portType('TestPort');
        $portType->operation('Operation1', 'Request1', 'Response1');
        $portType->operation('Operation2', 'Request2', 'Response2')
            ->inputAction('http://example.com/custom');

        $portType->addressing();

        $xml = $wsdl->build();

        expect($xml)->toContain('wsam:Action');
    });
});
