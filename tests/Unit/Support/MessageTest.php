<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Core\Message;
use Cline\WsdlBuilder\Core\MessagePart;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

describe('Message', function (): void {
    describe('Happy Paths', function (): void {
        test('creates message with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $message = $wsdl->message('GetUserRequest');

            expect($message->getName())->toBe('GetUserRequest');
        });

        test('adds part with XsdType enum', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $message = $wsdl->message('GetUserRequest')
                ->part('userId', XsdType::Int);

            $parts = $message->getParts();

            expect($parts)->toHaveCount(1)
                ->and($parts[0]->name)->toBe('userId')
                ->and($parts[0]->type)->toBe('xsd:int');
        });

        test('adds part with string type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $message = $wsdl->message('GetUserRequest')
                ->part('parameters', 'tns:GetUserRequestType');

            $parts = $message->getParts();

            expect($parts[0]->type)->toBe('tns:GetUserRequestType');
        });

        test('adds multiple parts', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $message = $wsdl->message('GetUserRequest')
                ->part('userId', XsdType::Int)
                ->part('includeDetails', XsdType::Boolean);

            $parts = $message->getParts();

            expect($parts)->toHaveCount(2)
                ->and($parts[0]->name)->toBe('userId')
                ->and($parts[1]->name)->toBe('includeDetails');
        });

        test('end returns parent wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->message('TestMessage')->end();

            expect($result)->toBe($wsdl);
        });

        test('fluent interface chains parts', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->message('ComplexMessage')
                ->part('id', XsdType::Int)
                ->part('name', XsdType::String)
                ->part('data', 'tns:CustomType')
                ->end();

            expect($result)->toBe($wsdl);

            $message = $wsdl->getMessages()['ComplexMessage'];

            expect($message->getParts())->toHaveCount(3);
        });
    });
});

describe('MessagePart', function (): void {
    test('creates readonly message part', function (): void {
        $part = new MessagePart('testPart', 'xsd:string');

        expect($part->name)->toBe('testPart')
            ->and($part->type)->toBe('xsd:string');
    });
});
