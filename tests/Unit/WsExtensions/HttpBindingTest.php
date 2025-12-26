<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Http\HttpBinding;
use Cline\WsdlBuilder\WsExtensions\Http\HttpOperation;
use Cline\WsdlBuilder\WsExtensions\Http\HttpUrlEncoded;
use Cline\WsdlBuilder\WsExtensions\Http\HttpUrlReplacement;

describe('HttpBinding', function (): void {
    describe('Happy Paths', function (): void {
        test('creates HTTP binding with GET verb', function (): void {
            $httpBinding = HttpBinding::get();

            expect($httpBinding->verb)->toBe('GET');
        });

        test('creates HTTP binding with POST verb', function (): void {
            $httpBinding = HttpBinding::post();

            expect($httpBinding->verb)->toBe('POST');
        });

        test('creates HTTP binding with PUT verb', function (): void {
            $httpBinding = HttpBinding::put();

            expect($httpBinding->verb)->toBe('PUT');
        });

        test('creates HTTP binding with DELETE verb', function (): void {
            $httpBinding = HttpBinding::delete();

            expect($httpBinding->verb)->toBe('DELETE');
        });

        test('creates HTTP binding with custom verb', function (): void {
            $httpBinding = HttpBinding::create('PATCH');

            expect($httpBinding->verb)->toBe('PATCH');
        });
    });
});

describe('HttpOperation', function (): void {
    describe('Happy Paths', function (): void {
        test('creates HTTP operation with location', function (): void {
            $httpOperation = HttpOperation::create('/users/(id)');

            expect($httpOperation->location)->toBe('/users/(id)');
        });

        test('creates HTTP operation with simple path', function (): void {
            $httpOperation = new HttpOperation('/api/endpoint');

            expect($httpOperation->location)->toBe('/api/endpoint');
        });
    });
});

describe('HttpUrlEncoded', function (): void {
    describe('Happy Paths', function (): void {
        test('creates HTTP URL-encoded element', function (): void {
            $urlEncoded = HttpUrlEncoded::create();

            expect($urlEncoded)->toBeInstanceOf(HttpUrlEncoded::class);
        });
    });
});

describe('HttpUrlReplacement', function (): void {
    describe('Happy Paths', function (): void {
        test('creates HTTP URL-replacement element', function (): void {
            $urlReplacement = HttpUrlReplacement::create();

            expect($urlReplacement)->toBeInstanceOf(HttpUrlReplacement::class);
        });
    });
});

describe('HTTP Binding Integration', function (): void {
    describe('Happy Paths', function (): void {
        test('binding supports HTTP binding with GET verb', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->httpBinding('GET');

            expect($binding->getHttpBinding())->not->toBeNull()
                ->and($binding->getHttpBinding()->verb)->toBe('GET');
        });

        test('binding supports HTTP binding with POST verb', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $binding = $wsdl->binding('TestBinding', 'TestPortType')
                ->httpBinding('POST');

            expect($binding->getHttpBinding())->not->toBeNull()
                ->and($binding->getHttpBinding()->verb)->toBe('POST');
        });

        test('generates WSDL with HTTP binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->message('GetUserRequest')
                ->part('userId', 'xsd:string')
                ->end()
                ->message('GetUserResponse')
                ->part('user', 'xsd:string')
                ->end()
                ->portType('UserPortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse')
                ->end()
                ->binding('UserBinding', 'UserPortType')
                ->httpBinding('GET')
                ->operation('GetUser', '')
                ->end();

            $generator = new \Cline\WsdlBuilder\WsdlGenerator($wsdl);
            $xml = $generator->generate();

            expect($xml)->toContain('xmlns:http="http://schemas.xmlsoap.org/wsdl/http/"')
                ->and($xml)->toContain('http:binding')
                ->and($xml)->toContain('verb="GET"');
        });

        test('generates WSDL with HTTP operation location', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->message('GetUserRequest')
                ->part('userId', 'xsd:string')
                ->end();
            $wsdl->message('GetUserResponse')
                ->part('user', 'xsd:string')
                ->end();
            $wsdl->portType('UserPortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse')
                ->end();
            $wsdl->binding('UserBinding', 'UserPortType')
                ->httpBinding('GET')
                ->operation('GetUser', '');

            // Add HTTP operation to the last operation
            $bindings = $wsdl->getBindings();
            $binding = $bindings['UserBinding'];
            $operations = $binding->getOperations();
            $lastOperation = end($operations);
            $lastOperation->setHttpOperation(HttpOperation::create('/users/(userId)'));

            $generator = new \Cline\WsdlBuilder\WsdlGenerator($wsdl);
            $xml = $generator->generate();

            expect($xml)->toContain('http:operation')
                ->and($xml)->toContain('location="/users/(userId)"');
        });

        test('generates WSDL with HTTP URL-encoded input', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->message('CreateUserRequest')
                ->part('username', 'xsd:string')
                ->end();
            $wsdl->message('CreateUserResponse')
                ->part('userId', 'xsd:string')
                ->end();
            $wsdl->portType('UserPortType')
                ->operation('CreateUser', 'CreateUserRequest', 'CreateUserResponse')
                ->end();
            $wsdl->binding('UserBinding', 'UserPortType')
                ->httpBinding('POST')
                ->operation('CreateUser', '');

            // Add HTTP URL-encoded to the last operation
            $bindings = $wsdl->getBindings();
            $binding = $bindings['UserBinding'];
            $operations = $binding->getOperations();
            $lastOperation = end($operations);
            $lastOperation->setHttpOperation(HttpOperation::create('/users'));
            $lastOperation->setHttpUrlEncoded(HttpUrlEncoded::create());

            $generator = new \Cline\WsdlBuilder\WsdlGenerator($wsdl);
            $xml = $generator->generate();

            expect($xml)->toContain('http:urlEncoded');
        });

        test('generates WSDL with HTTP URL-replacement input', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->message('GetUserRequest')
                ->part('userId', 'xsd:string')
                ->end();
            $wsdl->message('GetUserResponse')
                ->part('user', 'xsd:string')
                ->end();
            $wsdl->portType('UserPortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse')
                ->end();
            $wsdl->binding('UserBinding', 'UserPortType')
                ->httpBinding('GET')
                ->operation('GetUser', '');

            // Add HTTP URL-replacement to the last operation
            $bindings = $wsdl->getBindings();
            $binding = $bindings['UserBinding'];
            $operations = $binding->getOperations();
            $lastOperation = end($operations);
            $lastOperation->setHttpOperation(HttpOperation::create('/users/(userId)'));
            $lastOperation->setHttpUrlReplacement(HttpUrlReplacement::create());

            $generator = new \Cline\WsdlBuilder\WsdlGenerator($wsdl);
            $xml = $generator->generate();

            expect($xml)->toContain('http:urlReplacement');
        });

        test('HTTP binding replaces SOAP binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->message('GetUserRequest')
                ->part('userId', 'xsd:string')
                ->end()
                ->message('GetUserResponse')
                ->part('user', 'xsd:string')
                ->end()
                ->portType('UserPortType')
                ->operation('GetUser', 'GetUserRequest', 'GetUserResponse')
                ->end()
                ->binding('UserBinding', 'UserPortType')
                ->httpBinding('GET')
                ->operation('GetUser', '')
                ->end();

            $generator = new \Cline\WsdlBuilder\WsdlGenerator($wsdl);
            $xml = $generator->generate();

            // Should have HTTP binding, not SOAP binding
            expect($xml)->toContain('http:binding')
                ->and($xml)->toContain('verb="GET"')
                ->and($xml)->not->toContain('<soap:binding');
        });
    });
});
