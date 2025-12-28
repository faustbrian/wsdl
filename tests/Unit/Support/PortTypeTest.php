<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Core\PortTypeOperation;
use Cline\WsdlBuilder\Wsdl;

describe('PortType', function (): void {
    describe('Happy Paths', function (): void {
        test('creates port type with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $portType = $wsdl->portType('UserServicePortType');

            expect($portType->getName())->toBe('UserServicePortType');
        });

        test('adds operation with input and output messages', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $portType = $wsdl->portType('UserServicePortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse');

            $operations = $portType->getOperations();

            expect($operations)->toHaveCount(1)
                ->and($operations['GetUser']->name)->toBe('GetUser')
                ->and($operations['GetUser']->input)->toBe('GetUserRequest')
                ->and($operations['GetUser']->output)->toBe('GetUserResponse')
                ->and($operations['GetUser']->fault)->toBeNull();
        });

        test('adds operation with fault message', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $portType = $wsdl->portType('UserServicePortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse', 'GetUserFault');

            $operations = $portType->getOperations();

            expect($operations['GetUser']->fault)->toBe('GetUserFault');
        });

        test('adds multiple operations', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $portType = $wsdl->portType('UserServicePortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse')
                ->operation('CreateUser', 'CreateUserRequest', 'CreateUserResponse')
                ->operation('DeleteUser', 'DeleteUserRequest', 'DeleteUserResponse');

            expect($portType->getOperations())->toHaveCount(3);
        });

        test('end returns parent wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->portType('TestPortType')->end();

            expect($result)->toBe($wsdl);
        });

        test('fluent interface chains operations', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $result = $wsdl->portType('UserServicePortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse')
                ->operation('UpdateUser', 'UpdateUserRequest', 'UpdateUserResponse', 'UpdateUserFault')
                ->end();

            expect($result)->toBe($wsdl);
        });
    });
});

describe('PortTypeOperation', function (): void {
    test('creates readonly port type operation without fault', function (): void {
        $op = new PortTypeOperation('GetUser', 'GetUserRequest', 'GetUserResponse');

        expect($op->name)->toBe('GetUser')
            ->and($op->input)->toBe('GetUserRequest')
            ->and($op->output)->toBe('GetUserResponse')
            ->and($op->fault)->toBeNull();
    });

    test('creates readonly port type operation with fault', function (): void {
        $op = new PortTypeOperation('GetUser', 'GetUserRequest', 'GetUserResponse', 'GetUserFault');

        expect($op->fault)->toBe('GetUserFault');
    });
});
