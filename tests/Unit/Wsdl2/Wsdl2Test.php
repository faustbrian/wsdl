<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;
use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Imports\SchemaImport;
use Cline\WsdlBuilder\Imports\SchemaInclude;
use Cline\WsdlBuilder\Wsdl2\Binding2;
use Cline\WsdlBuilder\Wsdl2\BindingFault2;
use Cline\WsdlBuilder\Wsdl2\BindingOperation2;
use Cline\WsdlBuilder\Wsdl2\Endpoint;
use Cline\WsdlBuilder\Wsdl2\Enums\MessageExchangePattern;
use Cline\WsdlBuilder\Wsdl2\Interface_;
use Cline\WsdlBuilder\Wsdl2\InterfaceFault;
use Cline\WsdlBuilder\Wsdl2\InterfaceOperation;
use Cline\WsdlBuilder\Wsdl2\Service2;
use Cline\WsdlBuilder\Wsdl2\Wsdl2;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;
use Cline\WsdlBuilder\Xsd\DerivedTypes\ListType;
use Cline\WsdlBuilder\Xsd\DerivedTypes\UnionType;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;

describe('Wsdl2', function (): void {
    describe('Happy Paths', function (): void {
        test('creates a new WSDL 2.0 builder with name and target namespace', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('UserService', 'http://example.com/userservice');

            // Assert
            expect($wsdl)
                ->toBeInstanceOf(Wsdl2::class)
                ->and($wsdl->getName())->toBe('UserService')
                ->and($wsdl->getTargetNamespace())->toBe('http://example.com/userservice');
        });

        test('creates simple type and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $type = $wsdl->simpleType('PhoneNumber');

            // Assert
            expect($type)
                ->toBeInstanceOf(SimpleType::class)
                ->and($wsdl->getSimpleTypes())->toHaveKey('PhoneNumber')
                ->and($wsdl->getSimpleTypes()['PhoneNumber'])->toBe($type);
        });

        test('creates complex type and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $type = $wsdl->complexType('Person');

            // Assert
            expect($type)
                ->toBeInstanceOf(ComplexType::class)
                ->and($wsdl->getComplexTypes())->toHaveKey('Person')
                ->and($wsdl->getComplexTypes()['Person'])->toBe($type);
        });

        test('creates element group and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $group = $wsdl->elementGroup('AddressGroup');

            // Assert
            expect($group)
                ->toBeInstanceOf(ElementGroup::class)
                ->and($wsdl->getElementGroups())->toHaveKey('AddressGroup')
                ->and($wsdl->getElementGroups()['AddressGroup'])->toBe($group);
        });

        test('creates attribute group and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $group = $wsdl->attributeGroup('CommonAttributes');

            // Assert
            expect($group)
                ->toBeInstanceOf(AttributeGroup::class)
                ->and($wsdl->getAttributeGroups())->toHaveKey('CommonAttributes')
                ->and($wsdl->getAttributeGroups()['CommonAttributes'])->toBe($group);
        });

        test('creates list type and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $type = $wsdl->listType('IntegerList');

            // Assert
            expect($type)
                ->toBeInstanceOf(ListType::class)
                ->and($wsdl->getListTypes())->toHaveKey('IntegerList')
                ->and($wsdl->getListTypes()['IntegerList'])->toBe($type);
        });

        test('creates union type and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $type = $wsdl->unionType('NumberOrString');

            // Assert
            expect($type)
                ->toBeInstanceOf(UnionType::class)
                ->and($wsdl->getUnionTypes())->toHaveKey('NumberOrString')
                ->and($wsdl->getUnionTypes()['NumberOrString'])->toBe($type);
        });

        test('creates interface and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $interface = $wsdl->interface('UserInterface');

            // Assert
            expect($interface)
                ->toBeInstanceOf(Interface_::class)
                ->and($wsdl->getInterfaces())->toHaveKey('UserInterface')
                ->and($wsdl->getInterfaces()['UserInterface'])->toBe($interface);
        });

        test('creates binding with interface reference and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Assert
            expect($binding)
                ->toBeInstanceOf(Binding2::class)
                ->and($binding->getName())->toBe('UserBinding')
                ->and($binding->getInterfaceRef())->toBe('UserInterface')
                ->and($wsdl->getBindings())->toHaveKey('UserBinding')
                ->and($wsdl->getBindings()['UserBinding'])->toBe($binding);
        });

        test('creates service and stores in collection', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $service = $wsdl->service('UserService');

            // Assert
            expect($service)
                ->toBeInstanceOf(Service2::class)
                ->and($wsdl->getServices())->toHaveKey('UserService')
                ->and($wsdl->getServices()['UserService'])->toBe($service);
        });

        test('adds schema import with namespace and location', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $result = $wsdl->schemaImport('http://schemas.example.com/types', 'types.xsd');

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getSchemaImports())->toHaveCount(1)
                ->and($wsdl->getSchemaImports()[0])->toBeInstanceOf(SchemaImport::class);
        });

        test('adds schema include with location', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $result = $wsdl->schemaInclude('common.xsd');

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getSchemaIncludes())->toHaveCount(1)
                ->and($wsdl->getSchemaIncludes()[0])->toBeInstanceOf(SchemaInclude::class);
        });

        test('adds documentation at WSDL level with content and language', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $result = $wsdl->documentation('This is a user service', 'en', 'http://example.com/docs');

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($wsdl->getDocumentation()->content)->toBe('This is a user service');
        });

        test('supports fluent interface with method chaining', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('Service', 'http://example.com/')
                ->simpleType('PhoneNumber')
                ->base(XsdType::String)
                ->pattern('[0-9]{10}')
                ->end()
                ->complexType('Person')
                ->element('name', XsdType::String)
                ->end()
                ->interface('UserInterface')
                ->end()
                ->binding('UserBinding', 'UserInterface')
                ->end()
                ->service('UserService')
                ->end();

            // Assert
            expect($wsdl)->toBeInstanceOf(Wsdl2::class)
                ->and($wsdl->getSimpleTypes())->toHaveKey('PhoneNumber')
                ->and($wsdl->getComplexTypes())->toHaveKey('Person')
                ->and($wsdl->getInterfaces())->toHaveKey('UserInterface')
                ->and($wsdl->getBindings())->toHaveKey('UserBinding')
                ->and($wsdl->getServices())->toHaveKey('UserService');
        });
    });

    describe('Namespace Constants', function (): void {
        test('has correct WSDL 2.0 namespace', function (): void {
            // Assert
            expect(Wsdl2::WSDL_NS)->toBe('http://www.w3.org/ns/wsdl');
        });

        test('has correct XSD namespace', function (): void {
            // Assert
            expect(Wsdl2::XSD_NS)->toBe('http://www.w3.org/2001/XMLSchema');
        });

        test('has correct SOAP 1.2 namespace for WSDL 2.0', function (): void {
            // Assert
            expect(Wsdl2::SOAP_NS)->toBe('http://www.w3.org/ns/wsdl/soap');
        });

        test('has correct HTTP namespace', function (): void {
            // Assert
            expect(Wsdl2::HTTP_NS)->toBe('http://www.w3.org/ns/wsdl/http');
        });

        test('has correct SOAP HTTP binding URI', function (): void {
            // Assert
            expect(Wsdl2::SOAP_HTTP_BINDING)->toBe('http://www.w3.org/2003/05/soap/bindings/HTTP/');
        });
    });

    describe('Edge Cases', function (): void {
        test('handles multiple schema imports without conflict', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $wsdl->schemaImport('http://schemas.example.com/types1', 'types1.xsd')
                ->schemaImport('http://schemas.example.com/types2', 'types2.xsd')
                ->schemaImport('http://schemas.example.com/types3', 'types3.xsd');

            // Assert
            expect($wsdl->getSchemaImports())->toHaveCount(3);
        });

        test('handles multiple schema includes without conflict', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $wsdl->schemaInclude('common1.xsd')
                ->schemaInclude('common2.xsd');

            // Assert
            expect($wsdl->getSchemaIncludes())->toHaveCount(2);
        });

        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Assert
            expect($wsdl->getDocumentation())->toBeNull();
        });

        test('replaces documentation when called multiple times', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/')
                ->documentation('First documentation');

            // Act
            $wsdl->documentation('Second documentation');

            // Assert
            expect($wsdl->getDocumentation()->content)->toBe('Second documentation');
        });

        test('build method generates XML string via Wsdl2Generator', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toBeString()
                ->and($xml)->not->toBeEmpty();
        });

        test('buildDom method generates DOMDocument via Wsdl2Generator', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $dom = $wsdl->buildDom();

            // Assert
            expect($dom)->toBeInstanceOf(DOMDocument::class);
        });
    });
});

