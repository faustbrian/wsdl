<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Imports\SchemaImport;
use Cline\WsdlBuilder\Imports\SchemaInclude;
use Cline\WsdlBuilder\Imports\WsdlImport;
use Cline\WsdlBuilder\Wsdl;

describe('WsdlImport', function (): void {
    describe('Happy Paths', function (): void {
        test('creates import with namespace and location', function (): void {
            // Arrange & Act
            $import = new WsdlImport(
                namespace: 'http://example.com/service',
                location: 'http://example.com/service.wsdl',
            );

            // Assert
            expect($import->namespace)->toBe('http://example.com/service')
                ->and($import->location)->toBe('http://example.com/service.wsdl');
        });

        test('adds import via Wsdl import method', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $result = $wsdl->import(
                namespace: 'http://external.example.com/service',
                location: 'http://external.example.com/service.wsdl',
            );

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getImports())->toHaveCount(1)
                ->and($wsdl->getImports()[0])->toBeInstanceOf(WsdlImport::class)
                ->and($wsdl->getImports()[0]->namespace)->toBe('http://external.example.com/service')
                ->and($wsdl->getImports()[0]->location)->toBe('http://external.example.com/service.wsdl');
        });

        test('allows multiple imports', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $wsdl->import('http://service1.example.com/', 'http://service1.example.com/service.wsdl')
                ->import('http://service2.example.com/', 'http://service2.example.com/service.wsdl');

            // Assert
            expect($wsdl->getImports())->toHaveCount(2)
                ->and($wsdl->getImports()[0]->namespace)->toBe('http://service1.example.com/')
                ->and($wsdl->getImports()[1]->namespace)->toBe('http://service2.example.com/');
        });
    });

    describe('Properties', function (): void {
        test('namespace property is readonly', function (): void {
            // Arrange
            $import = new WsdlImport('http://example.com/', 'http://example.com/service.wsdl');

            // Act & Assert
            expect($import->namespace)->toBe('http://example.com/');
        });

        test('location property is readonly', function (): void {
            // Arrange
            $import = new WsdlImport('http://example.com/', 'http://example.com/service.wsdl');

            // Act & Assert
            expect($import->location)->toBe('http://example.com/service.wsdl');
        });
    });
});

describe('SchemaImport', function (): void {
    describe('Happy Paths', function (): void {
        test('creates import with namespace and schema location', function (): void {
            // Arrange & Act
            $import = new SchemaImport(
                namespace: 'http://example.com/schemas',
                schemaLocation: 'http://example.com/schemas/types.xsd',
            );

            // Assert
            expect($import->namespace)->toBe('http://example.com/schemas')
                ->and($import->schemaLocation)->toBe('http://example.com/schemas/types.xsd');
        });

        test('creates import with namespace only', function (): void {
            // Arrange & Act
            $import = new SchemaImport(namespace: 'http://example.com/schemas');

            // Assert
            expect($import->namespace)->toBe('http://example.com/schemas')
                ->and($import->schemaLocation)->toBeNull();
        });

        test('adds schema import via Wsdl schemaImport method with location', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $result = $wsdl->schemaImport(
                namespace: 'http://schemas.example.com/types',
                schemaLocation: 'http://schemas.example.com/types.xsd',
            );

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getSchemaImports())->toHaveCount(1)
                ->and($wsdl->getSchemaImports()[0])->toBeInstanceOf(SchemaImport::class)
                ->and($wsdl->getSchemaImports()[0]->namespace)->toBe('http://schemas.example.com/types')
                ->and($wsdl->getSchemaImports()[0]->schemaLocation)->toBe('http://schemas.example.com/types.xsd');
        });

        test('adds schema import via Wsdl schemaImport method without location', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $result = $wsdl->schemaImport(namespace: 'http://schemas.example.com/types');

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getSchemaImports())->toHaveCount(1)
                ->and($wsdl->getSchemaImports()[0]->namespace)->toBe('http://schemas.example.com/types')
                ->and($wsdl->getSchemaImports()[0]->schemaLocation)->toBeNull();
        });

        test('allows multiple schema imports', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $wsdl->schemaImport('http://schemas1.example.com/', 'schema1.xsd')
                ->schemaImport('http://schemas2.example.com/', 'schema2.xsd')
                ->schemaImport('http://schemas3.example.com/');

            // Assert
            expect($wsdl->getSchemaImports())->toHaveCount(3)
                ->and($wsdl->getSchemaImports()[0]->namespace)->toBe('http://schemas1.example.com/')
                ->and($wsdl->getSchemaImports()[1]->namespace)->toBe('http://schemas2.example.com/')
                ->and($wsdl->getSchemaImports()[2]->namespace)->toBe('http://schemas3.example.com/')
                ->and($wsdl->getSchemaImports()[2]->schemaLocation)->toBeNull();
        });
    });

    describe('Properties', function (): void {
        test('namespace property is readonly', function (): void {
            // Arrange
            $import = new SchemaImport('http://example.com/schemas');

            // Act & Assert
            expect($import->namespace)->toBe('http://example.com/schemas');
        });

        test('schemaLocation property is readonly and optional', function (): void {
            // Arrange
            $importWithLocation = new SchemaImport('http://example.com/schemas', 'types.xsd');
            $importWithoutLocation = new SchemaImport('http://example.com/schemas');

            // Act & Assert
            expect($importWithLocation->schemaLocation)->toBe('types.xsd')
                ->and($importWithoutLocation->schemaLocation)->toBeNull();
        });
    });
});

