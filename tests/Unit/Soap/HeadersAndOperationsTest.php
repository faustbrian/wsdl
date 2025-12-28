<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Operations\Notification;
use Cline\WsdlBuilder\Operations\OneWay;
use Cline\WsdlBuilder\Soap\Header;
use Cline\WsdlBuilder\Soap\HeaderFault;
use Cline\WsdlBuilder\Wsdl;

describe('Header', function (): void {
    describe('Happy Paths', function (): void {
        test('creates header with message and part', function (): void {
            $header = new Header('AuthHeader', 'credentials');

            expect($header->getMessage())->toBe('AuthHeader')
                ->and($header->getPart())->toBe('credentials');
        });

        test('defaults to literal use', function (): void {
            $header = new Header('TestHeader', 'testPart');

            expect($header->getUse())->toBe(BindingUse::Literal);
        });

        test('sets custom binding use', function (): void {
            $header = new Header('TestHeader', 'testPart');
            $result = $header->use(BindingUse::Encoded);

            expect($result)->toBe($header)
                ->and($header->getUse())->toBe(BindingUse::Encoded);
        });

        test('sets namespace for header', function (): void {
            $header = new Header('TestHeader', 'testPart');
            $result = $header->namespace('http://test.example.com/auth');

            expect($result)->toBe($header)
                ->and($header->getNamespace())->toBe('http://test.example.com/auth');
        });

        test('sets encoding style for header', function (): void {
            $header = new Header('TestHeader', 'testPart');
            $result = $header->encodingStyle('http://schemas.xmlsoap.org/soap/encoding/');

            expect($result)->toBe($header)
                ->and($header->getEncodingStyle())->toBe('http://schemas.xmlsoap.org/soap/encoding/');
        });

        test('marks header as required', function (): void {
            $header = new Header('TestHeader', 'testPart');
            $result = $header->required(true);

            expect($result)->toBe($header)
                ->and($header->isRequired())->toBeTrue();
        });

        test('marks header as not required by default', function (): void {
            $header = new Header('TestHeader', 'testPart');

            expect($header->isRequired())->toBeFalse();
        });

        test('chains multiple configuration methods fluently', function (): void {
            $header = new Header('AuthHeader', 'credentials');
            $result = $header
                ->use(BindingUse::Encoded)
                ->namespace('http://test.example.com/auth')
                ->encodingStyle('http://schemas.xmlsoap.org/soap/encoding/')
                ->required(true);

            expect($result)->toBe($header)
                ->and($header->getUse())->toBe(BindingUse::Encoded)
                ->and($header->getNamespace())->toBe('http://test.example.com/auth')
                ->and($header->getEncodingStyle())->toBe('http://schemas.xmlsoap.org/soap/encoding/')
                ->and($header->isRequired())->toBeTrue();
        });

        test('converts header to array with all fields set', function (): void {
            $header = new Header('AuthHeader', 'credentials');
            $header
                ->use(BindingUse::Encoded)
                ->namespace('http://test.example.com/auth')
                ->encodingStyle('http://schemas.xmlsoap.org/soap/encoding/')
                ->required(true);

            $array = $header->toArray();

            expect($array)->toBe([
                'message' => 'AuthHeader',
                'part' => 'credentials',
                'use' => 'encoded',
                'encodingStyle' => 'http://schemas.xmlsoap.org/soap/encoding/',
                'namespace' => 'http://test.example.com/auth',
                'required' => true,
            ]);
        });

        test('converts header to array with minimal fields', function (): void {
            $header = new Header('SimpleHeader', 'simplePart');

            $array = $header->toArray();

            expect($array)->toBe([
                'message' => 'SimpleHeader',
                'part' => 'simplePart',
                'use' => 'literal',
                'encodingStyle' => null,
                'namespace' => null,
                'required' => false,
            ]);
        });
    });

    describe('Edge Cases', function (): void {
        test('converts header to array with literal use enum value', function (): void {
            $header = new Header('TestHeader', 'testPart');

            $array = $header->toArray();

            expect($array['use'])->toBe('literal')
                ->and($array['use'])->toBeString();
        });

        test('converts header to array with encoded use enum value', function (): void {
            $header = new Header('TestHeader', 'testPart');
            $header->use(BindingUse::Encoded);

            $array = $header->toArray();

            expect($array['use'])->toBe('encoded')
                ->and($array['use'])->toBeString();
        });

        test('handles empty string namespace', function (): void {
            $header = new Header('TestHeader', 'testPart');
            $header->namespace('');

            expect($header->getNamespace())->toBe('');
        });

        test('handles empty string encoding style', function (): void {
            $header = new Header('TestHeader', 'testPart');
            $header->encodingStyle('');

            expect($header->getEncodingStyle())->toBe('');
        });

        test('toggles required flag multiple times', function (): void {
            $header = new Header('TestHeader', 'testPart');

            $header->required(true);

            expect($header->isRequired())->toBeTrue();

            $header->required(false);
            expect($header->isRequired())->toBeFalse();

            $header->required(true);
            expect($header->isRequired())->toBeTrue();
        });

        test('returns null for namespace when not set', function (): void {
            $header = new Header('TestHeader', 'testPart');

            expect($header->getNamespace())->toBeNull();
        });

        test('returns null for encoding style when not set', function (): void {
            $header = new Header('TestHeader', 'testPart');

            expect($header->getEncodingStyle())->toBeNull();
        });
    });
});