describe('Interface_', function (): void {
    describe('Happy Paths', function (): void {
        test('creates interface with name via fluent API', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $interface = $wsdl->interface('UserInterface');

            // Assert
            expect($interface)
                ->toBeInstanceOf(Interface_::class)
                ->and($interface->getName())->toBe('UserInterface');
        });

        test('adds interface inheritance via extends method', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $interface->extends('BaseInterface');

            // Assert
            expect($interface->getExtends())
                ->toHaveCount(1)
                ->and($interface->getExtends()[0])->toBe('BaseInterface');
        });

        test('adds multiple interface inheritance via extends method', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $interface->extends('BaseInterface')
                ->extends('SecondInterface')
                ->extends('ThirdInterface');

            // Assert
            expect($interface->getExtends())->toHaveCount(3)
                ->and($interface->getExtends())->toBe(['BaseInterface', 'SecondInterface', 'ThirdInterface']);
        });

        test('adds interface-level fault with name and element reference', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $interface->fault('InvalidUserFault', 'tns:InvalidUserError');

            // Assert
            expect($interface->getFaults())->toHaveKey('InvalidUserFault')
                ->and($interface->getFaults()['InvalidUserFault'])->toBeInstanceOf(InterfaceFault::class)
                ->and($interface->getFaults()['InvalidUserFault']->name)->toBe('InvalidUserFault')
                ->and($interface->getFaults()['InvalidUserFault']->element)->toBe('tns:InvalidUserError');
        });

        test('adds operation to interface and returns InterfaceOperation instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $operation = $interface->operation('GetUser');

            // Assert
            expect($operation)
                ->toBeInstanceOf(InterfaceOperation::class)
                ->and($operation->getName())->toBe('GetUser')
                ->and($interface->getOperations())->toHaveKey('GetUser');
        });

        test('adds documentation to interface with content and language', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $interface->documentation('User management interface', 'en');

            // Assert
            expect($interface->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($interface->getDocumentation()->content)->toBe('User management interface');
        });

        test('end method returns parent WSDL builder instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $result = $interface->end();

            // Assert
            expect($result)->toBe($wsdl);
        });

        test('supports fluent interface for building complete interface definition', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $wsdl->interface('UserInterface')
                ->extends('BaseInterface')
                ->fault('InvalidUserFault', 'tns:InvalidUserError')
                ->operation('GetUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('tns:GetUserRequest')
                ->output('tns:GetUserResponse')
                ->end()
                ->documentation('User management interface')
                ->end();

            // Assert
            expect($wsdl->getInterfaces())->toHaveKey('UserInterface')
                ->and($wsdl->getInterfaces()['UserInterface']->getExtends())->toContain('BaseInterface')
                ->and($wsdl->getInterfaces()['UserInterface']->getFaults())->toHaveKey('InvalidUserFault')
                ->and($wsdl->getInterfaces()['UserInterface']->getOperations())->toHaveKey('GetUser');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns empty array when no inheritance is defined', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Assert
            expect($interface->getExtends())->toBeEmpty();
        });

        test('returns empty array when no faults are defined', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Assert
            expect($interface->getFaults())->toBeEmpty();
        });

        test('returns empty array when no operations are defined', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Assert
            expect($interface->getOperations())->toBeEmpty();
        });

        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Assert
            expect($interface->getDocumentation())->toBeNull();
        });
    });
});

