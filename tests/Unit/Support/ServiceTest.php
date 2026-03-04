<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Core\Port;
use Cline\WsdlBuilder\Wsdl;

describe('Service', function (): void {
    describe('Happy Paths', function (): void {
        test('creates service with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $service = $wsdl->service('UserService');

            expect($service->getName())->toBe('UserService');
        });

        test('adds port with name, binding, and address', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $service = $wsdl->service('UserService')
                ->port('UserServicePort', 'UserServiceBinding', 'http://example.com/soap/user');

            $ports = $service->getPorts();

            expect($ports)->toHaveCount(1)
                ->and($ports['UserServicePort']->name)->toBe('UserServicePort')
                ->and($ports['UserServicePort']->binding)->toBe('UserServiceBinding')
                ->and($ports['UserServicePort']->address)->toBe('http://example.com/soap/user');
        });

        test('adds multiple ports', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $service = $wsdl->service('MultiService')
                ->port('ServicePort1', 'Binding1', 'http://example.com/soap1')
                ->port('ServicePort2', 'Binding2', 'http://example.com/soap2');

            expect($service->getPorts())->toHaveCount(2);
        });

        test('end returns parent wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->service('TestService')->end();

            expect($result)->toBe($wsdl);
        });

        test('fluent interface chains ports', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $result = $wsdl->service('CompleteService')
                ->port('Port1', 'Binding1', 'http://example.com/1')
                ->port('Port2', 'Binding2', 'http://example.com/2')
                ->port('Port3', 'Binding3', 'http://example.com/3')
                ->end();

            expect($result)->toBe($wsdl);

            $service = $wsdl->getServices()['CompleteService'];

            expect($service->getPorts())->toHaveCount(3);
        });
    });
});

describe('Port', function (): void {
    test('creates readonly port', function (): void {
        $port = new Port('TestPort', 'TestBinding', 'http://example.com/soap');

        expect($port->name)->toBe('TestPort')
            ->and($port->binding)->toBe('TestBinding')
            ->and($port->address)->toBe('http://example.com/soap');
    });
});