describe('SchemaInclude', function (): void {
    describe('Happy Paths', function (): void {
        test('creates include with schema location', function (): void {
            // Arrange & Act
            $include = new SchemaInclude(schemaLocation: 'types.xsd');

            // Assert
            expect($include->schemaLocation)->toBe('types.xsd');
        });

        test('adds schema include via Wsdl schemaInclude method', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $result = $wsdl->schemaInclude(schemaLocation: 'shared-types.xsd');

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getSchemaIncludes())->toHaveCount(1)
                ->and($wsdl->getSchemaIncludes()[0])->toBeInstanceOf(SchemaInclude::class)
                ->and($wsdl->getSchemaIncludes()[0]->schemaLocation)->toBe('shared-types.xsd');
        });

        test('allows multiple schema includes', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $wsdl->schemaInclude('types1.xsd')
                ->schemaInclude('types2.xsd')
                ->schemaInclude('types3.xsd');

            // Assert
            expect($wsdl->getSchemaIncludes())->toHaveCount(3)
                ->and($wsdl->getSchemaIncludes()[0]->schemaLocation)->toBe('types1.xsd')
                ->and($wsdl->getSchemaIncludes()[1]->schemaLocation)->toBe('types2.xsd')
                ->and($wsdl->getSchemaIncludes()[2]->schemaLocation)->toBe('types3.xsd');
        });

        test('chains with other Wsdl methods', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $result = $wsdl
                ->schemaInclude('common.xsd')
                ->complexType('User')
                ->end();

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getSchemaIncludes())->toHaveCount(1)
                ->and($wsdl->getComplexTypes())->toHaveCount(1);
        });
    });

    describe('Properties', function (): void {
        test('schemaLocation property is readonly', function (): void {
            // Arrange
            $include = new SchemaInclude('types.xsd');

            // Act & Assert
            expect($include->schemaLocation)->toBe('types.xsd');
        });
    });
});