describe('InterfaceOperation', function (): void {
    describe('Happy Paths', function (): void {
        test('creates operation with name via interface', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $operation = $interface->operation('GetUser');

            // Assert
            expect($operation)
                ->toBeInstanceOf(InterfaceOperation::class)
                ->and($operation->getName())->toBe('GetUser');
        });

        test('sets message exchange pattern URI', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->pattern(MessageExchangePattern::InOut->value);

            // Assert
            expect($operation->getPattern())->toBe('http://www.w3.org/ns/wsdl/in-out');
        });

        test('sets input element reference', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->input('tns:GetUserRequest');

            // Assert
            expect($operation->getInput())->toBe('tns:GetUserRequest');
        });

        test('sets output element reference', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->output('tns:GetUserResponse');

            // Assert
            expect($operation->getOutput())->toBe('tns:GetUserResponse');
        });

        test('adds fault reference to operation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->fault('InvalidUserFault');

            // Assert
            expect($operation->getFaults())
                ->toHaveCount(1)
                ->and($operation->getFaults()[0])->toBe('InvalidUserFault');
        });

        test('adds multiple fault references to operation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->fault('InvalidUserFault')
                ->fault('DatabaseFault')
                ->fault('TimeoutFault');

            // Assert
            expect($operation->getFaults())->toHaveCount(3)
                ->and($operation->getFaults())->toBe(['InvalidUserFault', 'DatabaseFault', 'TimeoutFault']);
        });

        test('sets operation style URI', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->style('http://www.w3.org/ns/wsdl/style/iri');

            // Assert
            expect($operation->getStyle())->toBe('http://www.w3.org/ns/wsdl/style/iri');
        });

        test('marks operation as safe with no side effects', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->safe(true);

            // Assert
            expect($operation->isSafe())->toBeTrue();
        });

        test('marks operation as not safe by default', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('CreateUser');

            // Assert
            expect($operation->isSafe())->toBeFalse();
        });

        test('adds documentation to operation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->documentation('Retrieve user by ID', 'en');

            // Assert
            expect($operation->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($operation->getDocumentation()->content)->toBe('Retrieve user by ID');
        });

        test('end method returns parent interface instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');
            $operation = $interface->operation('GetUser');

            // Act
            $result = $operation->end();

            // Assert
            expect($result)->toBe($interface);
        });

        test('supports fluent interface for building complete operation definition', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $interface = $wsdl->interface('UserInterface');

            // Act
            $interface->operation('GetUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('tns:GetUserRequest')
                ->output('tns:GetUserResponse')
                ->fault('InvalidUserFault')
                ->fault('DatabaseFault')
                ->style('http://www.w3.org/ns/wsdl/style/iri')
                ->safe(true)
                ->documentation('Retrieve user by ID')
                ->end();

            // Assert
            $operation = $interface->getOperations()['GetUser'];
            expect($operation->getPattern())->toBe('http://www.w3.org/ns/wsdl/in-out')
                ->and($operation->getInput())->toBe('tns:GetUserRequest')
                ->and($operation->getOutput())->toBe('tns:GetUserResponse')
                ->and($operation->getFaults())->toHaveCount(2)
                ->and($operation->getStyle())->toBe('http://www.w3.org/ns/wsdl/style/iri')
                ->and($operation->isSafe())->toBeTrue();
        });
    });

    describe('Edge Cases', function (): void {
        test('returns null pattern when none is set', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getPattern())->toBeNull();
        });

        test('returns null input when none is set', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getInput())->toBeNull();
        });

        test('returns null output when none is set', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getOutput())->toBeNull();
        });

        test('returns empty array when no faults are added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getFaults())->toBeEmpty();
        });

        test('returns null style when none is set', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getStyle())->toBeNull();
        });

        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getDocumentation())->toBeNull();
        });

        test('allows setting safe to false explicitly', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->safe(true)->safe(false);

            // Assert
            expect($operation->isSafe())->toBeFalse();
        });
    });
});

