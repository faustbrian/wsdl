<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl2\Wsdl2;

describe('Wsdl2Generator Coverage Edge Cases', function (): void {
    test('generates documentation as first child when parent has existing children', function (): void {
        $wsdl = Wsdl2::create('http://example.com/test');
        $interface = $wsdl->interface('TestInterface');
        $interface->operation('TestOp')
            ->pattern('http://www.w3.org/ns/wsdl/in-out')
            ->input('tns:RequestMessage')
            ->output('tns:ResponseMessage');
        $interface->documentation('Interface documentation');

        $xml = $wsdl->build();

        expect($xml)->toContain('<documentation>Interface documentation</documentation>');
    });

    test('generates simple content extension with prefixed custom base', function (): void {
        $wsdl = Wsdl2::create('http://example.com/test');
        $wsdl->complexType('CustomType')
            ->simpleContent()
            ->extension('tns:CustomBaseType')
            ->attribute('id', XsdType::String);

        $xml = $wsdl->build();

        expect($xml)->toContain('base="tns:CustomBaseType"');
    });

    test('generates element group with nullable and occurrence constraints', function (): void {
        $wsdl = Wsdl2::create('http://example.com/test');
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
