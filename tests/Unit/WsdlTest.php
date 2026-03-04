<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Core\Binding;
use Cline\WsdlBuilder\Core\Message;
use Cline\WsdlBuilder\Core\PortType;
use Cline\WsdlBuilder\Core\Service;
use Cline\WsdlBuilder\Enums\BindingStyle;
use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Enums\SoapVersion;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;

describe('Wsdl', function (): void {
    describe('Happy Paths', function (): void {
        test('creates a new WSDL builder', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/myservice');

            expect($wsdl)
                ->toBeInstanceOf(Wsdl::class)
                ->and($wsdl->getName())->toBe('MyService')
                ->and($wsdl->getTargetNamespace())->toBe('http://example.com/myservice');
        });

        test('sets SOAP version', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/')
                ->soapVersion(SoapVersion::Soap12);

            expect($wsdl->getSoapVersion())->toBe(SoapVersion::Soap12)
                ->and($wsdl->getSoapNamespace())->toBe(Wsdl::SOAP12_NS);
        });

        test('defaults to SOAP 1.1', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/');

            expect($wsdl->getSoapVersion())->toBe(SoapVersion::Soap11)
                ->and($wsdl->getSoapNamespace())->toBe(Wsdl::SOAP_NS);
        });

        test('sets default style', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/')
                ->defaultStyle(BindingStyle::Rpc);

            expect($wsdl->getDefaultStyle())->toBe(BindingStyle::Rpc);
        });

        test('sets default use', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/')
                ->defaultUse(BindingUse::Encoded);

            expect($wsdl->getDefaultUse())->toBe(BindingUse::Encoded);
        });

        test('creates simple type', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/');
            $type = $wsdl->simpleType('PhoneNumber');

            expect($type)
                ->toBeInstanceOf(SimpleType::class)
                ->and($wsdl->getSimpleTypes())->toHaveKey('PhoneNumber');
        });

        test('creates complex type', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/');
            $type = $wsdl->complexType('Person');

            expect($type)
                ->toBeInstanceOf(ComplexType::class)
                ->and($wsdl->getComplexTypes())->toHaveKey('Person');
        });

        test('creates message', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/');
            $message = $wsdl->message('GetUserRequest');

            expect($message)
                ->toBeInstanceOf(Message::class)
                ->and($wsdl->getMessages())->toHaveKey('GetUserRequest');
        });

        test('creates port type', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/');
            $portType = $wsdl->portType('UserServicePortType');

            expect($portType)
                ->toBeInstanceOf(PortType::class)
                ->and($wsdl->getPortTypes())->toHaveKey('UserServicePortType');
        });

        test('creates binding', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/');
            $binding = $wsdl->binding('UserServiceBinding', 'UserServicePortType');

            expect($binding)
                ->toBeInstanceOf(Binding::class)
                ->and($wsdl->getBindings())->toHaveKey('UserServiceBinding');
        });

        test('creates service', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            expect($service)
                ->toBeInstanceOf(Service::class)
                ->and($wsdl->getServices())->toHaveKey('UserService');
        });

        test('fluent interface returns back to wsdl', function (): void {
            $wsdl = Wsdl::create('MyService', 'http://example.com/')
                ->simpleType('PhoneNumber')
                ->base(XsdType::String)
                ->pattern('[0-9]{10}')
                ->end()
                ->complexType('Person')
                ->element('name', XsdType::String)
                ->end()
                ->message('GetPersonRequest')
                ->part('id', XsdType::Int)
                ->end();

            expect($wsdl)->toBeInstanceOf(Wsdl::class);
        });
    });

    describe('Namespace Constants', function (): void {
        test('has correct WSDL namespace', function (): void {
            expect(Wsdl::WSDL_NS)->toBe('http://schemas.xmlsoap.org/wsdl/');
        });

        test('has correct XSD namespace', function (): void {
            expect(Wsdl::XSD_NS)->toBe('http://www.w3.org/2001/XMLSchema');
        });

        test('has correct SOAP namespace', function (): void {
            expect(Wsdl::SOAP_NS)->toBe('http://schemas.xmlsoap.org/wsdl/soap/');
        });

        test('has correct SOAP 12 namespace', function (): void {
            expect(Wsdl::SOAP12_NS)->toBe('http://schemas.xmlsoap.org/wsdl/soap12/');
        });

        test('has correct HTTP transport', function (): void {
            expect(Wsdl::HTTP_TRANSPORT)->toBe('http://schemas.xmlsoap.org/soap/http');
        });
    });
});