describe('Binding2', function (): void {
    describe('Happy Paths', function (): void {
        test('creates binding with name and interface reference via fluent API', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Assert
            expect($binding)
                ->toBeInstanceOf(Binding2::class)
                ->and($binding->getName())->toBe('UserBinding')
                ->and($binding->getInterfaceRef())->toBe('UserInterface');
        });

        test('sets binding type URI for SOAP', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Act
            $binding->type('http://www.w3.org/ns/wsdl/soap');

            // Assert
            expect($binding->getType())->toBe('http://www.w3.org/ns/wsdl/soap');
        });

        test('adds operation to binding and returns BindingOperation2 instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Act
            $operation = $binding->operation('GetUser');

            // Assert
            expect($operation)
                ->toBeInstanceOf(BindingOperation2::class)
                ->and($operation->getRef())->toBe('GetUser')
                ->and($binding->getOperations())->toHaveKey('GetUser');
        });

        test('adds fault to binding and returns BindingFault2 instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Act
            $fault = $binding->fault('InvalidUserFault');

            // Assert
            expect($fault)
                ->toBeInstanceOf(BindingFault2::class)
                ->and($fault->getRef())->toBe('InvalidUserFault')
                ->and($binding->getFaults())->toHaveKey('InvalidUserFault');
        });

        test('adds documentation to binding', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Act
            $binding->documentation('SOAP binding for user interface', 'en');

            // Assert
            expect($binding->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($binding->getDocumentation()->content)->toBe('SOAP binding for user interface');
        });

        test('end method returns parent WSDL builder instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Act
            $result = $binding->end();

            // Assert
            expect($result)->toBe($wsdl);
        });

        test('supports fluent interface for building complete binding definition', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');

            // Act
            $wsdl->binding('UserBinding', 'UserInterface')
                ->type('http://www.w3.org/ns/wsdl/soap')
                ->operation('GetUser')
                ->soapAction('http://example.com/GetUser')
                ->end()
                ->fault('InvalidUserFault')
                ->end()
                ->documentation('SOAP binding for user interface')
                ->end();

            // Assert
            $binding = $wsdl->getBindings()['UserBinding'];
            expect($binding->getType())->toBe('http://www.w3.org/ns/wsdl/soap')
                ->and($binding->getOperations())->toHaveKey('GetUser')
                ->and($binding->getFaults())->toHaveKey('InvalidUserFault');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns null type when none is set', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Assert
            expect($binding->getType())->toBeNull();
        });

        test('returns empty array when no operations are defined', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Assert
            expect($binding->getOperations())->toBeEmpty();
        });

        test('returns empty array when no faults are defined', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Assert
            expect($binding->getFaults())->toBeEmpty();
        });

        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Assert
            expect($binding->getDocumentation())->toBeNull();
        });
    });
});