describe('Documentation', function (): void {
    describe('Happy Paths', function (): void {
        test('creates documentation with content only', function (): void {
            // Arrange & Act
            $doc = new Documentation(content: 'Service documentation');

            // Assert
            expect($doc->content)->toBe('Service documentation')
                ->and($doc->lang)->toBeNull()
                ->and($doc->source)->toBeNull();
        });

        test('creates documentation with content and language', function (): void {
            // Arrange & Act
            $doc = new Documentation(
                content: 'Service documentation',
                lang: 'en',
            );

            // Assert
            expect($doc->content)->toBe('Service documentation')
                ->and($doc->lang)->toBe('en')
                ->and($doc->source)->toBeNull();
        });

        test('creates documentation with all properties', function (): void {
            // Arrange & Act
            $doc = new Documentation(
                content: 'Service documentation',
                lang: 'en',
                source: 'http://example.com/docs',
            );

            // Assert
            expect($doc->content)->toBe('Service documentation')
                ->and($doc->lang)->toBe('en')
                ->and($doc->source)->toBe('http://example.com/docs');
        });

        test('adds documentation to Wsdl', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $result = $wsdl->documentation('Main service documentation', 'en', 'http://docs.example.com');

            // Assert
            expect($result)->toBe($wsdl)
                ->and($wsdl->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($wsdl->getDocumentation()->content)->toBe('Main service documentation')
                ->and($wsdl->getDocumentation()->lang)->toBe('en')
                ->and($wsdl->getDocumentation()->source)->toBe('http://docs.example.com');
        });

        test('adds documentation to SimpleType', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('Email')
                ->documentation('Email address type', 'en');

            // Assert
            expect($type->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($type->getDocumentation()->content)->toBe('Email address type')
                ->and($type->getDocumentation()->lang)->toBe('en')
                ->and($type->getDocumentation()->source)->toBeNull();
        });

        test('adds documentation to ComplexType', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('User')
                ->documentation('User complex type');

            // Assert
            expect($type->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($type->getDocumentation()->content)->toBe('User complex type');
        });

        test('adds documentation to Message', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $message = $wsdl->message('GetUserRequest')
                ->documentation('Request message for getting user');

            // Assert
            expect($message->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($message->getDocumentation()->content)->toBe('Request message for getting user');
        });

        test('adds documentation to PortType', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $portType = $wsdl->portType('UserServicePort')
                ->documentation('User service operations');

            // Assert
            expect($portType->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($portType->getDocumentation()->content)->toBe('User service operations');
        });

        test('adds documentation to Binding', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $wsdl->portType('UserServicePort');

            // Act
            $binding = $wsdl->binding('UserServiceBinding', 'UserServicePort')
                ->documentation('SOAP binding for user service');

            // Assert
            expect($binding->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($binding->getDocumentation()->content)->toBe('SOAP binding for user service');
        });

        test('adds documentation to Service', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $service = $wsdl->service('UserService')
                ->documentation('User management service');

            // Assert
            expect($service->getDocumentation())->toBeInstanceOf(Documentation::class)
                ->and($service->getDocumentation()->content)->toBe('User management service');
        });

        test('documentation method returns parent for fluent chaining on SimpleType', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('PhoneNumber')
                ->documentation('Phone number format')
                ->pattern('[0-9]{3}-[0-9]{3}-[0-9]{4}');

            // Assert
            expect($type->getDocumentation()->content)->toBe('Phone number format')
                ->and($type->getPattern())->toBe('[0-9]{3}-[0-9]{3}-[0-9]{4}');
        });

        test('documentation method returns parent for fluent chaining on ComplexType', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('Address')
                ->documentation('Address information')
                ->element('street', 'xsd:string');

            // Assert
            expect($type->getDocumentation()->content)->toBe('Address information')
                ->and($type->getElements())->toHaveCount(1);
        });

        test('documentation method returns Wsdl for fluent chaining on Wsdl', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $result = $wsdl
                ->documentation('Main service')
                ->simpleType('CustomType');

            // Assert
            expect($wsdl->getDocumentation()->content)->toBe('Main service')
                ->and($result)->not->toBe($wsdl)
                ->and($wsdl->getSimpleTypes())->toHaveCount(1);
        });
    });

    describe('Properties', function (): void {
        test('content property is readonly', function (): void {
            // Arrange
            $doc = new Documentation('Test content');

            // Act & Assert
            expect($doc->content)->toBe('Test content');
        });

        test('lang property is readonly and optional', function (): void {
            // Arrange
            $docWithLang = new Documentation('Test', 'en');
            $docWithoutLang = new Documentation('Test');

            // Act & Assert
            expect($docWithLang->lang)->toBe('en')
                ->and($docWithoutLang->lang)->toBeNull();
        });

        test('source property is readonly and optional', function (): void {
            // Arrange
            $docWithSource = new Documentation('Test', null, 'http://example.com');
            $docWithoutSource = new Documentation('Test');

            // Act & Assert
            expect($docWithSource->source)->toBe('http://example.com')
                ->and($docWithoutSource->source)->toBeNull();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles empty string content', function (): void {
            // Arrange & Act
            $doc = new Documentation('');

            // Assert
            expect($doc->content)->toBe('');
        });

        test('handles multiline content', function (): void {
            // Arrange
            $content = "Line 1\nLine 2\nLine 3";

            // Act
            $doc = new Documentation($content);

            // Assert
            expect($doc->content)->toBe($content);
        });

        test('handles special characters in content', function (): void {
            // Arrange
            $content = 'Content with <tags> & "quotes" and \'apostrophes\'';

            // Act
            $doc = new Documentation($content);

            // Assert
            expect($doc->content)->toBe($content);
        });

        test('handles unicode characters in content', function (): void {
            // Arrange
            $content = 'Unicode: 你好 Привет مرحبا';

            // Act
            $doc = new Documentation($content);

            // Assert
            expect($doc->content)->toBe($content);
        });

        test('replaces previous documentation when called multiple times on Wsdl', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $wsdl->documentation('First documentation');
            $wsdl->documentation('Second documentation');

            // Assert
            expect($wsdl->getDocumentation()->content)->toBe('Second documentation');
        });

        test('replaces previous documentation when called multiple times on SimpleType', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('TestType')
                ->documentation('First doc')
                ->documentation('Second doc');

            // Assert
            expect($type->getDocumentation()->content)->toBe('Second doc');
        });
    });
});