describe('HeaderFault', function (): void {
    describe('Happy Paths', function (): void {
        test('creates header fault with message and part', function (): void {
            $fault = new HeaderFault('AuthFault', 'faultDetail');

            expect($fault->getMessage())->toBe('AuthFault')
                ->and($fault->getPart())->toBe('faultDetail');
        });

        test('defaults to literal use', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');

            expect($fault->getUse())->toBe(BindingUse::Literal);
        });

        test('sets custom binding use', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');
            $result = $fault->use(BindingUse::Encoded);

            expect($result)->toBe($fault)
                ->and($fault->getUse())->toBe(BindingUse::Encoded);
        });

        test('sets namespace for header fault', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');
            $result = $fault->namespace('http://test.example.com/faults');

            expect($result)->toBe($fault)
                ->and($fault->getNamespace())->toBe('http://test.example.com/faults');
        });

        test('sets encoding style for header fault', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');
            $result = $fault->encodingStyle('http://schemas.xmlsoap.org/soap/encoding/');

            expect($result)->toBe($fault)
                ->and($fault->getEncodingStyle())->toBe('http://schemas.xmlsoap.org/soap/encoding/');
        });

        test('marks header fault as required', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');
            $result = $fault->required(true);

            expect($result)->toBe($fault)
                ->and($fault->isRequired())->toBeTrue();
        });

        test('marks header fault as not required by default', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');

            expect($fault->isRequired())->toBeFalse();
        });

        test('chains multiple configuration methods fluently', function (): void {
            $fault = new HeaderFault('AuthFault', 'faultDetail');
            $result = $fault
                ->use(BindingUse::Encoded)
                ->namespace('http://test.example.com/faults')
                ->encodingStyle('http://schemas.xmlsoap.org/soap/encoding/')
                ->required(true);

            expect($result)->toBe($fault)
                ->and($fault->getUse())->toBe(BindingUse::Encoded)
                ->and($fault->getNamespace())->toBe('http://test.example.com/faults')
                ->and($fault->getEncodingStyle())->toBe('http://schemas.xmlsoap.org/soap/encoding/')
                ->and($fault->isRequired())->toBeTrue();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles empty string namespace', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');
            $fault->namespace('');

            expect($fault->getNamespace())->toBe('');
        });

        test('handles empty string encoding style', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');
            $fault->encodingStyle('');

            expect($fault->getEncodingStyle())->toBe('');
        });

        test('toggles required flag multiple times', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');

            $fault->required(true);

            expect($fault->isRequired())->toBeTrue();

            $fault->required(false);
            expect($fault->isRequired())->toBeFalse();

            $fault->required(true);
            expect($fault->isRequired())->toBeTrue();
        });

        test('returns null for namespace when not set', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');

            expect($fault->getNamespace())->toBeNull();
        });

        test('returns null for encoding style when not set', function (): void {
            $fault = new HeaderFault('TestFault', 'testPart');

            expect($fault->getEncodingStyle())->toBeNull();
        });
    });
});