describe('BindingOperation2', function (): void {
    describe('Happy Paths', function (): void {
        test('creates binding operation with reference via binding', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Act
            $operation = $binding->operation('GetUser');

            // Assert
            expect($operation)
                ->toBeInstanceOf(BindingOperation2::class)
                ->and($operation->getRef())->toBe('GetUser');
        });

        test('sets SOAP action URI', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->binding('UserBinding', 'UserInterface')->operation('GetUser');

            // Act
            $operation->soapAction('http://example.com/GetUser');

            // Assert
            expect($operation->getSoapAction())->toBe('http://example.com/GetUser');
        });

        test('adds documentation to binding operation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->binding('UserBinding', 'UserInterface')->operation('GetUser');

            // Act
            $operation->documentation('Get user operation binding', 'en');

            // Assert
            expect($operation->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($operation->getDocumentation()->content)->toBe('Get user operation binding');
        });

        test('end method returns parent binding instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');
            $operation = $binding->operation('GetUser');

            // Act
            $result = $operation->end();

            // Assert
            expect($result)->toBe($binding);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns null SOAP action when none is set', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->binding('UserBinding', 'UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getSoapAction())->toBeNull();
        });

        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->binding('UserBinding', 'UserInterface')->operation('GetUser');

            // Assert
            expect($operation->getDocumentation())->toBeNull();
        });
    });
});

describe('BindingFault2', function (): void {
    describe('Happy Paths', function (): void {
        test('creates binding fault with reference via binding', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');

            // Act
            $fault = $binding->fault('InvalidUserFault');

            // Assert
            expect($fault)
                ->toBeInstanceOf(BindingFault2::class)
                ->and($fault->getRef())->toBe('InvalidUserFault');
        });

        test('adds documentation to binding fault', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $fault = $wsdl->binding('UserBinding', 'UserInterface')->fault('InvalidUserFault');

            // Act
            $fault->documentation('Invalid user fault binding', 'en');

            // Assert
            expect($fault->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($fault->getDocumentation()->content)->toBe('Invalid user fault binding');
        });

        test('end method returns parent binding instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $binding = $wsdl->binding('UserBinding', 'UserInterface');
            $fault = $binding->fault('InvalidUserFault');

            // Act
            $result = $fault->end();

            // Assert
            expect($result)->toBe($binding);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $fault = $wsdl->binding('UserBinding', 'UserInterface')->fault('InvalidUserFault');

            // Assert
            expect($fault->getDocumentation())->toBeNull();
        });
    });
});

