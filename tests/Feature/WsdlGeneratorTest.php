<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\BindingStyle;
use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Enums\SoapVersion;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

describe('WsdlGenerator Coverage', function (): void {
    describe('Schema Imports and Includes', function (): void {
        test('generates schema imports with namespace', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->schemaImport('http://example.com/external', 'http://example.com/external.xsd')
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:import namespace="http://example.com/external"');
            expect($xml)->toContain('schemaLocation="http://example.com/external.xsd"');
        });

        test('generates schema imports without schemaLocation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->schemaImport('http://example.com/external')
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:import namespace="http://example.com/external"');
            expect($xml)->not->toContain('schemaLocation=');
        });

        test('generates schema includes with schemaLocation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->schemaInclude('common-types.xsd')
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:include schemaLocation="common-types.xsd"');
        });

        test('generates WSDL imports', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->import('http://example.com/external', 'external.wsdl')
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:import');
            expect($xml)->toContain('namespace="http://example.com/external"');
            expect($xml)->toContain('location="external.wsdl"');
        });
    });

    describe('Schema Redefines', function (): void {
        test('generates schema redefine with simple type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test');
            $wsdl->redefine('base-types.xsd')
                ->simpleType('StatusType')
                ->base(XsdType::String)
                ->enumeration('draft', 'published');

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:redefine schemaLocation="base-types.xsd"');
            expect($xml)->toContain('name="StatusType"');
        });

        test('generates schema redefine with complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test');
            $wsdl->redefine('base-types.xsd')
                ->complexType('PersonType')
                ->element('id', XsdType::Int)
                ->element('name', XsdType::String);

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:redefine schemaLocation="base-types.xsd"');
            expect($xml)->toContain('name="PersonType"');
        });

        test('generates schema redefine with attribute group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test');
            $wsdl->redefine('base-types.xsd')
                ->attributeGroup('CommonAttributes')
                ->attribute('version', XsdType::String);

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:redefine schemaLocation="base-types.xsd"');
            expect($xml)->toContain('name="CommonAttributes"');
        });

        test('generates schema redefine with element group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test');
            $wsdl->redefine('base-types.xsd')
                ->group('CommonElements')
                ->element('timestamp', XsdType::DateTime);

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:redefine schemaLocation="base-types.xsd"');
            expect($xml)->toContain('<xsd:group name="CommonElements"');
        });
    });

    describe('Simple Type Restriction Facets', function (): void {
        test('generates simple type with final attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->simpleType('FinalType')
                ->base(XsdType::String)
                ->final('restriction')
                ->pattern('[A-Z]+')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('final="restriction"');
        });
    });

    describe('List Types', function (): void {
        test('generates list type without restrictions', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->listType('IntegerList')
                ->itemType(XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:list itemType="xsd:int"');
        });

        test('generates list type with restrictions', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->listType('RestrictedList')
                ->itemType(XsdType::String)
                ->minLength(1)
                ->maxLength(10)
                ->pattern('[a-z]+')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:list itemType="xsd:string"');
            expect($xml)->toContain('xsd:restriction');
            expect($xml)->toContain('xsd:minLength');
            expect($xml)->toContain('xsd:maxLength');
            expect($xml)->toContain('xsd:pattern');
        });

        test('generates list type with enumeration', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->listType('EnumList')
                ->itemType(XsdType::String)
                ->enumeration('a', 'b', 'c')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('xsd:enumeration');
        });
    });

    describe('Union Types', function (): void {
        test('generates union type with multiple member types', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->unionType('NumberOrString')
                ->memberTypes(XsdType::Int, XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:union memberTypes="xsd:int xsd:string"');
        });

        test('generates union type with custom types', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->simpleType('TypeA')
                ->base(XsdType::String)
                ->end()
                ->simpleType('TypeB')
                ->base(XsdType::Int)
                ->end()
                ->unionType('CustomUnion')
                ->memberTypes('tns:TypeA', 'tns:TypeB')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('memberTypes="tns:TypeA tns:TypeB"');
        });
    });

    describe('Complex Type Variations', function (): void {
        test('generates complex type with mixed content', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('MixedType')
                ->mixed()
                ->element('emphasis', XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('mixed="true"');
        });

        test('generates complex type with block attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('BlockedType')
                ->block('extension')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('block="extension"');
        });

        test('generates complex type with final attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('FinalType')
                ->final('restriction')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('final="restriction"');
        });
    });

    describe('Simple Content', function (): void {
        test('generates simple content extension', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('ExtendedString')
                ->simpleContent()
                ->extension(XsdType::String)
                ->attribute('lang', XsdType::String)
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:simpleContent>');
            expect($xml)->toContain('<xsd:extension base="xsd:string"');
            expect($xml)->toContain('name="lang"');
        });

        test('generates simple content restriction', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->simpleType('BaseString')
                ->base(XsdType::String)
                ->end()
                ->complexType('RestrictedString')
                ->simpleContent()
                ->restriction('BaseString')
                ->attribute('version', XsdType::String)
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:simpleContent>');
            expect($xml)->toContain('<xsd:restriction base="tns:BaseString"');
        });
    });

    describe('Element Variations', function (): void {
        test('generates element with substitutionGroup', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->element('item', XsdType::String)
                ->substitutionGroup('tns:baseItem')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('substitutionGroup="tns:baseItem"');
        });

        test('generates element with block attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->element('item', XsdType::String)
                ->block('substitution')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('block="substitution"');
        });
    });

    describe('Attributes', function (): void {
        test('generates attribute with use required', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->attribute('id', XsdType::String, 'required')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('use="required"');
        });

        test('generates attribute with use optional', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->attribute('version', XsdType::String, 'optional')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('use="optional"');
        });

        test('generates attribute with use prohibited', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->attribute('deprecated', XsdType::String, 'prohibited')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('use="prohibited"');
        });

        test('generates attribute with default value', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->attribute('status', XsdType::String)
                ->default('active')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('default="active"');
        });

        test('generates attribute with fixed value', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->attribute('version', XsdType::String)
                ->fixed('1.0')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('fixed="1.0"');
        });

        test('generates attribute with form qualified', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->attribute('xmlns', XsdType::String)
                ->form('qualified')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('form="qualified"');
        });

        test('generates attribute with form unqualified', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->attribute('local', XsdType::String)
                ->form('unqualified')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('form="unqualified"');
        });
    });

    describe('AnyAttribute', function (): void {
        test('generates anyAttribute with namespace', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->anyAttribute('##any', 'lax')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:anyAttribute');
            expect($xml)->toContain('namespace="##any"');
            expect($xml)->toContain('processContents="lax"');
        });

        test('generates anyAttribute with strict processing', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->anyAttribute('##other', 'strict')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('processContents="strict"');
        });

        test('generates anyAttribute with skip processing', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->anyAttribute('##targetNamespace', 'skip')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('processContents="skip"');
        });
    });

    describe('Group References', function (): void {
        test('generates group reference in complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->elementGroup('CommonElements')
                ->element('timestamp', XsdType::DateTime)
                ->end()
                ->complexType('TestType')
                ->groupRef('CommonElements')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:group ref="tns:CommonElements"');
        });

        test('generates attribute group reference in complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->attributeGroup('CommonAttributes')
                ->attribute('version', XsdType::String)
                ->end()
                ->complexType('TestType')
                ->attributeGroupRef('CommonAttributes')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:attributeGroup ref="tns:CommonAttributes"');
        });
    });

    describe('Identity Constraints', function (): void {
        test('generates key constraint with selector and field', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('UsersType')
                ->element('user', 'tns:UserType', false, 0, -1)
                ->end()
                ->key('userKey')
                ->selector('.//user')
                ->field('@id')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:key name="userKey"');
            expect($xml)->toContain('<xsd:selector xpath=".//user"');
            expect($xml)->toContain('<xsd:field xpath="@id"');
        });

        test('generates keyref constraint', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('OrdersType')
                ->element('order', 'tns:OrderType', false, 0, -1)
                ->end()
                ->keyRef('orderUserRef', 'userKey')
                ->selector('.//order')
                ->field('@userId')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:keyref name="orderUserRef"');
            expect($xml)->toContain('refer="userKey"');
            expect($xml)->toContain('<xsd:selector xpath=".//order"');
            expect($xml)->toContain('<xsd:field xpath="@userId"');
        });

        test('generates unique constraint', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('EmailsType')
                ->element('email', XsdType::String, false, 0, -1)
                ->end()
                ->unique('emailUnique')
                ->selector('.//email')
                ->field('.')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:unique name="emailUnique"');
            expect($xml)->toContain('<xsd:selector xpath=".//email"');
            expect($xml)->toContain('<xsd:field xpath="."');
        });
    });

    describe('Compositors', function (): void {
        test('generates choice compositor with minOccurs and maxOccurs', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('PaymentType')
                ->choice(0, -1)
                ->element('creditCard', XsdType::String)
                ->element('bankAccount', XsdType::String)
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:choice');
            expect($xml)->toContain('minOccurs="0"');
            expect($xml)->toContain('maxOccurs="unbounded"');
        });

        test('generates all compositor', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('PersonType')
                ->all()
                ->element('firstName', XsdType::String)
                ->element('lastName', XsdType::String)
                ->element('email', XsdType::String)
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:all>');
        });

        test('generates any wildcard with namespace and processContents', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('ExtensibleType')
                ->any('##other', 'lax', 0, -1)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:any');
            expect($xml)->toContain('namespace="##other"');
            expect($xml)->toContain('processContents="lax"');
            expect($xml)->toContain('minOccurs="0"');
            expect($xml)->toContain('maxOccurs="unbounded"');
        });
    });

    describe('XSD Annotations', function (): void {
        test('generates annotation with documentation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('AnnotatedType')
                ->annotation()
                ->documentation('This is a user type')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:annotation>');
            expect($xml)->toContain('<xsd:documentation>');
            expect($xml)->toContain('This is a user type');
        });

        test('generates annotation with documentation with lang', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('AnnotatedType')
                ->annotation()
                ->documentation('This is a user type', 'en')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('xml:lang="en"');
        });

        test('generates annotation with documentation with source', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('AnnotatedType')
                ->annotation()
                ->documentation('User documentation', null, 'http://example.com/docs')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('source="http://example.com/docs"');
        });

        test('generates annotation with appinfo', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('AnnotatedType')
                ->annotation()
                ->appInfo('Application specific info')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:appinfo>');
            expect($xml)->toContain('Application specific info');
        });

        test('generates annotation with appinfo with source', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('AnnotatedType')
                ->annotation()
                ->appInfo('Custom metadata', 'http://example.com/metadata')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:appinfo');
            expect($xml)->toContain('source="http://example.com/metadata"');
        });

        test('generates annotation with multiple documentations and appinfos', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('AnnotatedType')
                ->annotation()
                ->documentation('English description', 'en')
                ->documentation('Description française', 'fr')
                ->appInfo('Metadata 1')
                ->appInfo('Metadata 2')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('English description');
            expect($xml)->toContain('Description française');
            expect($xml)->toContain('Metadata 1');
            expect($xml)->toContain('Metadata 2');
        });
    });

    describe('Documentation', function (): void {
        test('generates WSDL documentation with lang attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->documentation('Service documentation', 'en')
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('xml:lang="en"');
            expect($xml)->toContain('Service documentation');
        });

        test('generates WSDL documentation with source attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->documentation('External docs', null, 'http://example.com/docs')
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('source="http://example.com/docs"');
        });

        test('generates documentation on message', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('TestMessage')
                ->documentation('Message documentation')
                ->part('param', XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('Message documentation');
        });

        test('generates documentation on portType', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->portType('TestPort')
                ->documentation('PortType documentation')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('PortType documentation');
        });

        test('generates documentation on binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('TestBinding', 'TestPort')
                ->documentation('Binding documentation')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('Binding documentation');
        });

        test('generates documentation on service', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->service('TestService')
                ->documentation('Service endpoint documentation')
                ->port('TestPort', 'TestBinding', 'http://example.com/soap')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('Service endpoint documentation');
        });

        test('generates documentation on complexType', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('TestType')
                ->documentation('Complex type documentation')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('Complex type documentation');
        });

        test('generates documentation on simpleType', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->simpleType('TestType')
                ->documentation('Simple type documentation')
                ->base(XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('Simple type documentation');
        });
    });

    describe('Message Variations', function (): void {
        test('generates message part with type attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('TestMessage')
                ->part('param', XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('type="xsd:string"');
        });

        test('generates message part with element attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->complexType('RequestType')
                ->element('id', XsdType::Int)
                ->end()
                ->message('TestMessage')
                ->part('parameters', 'tns:RequestType')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('element="tns:RequestType"');
        });

        test('generates message with multiple parts', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('MultiPartMessage')
                ->part('header', XsdType::String)
                ->part('body', XsdType::String)
                ->part('footer', XsdType::String)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('name="header"');
            expect($xml)->toContain('name="body"');
            expect($xml)->toContain('name="footer"');
        });
    });

    describe('PortType Operation Variations', function (): void {
        test('generates one-way operation without output', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('NotificationMessage')
                ->part('event', XsdType::String)
                ->end()
                ->portType('TestPort')
                ->operation('Notify', 'NotificationMessage')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:input');
            expect($xml)->not->toContain('<wsdl:output');
        });

        test('generates notification operation without input', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('EventMessage')
                ->part('event', XsdType::String)
                ->end()
                ->portType('TestPort')
                ->operation('OnEvent', null, 'EventMessage')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:output');
            expect($xml)->not->toContain('<wsdl:input');
        });

        test('generates operation with fault', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('InputMessage')->end()
                ->message('OutputMessage')->end()
                ->message('FaultMessage')
                ->part('error', XsdType::String)
                ->end()
                ->portType('TestPort')
                ->operation('RiskyOperation', 'InputMessage', 'OutputMessage', 'FaultMessage')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:fault');
            expect($xml)->toContain('name="FaultMessage"');
            expect($xml)->toContain('message="tns:FaultMessage"');
        });
    });

    describe('SOAP Bindings', function (): void {
        test('generates SOAP binding with rpc/encoded style', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->defaultStyle(BindingStyle::Rpc)
                ->defaultUse(BindingUse::Encoded)
                ->binding('TestBinding', 'TestPort')
                ->operation('TestOp', 'urn:test')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('style="rpc"');
            expect($xml)->toContain('use="encoded"');
        });

        test('generates SOAP binding with document/literal style', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->defaultStyle(BindingStyle::Document)
                ->defaultUse(BindingUse::Literal)
                ->binding('TestBinding', 'TestPort')
                ->operation('TestOp', 'urn:test')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('style="document"');
            expect($xml)->toContain('use="literal"');
        });

        test('generates SOAP binding operation with custom style', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('TestBinding', 'TestPort')
                ->operation('TestOp', 'urn:test', BindingStyle::Rpc)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('style="rpc"');
        });
    });

    describe('SOAP Headers', function (): void {
        test('generates SOAP header in operation', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('HeaderMessage')
                ->part('authentication', XsdType::String)
                ->end()
                ->binding('TestBinding', 'TestPort')
                ->operation('SecureOp', 'urn:test')
                ->header('HeaderMessage', 'authentication', BindingUse::Literal)
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<soap:header');
            expect($xml)->toContain('message="tns:HeaderMessage"');
            expect($xml)->toContain('part="authentication"');
            expect($xml)->toContain('use="literal"');
        });

        test('generates SOAP header with namespace', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('HeaderMessage')
                ->part('auth', XsdType::String)
                ->end()
                ->binding('TestBinding', 'TestPort')
                ->operation('SecureOp', 'urn:test')
                ->header('HeaderMessage', 'auth', BindingUse::Literal, 'http://example.com/auth')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('namespace="http://example.com/auth"');
        });

        test('generates SOAP header with encodingStyle', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('HeaderMessage')
                ->part('auth', XsdType::String)
                ->end()
                ->binding('TestBinding', 'TestPort')
                ->operation('SecureOp', 'urn:test')
                ->header('HeaderMessage', 'auth', BindingUse::Encoded, null, 'http://schemas.xmlsoap.org/soap/encoding/')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"');
        });

        test('generates SOAP header fault', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('HeaderMessage')
                ->part('auth', XsdType::String)
                ->end()
                ->message('HeaderFaultMessage')
                ->part('authError', XsdType::String)
                ->end()
                ->binding('TestBinding', 'TestPort')
                ->operation('SecureOp', 'urn:test')
                ->header('HeaderMessage', 'auth', BindingUse::Literal)
                ->headerFault('HeaderFaultMessage', 'authError', BindingUse::Literal)
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<soap:headerfault');
            expect($xml)->toContain('message="tns:HeaderFaultMessage"');
            expect($xml)->toContain('part="authError"');
        });
    });

    describe('HTTP Bindings', function (): void {
        test('generates HTTP GET binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('HttpBinding', 'TestPort')
                ->httpBinding('GET')
                ->operation('GetData', 'urn:getData')
                ->httpOperation('/data')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<http:binding');
            expect($xml)->toContain('verb="GET"');
            expect($xml)->toContain('<http:operation');
            expect($xml)->toContain('location="/data"');
        });

        test('generates HTTP POST binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('HttpBinding', 'TestPort')
                ->httpBinding('POST')
                ->operation('PostData', 'urn:postData')
                ->httpOperation('/data')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('verb="POST"');
        });

        test('generates HTTP binding with urlEncoded', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('HttpBinding', 'TestPort')
                ->httpBinding('POST')
                ->operation('PostForm', 'urn:postForm')
                ->httpOperation('/form')
                ->httpUrlEncoded()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<http:urlEncoded');
        });

        test('generates HTTP binding with urlReplacement', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('HttpBinding', 'TestPort')
                ->httpBinding('GET')
                ->operation('GetResource', 'urn:getResource')
                ->httpOperation('/resource/(id)')
                ->httpUrlReplacement()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<http:urlReplacement');
        });
    });

    describe('MIME Multipart Bindings', function (): void {
        test('generates MIME multipart for input', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('TestBinding', 'TestPort')
                ->operation('Upload', 'urn:upload')
                ->mimeMultipart('input')
                ->mimePart('file', 'application/octet-stream')
                ->mimePart('metadata', 'application/json')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<mime:multipartRelated>');
            expect($xml)->toContain('<mime:part');
            expect($xml)->toContain('type="application/octet-stream"');
            expect($xml)->toContain('type="application/json"');
        });

        test('generates MIME multipart for output', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('TestBinding', 'TestPort')
                ->operation('Download', 'urn:download')
                ->mimeMultipart('output')
                ->mimePart('file', 'application/pdf')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('type="application/pdf"');
        });

        test('generates MIME part with SOAP body', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('TestBinding', 'TestPort')
                ->operation('ComplexUpload', 'urn:upload')
                ->mimeMultipart('input')
                ->soapBodyPart()
                ->mimePart('attachment', 'application/octet-stream')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<soap:body');
            expect($xml)->toContain('use="literal"');
        });

        test('generates MIME part with name attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('TestBinding', 'TestPort')
                ->operation('NamedUpload', 'urn:upload')
                ->mimeMultipart('input')
                ->mimePartNamed('document', 'content', 'application/xml')
                ->end()
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('name="document"');
            expect($xml)->toContain('part="content"');
        });
    });

    describe('WS-Addressing', function (): void {
        test('generates WS-Addressing on binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('TestBinding', 'TestPort')
                ->usingAddressing()
                ->operation('TestOp', 'urn:test')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('xmlns:wsaw="http://www.w3.org/2006/05/addressing/wsdl"');
            expect($xml)->toContain('wsaw:UsingAddressing="required"');
        });

        test('generates WS-Addressing on portType', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->portType('TestPort')
                ->usingAddressing()
                ->operation('TestOp', 'InputMsg', 'OutputMsg')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('wsaw:UsingAddressing="true"');
        });

        test('generates WS-Addressing action on operation input', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('InputMsg')->end()
                ->message('OutputMsg')->end()
                ->portType('TestPort')
                ->operation('TestOp', 'InputMsg', 'OutputMsg')
                ->action('http://example.com/TestAction')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsaw:Action>http://example.com/TestAction</wsaw:Action>');
        });

        test('generates WS-Addressing action on operation output', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('InputMsg')->end()
                ->message('OutputMsg')->end()
                ->portType('TestPort')
                ->operation('TestOp', 'InputMsg', 'OutputMsg')
                ->action('http://example.com/Input', 'http://example.com/Output')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('http://example.com/Output');
        });

        test('generates WS-Addressing fault action', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->message('InputMsg')->end()
                ->message('OutputMsg')->end()
                ->message('FaultMsg')->end()
                ->portType('TestPort')
                ->operation('TestOp', 'InputMsg', 'OutputMsg', 'FaultMsg')
                ->action('http://example.com/Input', 'http://example.com/Output')
                ->faultAction('FaultMsg', 'http://example.com/Fault')
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('http://example.com/Fault');
        });
    });

    describe('WS-Policy', function (): void {
        test('generates WS-Policy at WSDL level', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->policy('MyPolicy')
                ->assertion('sp:TransportBinding', 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702')
                ->end()
                ->end()
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('xmlns:wsp="http://www.w3.org/ns/ws-policy"');
            expect($xml)->toContain('<wsp:Policy');
            expect($xml)->toContain('xml:id="MyPolicy"');
        });

        test('generates WS-Policy with Name attribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->policy('PolicyId', 'PolicyName')
                ->assertion('wsp:Test', 'http://www.w3.org/ns/ws-policy')
                ->end()
                ->end()
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('Name="PolicyName"');
        });

        test('generates WS-Policy on binding', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('SecureBinding', 'TestPort')
                ->policy('BindingPolicy')
                ->assertion('sp:TransportBinding', 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702')
                ->end()
                ->end()
                ->operation('TestOp', 'urn:test')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('xmlns:sp="http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702"');
            expect($xml)->toContain('<sp:TransportBinding');
        });

        test('generates WS-PolicyReference', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('SecureBinding', 'TestPort')
                ->policyReference('#MyPolicy')
                ->operation('TestOp', 'urn:test')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsp:PolicyReference');
            expect($xml)->toContain('URI="#MyPolicy"');
        });

        test('generates WS-PolicyReference with digest', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->binding('SecureBinding', 'TestPort')
                ->policyReference('#MyPolicy', 'abc123', 'http://www.w3.org/2001/04/xmlenc#sha256')
                ->operation('TestOp', 'urn:test')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('Digest="abc123"');
            expect($xml)->toContain('DigestAlgorithm="http://www.w3.org/2001/04/xmlenc#sha256"');
        });

        test('generates WS-Policy with All operator', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->policy('ComplexPolicy')
                ->all()
                ->assertion('wsp:Test1', 'http://www.w3.org/ns/ws-policy')
                ->assertion('wsp:Test2', 'http://www.w3.org/ns/ws-policy')
                ->end()
                ->end()
                ->end()
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsp:All>');
        });

        test('generates WS-Policy with ExactlyOne operator', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->policy('ChoicePolicy')
                ->exactlyOne()
                ->assertion('wsp:Option1', 'http://www.w3.org/ns/ws-policy')
                ->assertion('wsp:Option2', 'http://www.w3.org/ns/ws-policy')
                ->end()
                ->end()
                ->end()
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsp:ExactlyOne>');
        });

        test('generates WS-Policy with nested operators', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->policy('NestedPolicy')
                ->exactlyOne()
                ->all()
                ->assertion('wsp:Assertion1', 'http://www.w3.org/ns/ws-policy')
                ->assertion('wsp:Assertion2', 'http://www.w3.org/ns/ws-policy')
                ->end()
                ->all()
                ->assertion('wsp:Assertion3', 'http://www.w3.org/ns/ws-policy')
                ->end()
                ->end()
                ->end()
                ->end()
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsp:ExactlyOne>');
            expect($xml)->toContain('<wsp:All>');
        });

        test('generates WS-Policy assertion with attributes', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->policy('AttrPolicy')
                ->assertion('sp:HttpsToken', 'http://docs.oasis-open.org/ws-sx/ws-securitypolicy/200702', [
                    'RequireClientCertificate' => 'true',
                ])
                ->end()
                ->end()
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('RequireClientCertificate="true"');
        });

        test('generates WS-Policy nested policy in operator', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->policy('OuterPolicy')
                ->all()
                ->nestedPolicy()
                ->assertion('wsp:Test', 'http://www.w3.org/ns/ws-policy')
                ->end()
                ->end()
                ->end()
                ->end()
                ->complexType('TestType')
                ->element('id', XsdType::Int)
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsp:Policy>');
        });
    });

    describe('Service Variations', function (): void {
        test('generates service with multiple ports', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->service('MultiPortService')
                ->port('Port1', 'Binding1', 'http://example.com/service1')
                ->port('Port2', 'Binding2', 'http://example.com/service2')
                ->port('Port3', 'Binding3', 'http://example.com/service3')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('name="Port1"');
            expect($xml)->toContain('name="Port2"');
            expect($xml)->toContain('name="Port3"');
            expect($xml)->toContain('binding="tns:Binding1"');
            expect($xml)->toContain('binding="tns:Binding2"');
            expect($xml)->toContain('binding="tns:Binding3"');
        });

        test('generates service with WS-Policy', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->service('SecureService')
                ->policy('ServicePolicy')
                ->assertion('wsp:Test', 'http://www.w3.org/ns/ws-policy')
                ->end()
                ->end()
                ->port('TestPort', 'TestBinding', 'http://example.com/soap')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('xmlns:wsp=');
        });

        test('generates service with policy reference', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->service('SecureService')
                ->policyReference('#GlobalPolicy')
                ->port('TestPort', 'TestBinding', 'http://example.com/soap')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsp:PolicyReference URI="#GlobalPolicy"');
        });
    });

    describe('Element Groups', function (): void {
        test('generates element group with choice', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->elementGroup('PaymentChoice')
                ->choice()
                ->element('creditCard', XsdType::String)
                ->element('bankAccount', XsdType::String)
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:group name="PaymentChoice"');
            expect($xml)->toContain('<xsd:choice>');
        });

        test('generates element group with all', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->elementGroup('PersonGroup')
                ->all()
                ->element('firstName', XsdType::String)
                ->element('lastName', XsdType::String)
                ->end()
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:all>');
        });
    });

    describe('Attribute Groups', function (): void {
        test('generates attribute group with anyAttribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://example.com/test')
                ->attributeGroup('ExtensibleAttributes')
                ->attribute('id', XsdType::String)
                ->anyAttribute('##other', 'lax')
                ->end();

            $xml = $wsdl->build();

            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xsd:attributeGroup name="ExtensibleAttributes"');
            expect($xml)->toContain('<xsd:anyAttribute');
        });
    });
});