describe('Integration', function (): void {
    describe('Happy Paths', function (): void {
        test('combines imports and documentation in single Wsdl', function (): void {
            // Arrange & Act
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->documentation('Main service documentation')
                ->import('http://external.example.com/', 'external.wsdl')
                ->schemaImport('http://schemas.example.com/', 'types.xsd')
                ->schemaInclude('common.xsd');

            // Assert
            expect($wsdl->getDocumentation()->content)->toBe('Main service documentation')
                ->and($wsdl->getImports())->toHaveCount(1)
                ->and($wsdl->getSchemaImports())->toHaveCount(1)
                ->and($wsdl->getSchemaIncludes())->toHaveCount(1);
        });

        test('creates complex service with multiple imports and documented types', function (): void {
            // Arrange & Act
            $wsdl = Wsdl::create('ComplexService', 'http://test.example.com/')
                ->documentation('Complex service with imports')
                ->schemaImport('http://www.w3.org/2001/XMLSchema')
                ->schemaInclude('base-types.xsd')
                ->complexType('User')
                ->documentation('User entity')
                ->element('id', 'xsd:int')
                ->element('name', 'xsd:string')
                ->end()
                ->simpleType('Email')
                ->documentation('Email format validation')
                ->pattern('.+@.+\..+')
                ->end();

            // Assert
            expect($wsdl->getDocumentation()->content)->toBe('Complex service with imports')
                ->and($wsdl->getSchemaImports())->toHaveCount(1)
                ->and($wsdl->getSchemaIncludes())->toHaveCount(1)
                ->and($wsdl->getComplexTypes())->toHaveCount(1)
                ->and($wsdl->getSimpleTypes())->toHaveCount(1)
                ->and($wsdl->getComplexTypes()['User']->getDocumentation()->content)->toBe('User entity')
                ->and($wsdl->getSimpleTypes()['Email']->getDocumentation()->content)->toBe('Email format validation');
        });

        test('preserves all imports when building WSDL', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->import('http://service1.example.com/', 'service1.wsdl')
                ->import('http://service2.example.com/', 'service2.wsdl')
                ->schemaImport('http://schemas1.example.com/', 'schema1.xsd')
                ->schemaImport('http://schemas2.example.com/')
                ->schemaInclude('include1.xsd')
                ->schemaInclude('include2.xsd');

            // Act
            $imports = $wsdl->getImports();
            $schemaImports = $wsdl->getSchemaImports();
            $schemaIncludes = $wsdl->getSchemaIncludes();

            // Assert
            expect($imports)->toHaveCount(2)
                ->and($schemaImports)->toHaveCount(2)
                ->and($schemaIncludes)->toHaveCount(2)
                ->and($imports[0]->namespace)->toBe('http://service1.example.com/')
                ->and($imports[1]->namespace)->toBe('http://service2.example.com/')
                ->and($schemaImports[0]->namespace)->toBe('http://schemas1.example.com/')
                ->and($schemaImports[1]->namespace)->toBe('http://schemas2.example.com/')
                ->and($schemaIncludes[0]->schemaLocation)->toBe('include1.xsd')
                ->and($schemaIncludes[1]->schemaLocation)->toBe('include2.xsd');
        });
    });
});