describe('Service2', function (): void {
    describe('Happy Paths', function (): void {
        test('creates service with name via fluent API', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');

            // Act
            $service = $wsdl->service('UserService');

            // Assert
            expect($service)
                ->toBeInstanceOf(Service2::class)
                ->and($service->getName())->toBe('UserService');
        });

        test('sets interface reference for service', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Act
            $service->interface('UserInterface');

            // Assert
            expect($service->getInterfaceRef())->toBe('UserInterface');
        });

        test('adds endpoint to service and returns Endpoint instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Act
            $endpoint = $service->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap');

            // Assert
            expect($endpoint)
                ->toBeInstanceOf(Endpoint::class)
                ->and($endpoint->getName())->toBe('UserEndpoint')
                ->and($endpoint->getBinding())->toBe('UserBinding')
                ->and($endpoint->getAddress())->toBe('http://example.com/soap')
                ->and($service->getEndpoints())->toHaveKey('UserEndpoint');
        });

        test('adds documentation to service', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Act
            $service->documentation('User management service', 'en');

            // Assert
            expect($service->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($service->getDocumentation()->content)->toBe('User management service');
        });

        test('end method returns parent WSDL builder instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Act
            $result = $service->end();

            // Assert
            expect($result)->toBe($wsdl);
        });

        test('supports fluent interface for building complete service definition', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');

            // Act
            $wsdl->service('UserService')
                ->interface('UserInterface')
                ->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap')
                ->end()
                ->documentation('User management service')
                ->end();

            // Assert
            $service = $wsdl->getServices()['UserService'];
            expect($service->getInterfaceRef())->toBe('UserInterface')
                ->and($service->getEndpoints())->toHaveKey('UserEndpoint');
        });
    });

    describe('Edge Cases', function (): void {
        test('returns null interface reference when none is set', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Assert
            expect($service->getInterfaceRef())->toBeNull();
        });

        test('returns empty array when no endpoints are defined', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Assert
            expect($service->getEndpoints())->toBeEmpty();
        });

        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Assert
            expect($service->getDocumentation())->toBeNull();
        });
    });
});

describe('Endpoint', function (): void {
    describe('Happy Paths', function (): void {
        test('creates endpoint with name, binding, and address via service', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Act
            $endpoint = $service->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap');

            // Assert
            expect($endpoint)
                ->toBeInstanceOf(Endpoint::class)
                ->and($endpoint->getName())->toBe('UserEndpoint')
                ->and($endpoint->getBinding())->toBe('UserBinding')
                ->and($endpoint->getAddress())->toBe('http://example.com/soap');
        });

        test('adds documentation to endpoint', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $endpoint = $wsdl->service('UserService')->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap');

            // Act
            $endpoint->documentation('Primary SOAP endpoint', 'en');

            // Assert
            expect($endpoint->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($endpoint->getDocumentation()->content)->toBe('Primary SOAP endpoint');
        });

        test('end method returns parent service instance', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');
            $endpoint = $service->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap');

            // Act
            $result = $endpoint->end();

            // Assert
            expect($result)->toBe($service);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns null documentation when none is added', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $endpoint = $wsdl->service('UserService')->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap');

            // Assert
            expect($endpoint->getDocumentation())->toBeNull();
        });

        test('handles empty string values for name, binding, and address', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $service = $wsdl->service('UserService');

            // Act
            $endpoint = $service->endpoint('', '', '');

            // Assert
            expect($endpoint->getName())->toBe('')
                ->and($endpoint->getBinding())->toBe('')
                ->and($endpoint->getAddress())->toBe('');
        });
    });
});