describe('OneWay', function (): void {
    describe('Happy Paths', function (): void {
        test('creates one-way operation with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'SendNotification');

            expect($oneWay->getName())->toBe('SendNotification');
        });

        test('starts with empty inputs array', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'SendNotification');

            expect($oneWay->getInputs())->toBeEmpty();
        });

        test('adds input parameter with XSD type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'SendNotification');
            $result = $oneWay->input('message', XsdType::String);

            expect($result)->toBe($oneWay)
                ->and($oneWay->getInputs())->toHaveCount(1)
                ->and($oneWay->getInputs()[0]['name'])->toBe('message')
                ->and($oneWay->getInputs()[0]['type'])->toBe('string');
        });

        test('adds input parameter with custom type string', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'SendNotification');
            $result = $oneWay->input('payload', 'tns:CustomPayload');

            expect($result)->toBe($oneWay)
                ->and($oneWay->getInputs())->toHaveCount(1)
                ->and($oneWay->getInputs()[0]['name'])->toBe('payload')
                ->and($oneWay->getInputs()[0]['type'])->toBe('tns:CustomPayload');
        });

        test('adds multiple input parameters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = $wsdl->oneWay('SendEmail')
                ->input('to', XsdType::String)
                ->input('subject', XsdType::String)
                ->input('body', XsdType::String);

            expect($oneWay->getInputs())->toHaveCount(3)
                ->and($oneWay->getInputs()[0]['name'])->toBe('to')
                ->and($oneWay->getInputs()[1]['name'])->toBe('subject')
                ->and($oneWay->getInputs()[2]['name'])->toBe('body');
        });

        test('sets custom SOAP action', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'SendNotification');
            $result = $oneWay->soapAction('http://test.example.com/custom/action');

            expect($result)->toBe($oneWay)
                ->and($oneWay->getSoapAction())->toBe('http://test.example.com/custom/action');
        });

        test('returns null for SOAP action when not set', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'SendNotification');

            expect($oneWay->getSoapAction())->toBeNull();
        });

        test('builds one-way operation with end method returning wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->oneWay('SendNotification')
                ->input('message', XsdType::String)
                ->end();

            expect($result)->toBe($wsdl);
        });

        test('creates request message and complex type via fluent API', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->oneWay('SendEmail')
                ->input('to', XsdType::String)
                ->input('subject', XsdType::String)
                ->end();

            $messages = $wsdl->getMessages();
            $complexTypes = $wsdl->getComplexTypes();

            expect($messages)->toHaveKey('SendEmailInput')
                ->and($complexTypes)->toHaveKey('SendEmailRequest');
        });

        test('creates default port type automatically if not exists', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->oneWay('SendNotification')
                ->input('message', XsdType::String)
                ->end();

            $portTypes = $wsdl->getPortTypes();

            expect($portTypes)->toHaveKey('TestServicePortType');
        });

        test('creates default binding automatically if not exists', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->oneWay('SendNotification')
                ->input('message', XsdType::String)
                ->end();

            $bindings = $wsdl->getBindings();

            expect($bindings)->toHaveKey('TestServiceBinding');
        });

        test('uses custom SOAP action when building operation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->oneWay('SendNotification')
                ->input('message', XsdType::String)
                ->soapAction('http://custom.example.com/notify')
                ->end();

            $bindings = $wsdl->getBindings();
            $operations = $bindings['TestServiceBinding']->getOperations();

            expect($operations['SendNotification']->soapAction)->toBe('http://custom.example.com/notify');
        });

        test('generates default SOAP action from namespace when not provided', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->oneWay('SendNotification')
                ->input('message', XsdType::String)
                ->end();

            $bindings = $wsdl->getBindings();
            $operations = $bindings['TestServiceBinding']->getOperations();

            expect($operations['SendNotification']->soapAction)->toBe('http://test.example.com//SendNotification');
        });
    });

    describe('Edge Cases', function (): void {
        test('builds one-way operation with no input parameters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->oneWay('Ping')->end();

            expect($result)->toBe($wsdl)
                ->and($wsdl->getMessages())->toHaveKey('PingInput');
        });

        test('handles operation name with special characters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'Send-Notification_v2');

            expect($oneWay->getName())->toBe('Send-Notification_v2');
        });

        test('overwrites SOAP action when set multiple times', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $oneWay = new OneWay($wsdl, 'SendNotification');

            $oneWay->soapAction('http://first.example.com/action');
            $oneWay->soapAction('http://second.example.com/action');

            expect($oneWay->getSoapAction())->toBe('http://second.example.com/action');
        });

        test('uses existing port type when already created', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->portType('TestServicePortType');

            $wsdl->oneWay('SendNotification')
                ->input('message', XsdType::String)
                ->end();

            $portTypes = $wsdl->getPortTypes();

            expect($portTypes)->toHaveCount(1)
                ->and($portTypes)->toHaveKey('TestServicePortType');
        });

        test('uses existing binding when already created', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->portType('TestServicePortType');
            $wsdl->binding('TestServiceBinding', 'TestServicePortType');

            $wsdl->oneWay('SendNotification')
                ->input('message', XsdType::String)
                ->end();

            $bindings = $wsdl->getBindings();

            expect($bindings)->toHaveCount(1)
                ->and($bindings)->toHaveKey('TestServiceBinding');
        });
    });
});

