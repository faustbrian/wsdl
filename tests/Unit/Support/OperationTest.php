<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Core\Operation;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

describe('Operation (High-level API)', function (): void {
    describe('Happy Paths', function (): void {
        test('creates operation with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $op = $wsdl->operation('GetUser');

            expect($op->getName())->toBe('GetUser');
        });

        test('adds input parameters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $op = $wsdl->operation('GetUser')
                ->input('userId', XsdType::Int)
                ->input('includeDetails', XsdType::Boolean);

            $inputs = $op->getInputs();

            expect($inputs)->toHaveCount(2)
                ->and($inputs[0]['name'])->toBe('userId')
                ->and($inputs[0]['type'])->toBe('xsd:int')
                ->and($inputs[1]['name'])->toBe('includeDetails')
                ->and($inputs[1]['type'])->toBe('xsd:boolean');
        });

        test('adds output parameters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $op = $wsdl->operation('GetUser')
                ->output('user', 'tns:UserType')
                ->output('success', XsdType::Boolean);

            $outputs = $op->getOutputs();

            expect($outputs)->toHaveCount(2)
                ->and($outputs[0]['name'])->toBe('user')
                ->and($outputs[0]['type'])->toBe('tns:UserType')
                ->and($outputs[1]['name'])->toBe('success')
                ->and($outputs[1]['type'])->toBe('xsd:boolean');
        });

        test('adds fault parameters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $op = $wsdl->operation('GetUser')
                ->fault('errorCode', XsdType::Int)
                ->fault('errorMessage', XsdType::String);

            $faults = $op->getFaults();

            expect($faults)->toHaveCount(2)
                ->and($faults[0]['name'])->toBe('errorCode')
                ->and($faults[1]['name'])->toBe('errorMessage');
        });

        test('sets custom SOAP action', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $op = $wsdl->operation('GetUser')
                ->soapAction('urn:custom:GetUser');

            expect($op->getSoapAction())->toBe('urn:custom:GetUser');
        });

        test('end creates all required WSDL artifacts', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->operation('GetUser')
                ->input('userId', XsdType::Int)
                ->output('user', XsdType::String)
                ->end();

            // Should create complex types
            expect($wsdl->getComplexTypes())
                ->toHaveKey('GetUserRequest')
                ->toHaveKey('GetUserResponse');

            // Should create messages
            expect($wsdl->getMessages())
                ->toHaveKey('GetUserInput')
                ->toHaveKey('GetUserOutput');

            // Should create port type
            expect($wsdl->getPortTypes())
                ->toHaveKey('TestServicePortType');

            // Should create binding
            expect($wsdl->getBindings())
                ->toHaveKey('TestServiceBinding');
        });

        test('end creates fault artifacts when faults are defined', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->operation('GetUser')
                ->input('userId', XsdType::Int)
                ->output('user', XsdType::String)
                ->fault('code', XsdType::Int)
                ->end();

            // Should create fault type
            expect($wsdl->getComplexTypes())->toHaveKey('GetUserFault');

            // Should create fault message
            expect($wsdl->getMessages())->toHaveKey('GetUserFault');
        });

        test('multiple operations share port type and binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->operation('GetUser')
                ->input('userId', XsdType::Int)
                ->output('user', XsdType::String)
                ->end()
                ->operation('UpdateUser')
                ->input('userId', XsdType::Int)
                ->input('name', XsdType::String)
                ->output('success', XsdType::Boolean)
                ->end();

            // Should have only one port type and binding
            expect($wsdl->getPortTypes())->toHaveCount(1)
                ->and($wsdl->getBindings())->toHaveCount(1);

            // Port type should have both operations
            $portType = $wsdl->getPortTypes()['TestServicePortType'];

            expect($portType->getOperations())->toHaveCount(2);

            // Binding should have both operations
            $binding = $wsdl->getBindings()['TestServiceBinding'];

            expect($binding->getOperations())->toHaveCount(2);
        });

        test('uses custom SOAP action when provided', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->operation('GetUser')
                ->input('userId', XsdType::Int)
                ->output('user', XsdType::String)
                ->soapAction('urn:custom:action')
                ->end();

            $binding = $wsdl->getBindings()['TestServiceBinding'];
            $operations = $binding->getOperations();

            expect($operations['GetUser']->soapAction)->toBe('urn:custom:action');
        });

        test('generates default SOAP action from namespace and operation name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->operation('GetUser')
                ->input('userId', XsdType::Int)
                ->output('user', XsdType::String)
                ->end();

            $binding = $wsdl->getBindings()['TestServiceBinding'];
            $operations = $binding->getOperations();

            expect($operations['GetUser']->soapAction)->toBe('http://test.example.com//GetUser');
        });
    });

    describe('Fluent Interface', function (): void {
        test('chains all methods fluently', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $result = $wsdl->operation('ComplexOperation')
                ->input('id', XsdType::Int)
                ->input('name', XsdType::String)
                ->output('result', XsdType::Boolean)
                ->output('message', XsdType::String)
                ->fault('code', XsdType::Int)
                ->fault('description', XsdType::String)
                ->soapAction('urn:test:operation')
                ->end();

            expect($result)->toBe($wsdl);
        });
    });
});
