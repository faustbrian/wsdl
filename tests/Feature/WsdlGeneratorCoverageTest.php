<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Enums\SoapVersion;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use RuntimeException;

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

    test('throws exception when adding header without operation', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->message('HeaderMsg')->part('auth', XsdType::String)->end();
        $binding = $wsdl->binding('TestBinding', 'TestPort');

        expect(fn () => $binding->header('HeaderMsg', 'auth'))
            ->toThrow(RuntimeException::class, 'No operation exists to add header to');
    });

    test('throws exception when adding header fault without operation', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->message('FaultMsg')->part('error', XsdType::String)->end();
        $binding = $wsdl->binding('TestBinding', 'TestPort');

        expect(fn () => $binding->headerFault('FaultMsg', 'error', BindingUse::Literal))
            ->toThrow(RuntimeException::class, 'No operation exists to add header fault to');
    });

    test('throws exception when adding header fault without header', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->message('HeaderMsg')->part('auth', XsdType::String)->end();
        $wsdl->message('FaultMsg')->part('error', XsdType::String)->end();

        $binding = $wsdl->binding('TestBinding', 'TestPort');
        $binding->operation('TestOp', 'urn:test');
        // Get the binding again to trigger the "no header" case
        $binding->header('HeaderMsg', 'auth');

        expect(fn () => $wsdl->binding('AnotherBinding', 'TestPort')->operation('TestOp2', 'urn:test2')->headerFault('FaultMsg', 'error', BindingUse::Literal))
            ->toThrow(RuntimeException::class, 'No header exists to add fault to');
    });

    test('sets and gets binding use', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $binding = $wsdl->binding('TestBinding', 'TestPort')
            ->use(BindingUse::Encoded);

        expect($binding->getUse())->toBe(BindingUse::Encoded);
    });

    test('throws exception when adding input MIME without operation', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $binding = $wsdl->binding('TestBinding', 'TestPort');

        expect(fn () => $binding->inputMime())
            ->toThrow(RuntimeException::class, 'No operation exists to add MIME to');
    });

    test('throws exception when adding output MIME without operation', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $binding = $wsdl->binding('TestBinding', 'TestPort');

        expect(fn () => $binding->outputMime())
            ->toThrow(RuntimeException::class, 'No operation exists to add MIME to');
    });

    test('throws exception when adding HTTP operation without operation', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $binding = $wsdl->binding('TestBinding', 'TestPort');

        expect(fn () => $binding->httpOperation('GET'))
            ->toThrow(RuntimeException::class, 'No operation exists to add HTTP operation to');
    });

    test('throws exception when adding HTTP URL-encoded without operation', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $binding = $wsdl->binding('TestBinding', 'TestPort');

        expect(fn () => $binding->httpUrlEncoded())
            ->toThrow(RuntimeException::class, 'No operation exists to add HTTP URL-encoded to');
    });

    test('throws exception when adding HTTP URL-replacement without operation', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $binding = $wsdl->binding('TestBinding', 'TestPort');

        expect(fn () => $binding->httpUrlReplacement())
            ->toThrow(RuntimeException::class, 'No operation exists to add HTTP URL-replacement to');
    });

    test('sets operation action and fault action', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->message('InputMsg')->part('data', XsdType::String)->end();
        $wsdl->message('OutputMsg')->part('result', XsdType::String)->end();
        $wsdl->message('FaultMsg')->part('error', XsdType::String)->end();

        $wsdl->portType('TestPort')
            ->operation('TestOp', 'InputMsg', 'OutputMsg', 'FaultMsg')
            ->action('TestOp', 'http://example.com/input', 'http://example.com/output')
            ->faultAction('TestOp', 'FaultMsg', 'http://example.com/fault')
            ->end();

        $xml = $wsdl->build();

        expect($xml)->toContain('http://example.com/input');
        expect($xml)->toContain('http://example.com/output');
        expect($xml)->toContain('http://example.com/fault');
    });

    test('throws exception when setting fault action without action', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->message('InputMsg')->part('data', XsdType::String)->end();
        $wsdl->message('OutputMsg')->part('result', XsdType::String)->end();
        $wsdl->message('FaultMsg')->part('error', XsdType::String)->end();

        $portType = $wsdl->portType('TestPort');
        $portType->operation('TestOp', 'InputMsg', 'OutputMsg', 'FaultMsg');

        expect(fn () => $portType->faultAction('TestOp', 'FaultMsg', 'http://example.com/fault'))
            ->toThrow(RuntimeException::class, "No action defined for operation 'TestOp'. Call action() first.");
    });

    test('returns correct namespace for SOAP versions', function (): void {
        expect(SoapVersion::Soap11->namespace())->toBe('http://schemas.xmlsoap.org/wsdl/soap/');
        expect(SoapVersion::Soap12->namespace())->toBe('http://schemas.xmlsoap.org/wsdl/soap12/');
        expect(SoapVersion::Soap11->envelopeNamespace())->toBe('http://schemas.xmlsoap.org/soap/envelope/');
        expect(SoapVersion::Soap12->envelopeNamespace())->toBe('http://www.w3.org/2003/05/soap-envelope');
    });

    test('formats XSD types correctly for WSDL 1.1 and 2.0', function (): void {
        expect(XsdType::String->forWsdl1())->toBe('xsd:string');
        expect(XsdType::String->forWsdl2())->toBe('xs:string');
        expect(XsdType::String->localName())->toBe('string');
        expect(XsdType::SwaRef->forWsdl1())->toBe('swaRef'); // Special case without prefix
        expect(XsdType::SwaRef->forWsdl2())->toBe('swaRef'); // Special case without prefix
    });

    test('applies WS-Addressing actions from Operation to portType and binding', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->operation('TestOperation')
            ->input('request', XsdType::String)
            ->output('response', XsdType::String)
            ->action('http://example.com/TestOperation/input', 'http://example.com/TestOperation/output')
            ->end();

        $xml = $wsdl->build();

        expect($xml)->toContain('http://example.com/TestOperation/input');
        expect($xml)->toContain('http://example.com/TestOperation/output');
    });

    test('throws exception when setting fault action on Operation without action', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $operation = $wsdl->operation('TestOperation')
            ->input('request', XsdType::String)
            ->output('response', XsdType::String)
            ->fault('error', XsdType::String);

        expect(fn () => $operation->faultAction('error', 'http://example.com/TestOperation/fault'))
            ->toThrow(RuntimeException::class, "No action defined for operation 'TestOperation'. Call action() first.");
    });

    test('generates documentation on empty element (appendChild path)', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $wsdl->message('EmptyMessage')
            ->documentation('Documentation before parts')
            ->end();

        $xml = $wsdl->build();

        expect($xml)->toContain('<wsdl:message name="EmptyMessage">');
        expect($xml)->toContain('<wsdl:documentation>Documentation before parts</wsdl:documentation>');
    });

    test('generates annotation on empty element (appendChild path)', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');
        $type = $wsdl->complexType('AnnotatedEmptyType');
        $type->annotation()
            ->documentation('Annotation documentation');

        $xml = $wsdl->build();

        expect($xml)->toContain('<xsd:complexType name="AnnotatedEmptyType">');
        expect($xml)->toContain('<xsd:annotation>');
        expect($xml)->toContain('<xsd:documentation>Annotation documentation</xsd:documentation>');
    });

    test('handles portType with operations where only some have addressing actions', function (): void {
        $wsdl = Wsdl::create('TestService', 'http://example.com/test');

        // Create messages
        $wsdl->message('Input1')->part('param1', XsdType::String)->end();
        $wsdl->message('Output1')->part('result1', XsdType::String)->end();
        $wsdl->message('Input2')->part('param2', XsdType::String)->end();
        $wsdl->message('Output2')->part('result2', XsdType::String)->end();

        // Create portType with two operations
        $portType = $wsdl->portType('MixedPort');
        $portType->operation('OpWithAction', 'Input1', 'Output1');
        $portType->operation('OpWithoutAction', 'Input2', 'Output2');

        // Only add action to first operation
        $portType->action('OpWithAction', 'http://example.com/OpWithAction');

        $xml = $wsdl->build();

        expect($xml)->toContain('<wsdl:operation name="OpWithAction">');
        expect($xml)->toContain('<wsdl:operation name="OpWithoutAction">');
        expect($xml)->toContain('http://example.com/OpWithAction');
    });
});