describe('Notification', function (): void {
    describe('Happy Paths', function (): void {
        test('creates notification operation with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'AlertReceived');

            expect($notification->getName())->toBe('AlertReceived');
        });

        test('starts with empty outputs array', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'AlertReceived');

            expect($notification->getOutputs())->toBeEmpty();
        });

        test('adds output parameter with XSD type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'AlertReceived');
            $result = $notification->output('alertMessage', XsdType::String);

            expect($result)->toBe($notification)
                ->and($notification->getOutputs())->toHaveCount(1)
                ->and($notification->getOutputs()[0]['name'])->toBe('alertMessage')
                ->and($notification->getOutputs()[0]['type'])->toBe('string');
        });

        test('adds output parameter with custom type string', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'AlertReceived');
            $result = $notification->output('alert', 'tns:AlertData');

            expect($result)->toBe($notification)
                ->and($notification->getOutputs())->toHaveCount(1)
                ->and($notification->getOutputs()[0]['name'])->toBe('alert')
                ->and($notification->getOutputs()[0]['type'])->toBe('tns:AlertData');
        });

        test('adds multiple output parameters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = $wsdl->notification('SystemStatus')
                ->output('status', XsdType::String)
                ->output('timestamp', XsdType::DateTime)
                ->output('details', XsdType::String);

            expect($notification->getOutputs())->toHaveCount(3)
                ->and($notification->getOutputs()[0]['name'])->toBe('status')
                ->and($notification->getOutputs()[1]['name'])->toBe('timestamp')
                ->and($notification->getOutputs()[2]['name'])->toBe('details');
        });

        test('sets custom SOAP action', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'AlertReceived');
            $result = $notification->soapAction('http://test.example.com/custom/alert');

            expect($result)->toBe($notification)
                ->and($notification->getSoapAction())->toBe('http://test.example.com/custom/alert');
        });

        test('returns null for SOAP action when not set', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'AlertReceived');

            expect($notification->getSoapAction())->toBeNull();
        });

        test('builds notification operation with end method returning wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->notification('AlertReceived')
                ->output('message', XsdType::String)
                ->end();

            expect($result)->toBe($wsdl);
        });

        test('creates response message and complex type via fluent API', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->notification('SystemStatus')
                ->output('status', XsdType::String)
                ->output('uptime', XsdType::Int)
                ->end();

            $messages = $wsdl->getMessages();
            $complexTypes = $wsdl->getComplexTypes();

            expect($messages)->toHaveKey('SystemStatusOutput')
                ->and($complexTypes)->toHaveKey('SystemStatusResponse');
        });

        test('creates default port type automatically if not exists', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->notification('AlertReceived')
                ->output('message', XsdType::String)
                ->end();

            $portTypes = $wsdl->getPortTypes();

            expect($portTypes)->toHaveKey('TestServicePortType');
        });

        test('creates default binding automatically if not exists', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->notification('AlertReceived')
                ->output('message', XsdType::String)
                ->end();

            $bindings = $wsdl->getBindings();

            expect($bindings)->toHaveKey('TestServiceBinding');
        });

        test('uses custom SOAP action when building operation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->notification('AlertReceived')
                ->output('message', XsdType::String)
                ->soapAction('http://custom.example.com/alert')
                ->end();

            $bindings = $wsdl->getBindings();
            $operations = $bindings['TestServiceBinding']->getOperations();

            expect($operations['AlertReceived']->soapAction)->toBe('http://custom.example.com/alert');
        });

        test('generates default SOAP action from namespace when not provided', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->notification('AlertReceived')
                ->output('message', XsdType::String)
                ->end();

            $bindings = $wsdl->getBindings();
            $operations = $bindings['TestServiceBinding']->getOperations();

            expect($operations['AlertReceived']->soapAction)->toBe('http://test.example.com//AlertReceived');
        });
    });

    describe('Edge Cases', function (): void {
        test('builds notification operation with no output parameters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->notification('KeepAlive')->end();

            expect($result)->toBe($wsdl)
                ->and($wsdl->getMessages())->toHaveKey('KeepAliveOutput');
        });

        test('handles operation name with special characters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'Alert-Received_v2');

            expect($notification->getName())->toBe('Alert-Received_v2');
        });

        test('overwrites SOAP action when set multiple times', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $notification = new Notification($wsdl, 'AlertReceived');

            $notification->soapAction('http://first.example.com/alert');
            $notification->soapAction('http://second.example.com/alert');

            expect($notification->getSoapAction())->toBe('http://second.example.com/alert');
        });

        test('uses existing port type when already created', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->portType('TestServicePortType');

            $wsdl->notification('AlertReceived')
                ->output('message', XsdType::String)
                ->end();

            $portTypes = $wsdl->getPortTypes();

            expect($portTypes)->toHaveCount(1)
                ->and($portTypes)->toHaveKey('TestServicePortType');
        });

        test('uses existing binding when already created', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->portType('TestServicePortType');
            $wsdl->binding('TestServiceBinding', 'TestServicePortType');

            $wsdl->notification('AlertReceived')
                ->output('message', XsdType::String)
                ->end();

            $bindings = $wsdl->getBindings();

            expect($bindings)->toHaveCount(1)
                ->and($bindings)->toHaveKey('TestServiceBinding');
        });
    });
});
