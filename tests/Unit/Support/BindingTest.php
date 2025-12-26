<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\BindingStyle;
use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Core\Binding;
use Cline\WsdlBuilder\Core\BindingOperation;
use Cline\WsdlBuilder\Wsdl;

describe('Binding', function (): void {
    describe('Happy Paths', function (): void {
        test('creates binding with name and port type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType');

            expect($binding->getName())->toBe('TestBinding')
                ->and($binding->getPortType())->toBe('TestPortType');
        });

        test('defaults to document style', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType');

            expect($binding->getStyle())->toBe(BindingStyle::Document);
        });

        test('sets custom style', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->style(BindingStyle::Rpc);

            expect($binding->getStyle())->toBe(BindingStyle::Rpc);
        });

        test('defaults to HTTP transport', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType');

            expect($binding->getTransport())->toBe(Wsdl::HTTP_TRANSPORT);
        });

        test('sets custom transport', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->transport('http://custom.transport/');

            expect($binding->getTransport())->toBe('http://custom.transport/');
        });

        test('adds operation with name and SOAP action', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->operation('GetUser', 'http://test.example.com/GetUser');

            $operations = $binding->getOperations();

            expect($operations)->toHaveCount(1)
                ->and($operations['GetUser']->name)->toBe('GetUser')
                ->and($operations['GetUser']->soapAction)->toBe('http://test.example.com/GetUser');
        });

        test('adds operation with custom style and use', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->operation('GetUser', 'urn:GetUser', BindingStyle::Rpc, BindingUse::Encoded);

            $operations = $binding->getOperations();

            expect($operations['GetUser']->style)->toBe(BindingStyle::Rpc)
                ->and($operations['GetUser']->use)->toBe(BindingUse::Encoded);
        });

        test('operation inherits default style and use', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->defaultStyle(BindingStyle::Rpc)
                ->defaultUse(BindingUse::Encoded);

            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->operation('GetUser', 'urn:GetUser');

            $operations = $binding->getOperations();

            expect($operations['GetUser']->style)->toBe(BindingStyle::Rpc)
                ->and($operations['GetUser']->use)->toBe(BindingUse::Encoded);
        });

        test('adds multiple operations', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->operation('GetUser', 'urn:GetUser')
                ->operation('UpdateUser', 'urn:UpdateUser')
                ->operation('DeleteUser', 'urn:DeleteUser');

            expect($binding->getOperations())->toHaveCount(3);
        });

        test('end returns parent wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->binding('TestBinding', 'TestPortType')->end();

            expect($result)->toBe($wsdl);
        });
    });
});

describe('BindingOperation', function (): void {
    test('creates readonly binding operation', function (): void {
        $op = new BindingOperation(
            'TestOp',
            'urn:test',
            BindingStyle::Document,
            BindingUse::Literal,
        );

        expect($op->name)->toBe('TestOp')
            ->and($op->soapAction)->toBe('urn:test')
            ->and($op->style)->toBe(BindingStyle::Document)
            ->and($op->use)->toBe(BindingUse::Literal);
    });
});
