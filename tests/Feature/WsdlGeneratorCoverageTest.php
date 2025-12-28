<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

describe('WsdlGenerator Coverage Edge Cases', function (): void {
    test('generates documentation as first child when parent has existing children', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->message('TestMessage')
            ->part('field1', XsdType::String)
            ->documentation('Message documentation')
            ->end();

        $xml = $wsdl->build();

        expect($xml)->toContain('<wsdl:message name="TestMessage">');
        expect($xml)->toContain('<wsdl:documentation>Message documentation</wsdl:documentation>');
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
        $type = $wsdl->complexType('AnnotatedType');
        $type->element('field1', XsdType::String);
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
});