describe('MessageExchangePattern', function (): void {
    describe('Happy Paths', function (): void {
        test('has InOut pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::InOut->value)->toBe('http://www.w3.org/ns/wsdl/in-out');
        });

        test('has InOnly pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::InOnly->value)->toBe('http://www.w3.org/ns/wsdl/in-only');
        });

        test('has RobustInOnly pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::RobustInOnly->value)->toBe('http://www.w3.org/ns/wsdl/robust-in-only');
        });

        test('has OutOnly pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::OutOnly->value)->toBe('http://www.w3.org/ns/wsdl/out-only');
        });

        test('has OutIn pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::OutIn->value)->toBe('http://www.w3.org/ns/wsdl/out-in');
        });

        test('has OutOptionalIn pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::OutOptionalIn->value)->toBe('http://www.w3.org/ns/wsdl/out-opt-in');
        });

        test('has InOptionalOut pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::InOptionalOut->value)->toBe('http://www.w3.org/ns/wsdl/in-opt-out');
        });

        test('has RobustOutOnly pattern with correct URI', function (): void {
            // Assert
            expect(MessageExchangePattern::RobustOutOnly->value)->toBe('http://www.w3.org/ns/wsdl/robust-out-only');
        });

        test('can be used in operation pattern method', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $operation = $wsdl->interface('UserInterface')->operation('GetUser');

            // Act
            $operation->pattern(MessageExchangePattern::InOut->value);

            // Assert
            expect($operation->getPattern())->toBe(MessageExchangePattern::InOut->value);
        });
    });

    describe('Edge Cases', function (): void {
        test('enum has exactly 8 cases', function (): void {
            // Assert
            expect(MessageExchangePattern::cases())->toHaveCount(8);
        });

        test('all enum values start with WSDL namespace prefix', function (): void {
            // Arrange
            $prefix = 'http://www.w3.org/ns/wsdl/';

            // Act & Assert
            foreach (MessageExchangePattern::cases() as $pattern) {
                expect($pattern->value)->toStartWith($prefix);
            }
        });
    });
});

describe('Integration', function (): void {
    describe('Happy Paths', function (): void {
        test('builds complete WSDL 2.0 service definition via fluent API', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('UserService', 'http://example.com/userservice')
                ->documentation('Complete user management service')
                ->complexType('User')
                ->element('id', XsdType::Int)
                ->element('name', XsdType::String)
                ->element('email', XsdType::String)
                ->end()
                ->interface('UserInterface')
                ->extends('BaseInterface')
                ->fault('InvalidUserFault', 'tns:InvalidUserError')
                ->operation('GetUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('tns:GetUserRequest')
                ->output('tns:GetUserResponse')
                ->fault('InvalidUserFault')
                ->safe(true)
                ->documentation('Retrieve user by ID')
                ->end()
                ->operation('CreateUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('tns:CreateUserRequest')
                ->output('tns:CreateUserResponse')
                ->fault('InvalidUserFault')
                ->documentation('Create a new user')
                ->end()
                ->end()
                ->binding('UserBinding', 'UserInterface')
                ->type(Wsdl2::SOAP_NS)
                ->operation('GetUser')
                ->soapAction('http://example.com/GetUser')
                ->end()
                ->operation('CreateUser')
                ->soapAction('http://example.com/CreateUser')
                ->end()
                ->fault('InvalidUserFault')
                ->end()
                ->end()
                ->service('UserService')
                ->interface('UserInterface')
                ->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap')
                ->documentation('Primary SOAP endpoint')
                ->end()
                ->documentation('User management service')
                ->end();

            // Assert
            expect($wsdl->getName())->toBe('UserService')
                ->and($wsdl->getTargetNamespace())->toBe('http://example.com/userservice')
                ->and($wsdl->getComplexTypes())->toHaveKey('User')
                ->and($wsdl->getInterfaces())->toHaveKey('UserInterface')
                ->and($wsdl->getBindings())->toHaveKey('UserBinding')
                ->and($wsdl->getServices())->toHaveKey('UserService');

            $interface = $wsdl->getInterfaces()['UserInterface'];
            expect($interface->getExtends())->toContain('BaseInterface')
                ->and($interface->getFaults())->toHaveKey('InvalidUserFault')
                ->and($interface->getOperations())->toHaveKeys(['GetUser', 'CreateUser']);

            $getOperation = $interface->getOperations()['GetUser'];
            expect($getOperation->getPattern())->toBe(MessageExchangePattern::InOut->value)
                ->and($getOperation->getInput())->toBe('tns:GetUserRequest')
                ->and($getOperation->getOutput())->toBe('tns:GetUserResponse')
                ->and($getOperation->isSafe())->toBeTrue();

            $binding = $wsdl->getBindings()['UserBinding'];
            expect($binding->getInterfaceRef())->toBe('UserInterface')
                ->and($binding->getType())->toBe(Wsdl2::SOAP_NS)
                ->and($binding->getOperations())->toHaveKeys(['GetUser', 'CreateUser'])
                ->and($binding->getFaults())->toHaveKey('InvalidUserFault');

            $service = $wsdl->getServices()['UserService'];
            expect($service->getInterfaceRef())->toBe('UserInterface')
                ->and($service->getEndpoints())->toHaveKey('UserEndpoint');

            $endpoint = $service->getEndpoints()['UserEndpoint'];
            expect($endpoint->getBinding())->toBe('UserBinding')
                ->and($endpoint->getAddress())->toBe('http://example.com/soap');
        });

        test('builds WSDL 2.0 with multiple interfaces and services', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('MultiService', 'http://example.com/multiservice')
                ->interface('UserInterface')
                ->operation('GetUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->end()
                ->end()
                ->interface('OrderInterface')
                ->operation('GetOrder')
                ->pattern(MessageExchangePattern::InOut->value)
                ->end()
                ->end()
                ->binding('UserBinding', 'UserInterface')
                ->type(Wsdl2::SOAP_NS)
                ->end()
                ->binding('OrderBinding', 'OrderInterface')
                ->type(Wsdl2::SOAP_NS)
                ->end()
                ->service('UserService')
                ->interface('UserInterface')
                ->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/user')
                ->end()
                ->end()
                ->service('OrderService')
                ->interface('OrderInterface')
                ->endpoint('OrderEndpoint', 'OrderBinding', 'http://example.com/order')
                ->end()
                ->end();

            // Assert
            expect($wsdl->getInterfaces())->toHaveKeys(['UserInterface', 'OrderInterface'])
                ->and($wsdl->getBindings())->toHaveKeys(['UserBinding', 'OrderBinding'])
                ->and($wsdl->getServices())->toHaveKeys(['UserService', 'OrderService']);
        });

        test('builds WSDL 2.0 with schema imports and includes', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('Service', 'http://example.com/')
                ->schemaImport('http://schemas.example.com/types', 'types.xsd')
                ->schemaImport('http://schemas.example.com/common', 'common.xsd')
                ->schemaInclude('local.xsd')
                ->interface('UserInterface')
                ->operation('GetUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->end()
                ->end();

            // Assert
            expect($wsdl->getSchemaImports())->toHaveCount(2)
                ->and($wsdl->getSchemaIncludes())->toHaveCount(1);
        });
    });

    describe('Edge Cases', function (): void {
        test('builds minimal WSDL 2.0 with only name and namespace', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('MinimalService', 'http://example.com/minimal');

            // Assert
            expect($wsdl->getName())->toBe('MinimalService')
                ->and($wsdl->getTargetNamespace())->toBe('http://example.com/minimal')
                ->and($wsdl->getInterfaces())->toBeEmpty()
                ->and($wsdl->getBindings())->toBeEmpty()
                ->and($wsdl->getServices())->toBeEmpty();
        });

        test('handles interface with no operations or faults', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('Service', 'http://example.com/')
                ->interface('EmptyInterface')
                ->end();

            // Assert
            $interface = $wsdl->getInterfaces()['EmptyInterface'];
            expect($interface->getOperations())->toBeEmpty()
                ->and($interface->getFaults())->toBeEmpty()
                ->and($interface->getExtends())->toBeEmpty();
        });

        test('handles binding with no operations or faults', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('Service', 'http://example.com/')
                ->binding('EmptyBinding', 'SomeInterface')
                ->end();

            // Assert
            $binding = $wsdl->getBindings()['EmptyBinding'];
            expect($binding->getOperations())->toBeEmpty()
                ->and($binding->getFaults())->toBeEmpty();
        });

        test('handles service with no endpoints', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('Service', 'http://example.com/')
                ->service('EmptyService')
                ->end();

            // Assert
            $service = $wsdl->getServices()['EmptyService'];
            expect($service->getEndpoints())->toBeEmpty()
                ->and($service->getInterfaceRef())->toBeNull();
        });

        test('handles operation with only pattern and no input or output', function (): void {
            // Arrange & Act
            $wsdl = Wsdl2::create('Service', 'http://example.com/')
                ->interface('UserInterface')
                ->operation('MinimalOperation')
                ->pattern(MessageExchangePattern::InOnly->value)
                ->end()
                ->end();

            // Assert
            $operation = $wsdl->getInterfaces()['UserInterface']->getOperations()['MinimalOperation'];
            expect($operation->getPattern())->toBe(MessageExchangePattern::InOnly->value)
                ->and($operation->getInput())->toBeNull()
                ->and($operation->getOutput())->toBeNull()
                ->and($operation->getFaults())->toBeEmpty();
        });
    });
});
