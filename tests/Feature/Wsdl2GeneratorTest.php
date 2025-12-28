<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl2\Enums\MessageExchangePattern;
use Cline\WsdlBuilder\Wsdl2\Wsdl2;

describe('Wsdl2Generator', function (): void {
    describe('Basic WSDL 2.0 Structure', function (): void {
        test('generates empty WSDL 2.0 with just description element', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('EmptyService', 'http://example.com/empty');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<?xml version="1.0" encoding="UTF-8"?>');
            expect($xml)->toContain('<wsdl:description');
            expect($xml)->toContain('targetNamespace="http://example.com/empty"');
            expect($xml)->toContain('xmlns:wsdl="http://www.w3.org/ns/wsdl"');
            expect($xml)->toContain('xmlns:tns="http://example.com/empty"');
            expect($xml)->toContain('xmlns:xs="http://www.w3.org/2001/XMLSchema"');
        });

        test('generates WSDL 2.0 with documentation at description level', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('DocumentedService', 'http://example.com/documented')
                ->documentation('This is a comprehensive service documentation', 'en', 'http://example.com/docs');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('xml:lang="en"');
            expect($xml)->toContain('source="http://example.com/docs"');
            expect($xml)->toContain('This is a comprehensive service documentation');
        });
    });

    describe('Simple Types with Restrictions', function (): void {
        test('generates simple type with minLength and maxLength restrictions', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->simpleType('Username')
                ->base(XsdType::String)
                ->minLength(3)
                ->maxLength(50);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="Username">');
            expect($xml)->toContain('<xs:restriction base="xs:string">');
            expect($xml)->toContain('<xs:minLength value="3"/>');
            expect($xml)->toContain('<xs:maxLength value="50"/>');
        });

        test('generates simple type with pattern restriction', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->simpleType('PhoneNumber')
                ->base(XsdType::String)
                ->pattern('[0-9]{3}-[0-9]{3}-[0-9]{4}');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="PhoneNumber">');
            expect($xml)->toContain('<xs:pattern value="\d{3}-\d{3}-\d{4}"/>');
        });

        test('generates simple type with enumeration values', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->simpleType('Status')
                ->base(XsdType::String)
                ->enumeration('active')
                ->enumeration('inactive')
                ->enumeration('pending');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="Status">');
            expect($xml)->toContain('<xs:enumeration value="active"/>');
            expect($xml)->toContain('<xs:enumeration value="inactive"/>');
            expect($xml)->toContain('<xs:enumeration value="pending"/>');
        });

        test('generates simple type with minInclusive and maxInclusive', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->simpleType('Age')
                ->base(XsdType::Int)
                ->minInclusive('0')
                ->maxInclusive('150');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="Age">');
            expect($xml)->toContain('<xs:minInclusive value="0"/>');
            expect($xml)->toContain('<xs:maxInclusive value="150"/>');
        });

        test('generates simple type with minExclusive and maxExclusive', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->simpleType('Percentage')
                ->base(XsdType::Decimal)
                ->minExclusive('0')
                ->maxExclusive('100');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="Percentage">');
            expect($xml)->toContain('<xs:minExclusive value="0"/>');
            expect($xml)->toContain('<xs:maxExclusive value="100"/>');
        });

        test('generates simple type with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->simpleType('Email')
                ->base(XsdType::String)
                ->documentation('Valid email address format', 'en')
                ->pattern('[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('Valid email address format');
        });
    });

    describe('List Types', function (): void {
        test('generates list type without restrictions', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->listType('IntegerList')
                ->itemType(XsdType::Int);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="IntegerList">');
            expect($xml)->toContain('<xs:list itemType="xs:int"/>');
        });

        test('generates list type with minLength restriction', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->listType('NonEmptyList')
                ->itemType(XsdType::String)
                ->minLength(1);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="NonEmptyList">');
            expect($xml)->toContain('<xs:list itemType="xs:string"/>');
            expect($xml)->toContain('<xs:minLength value="1"/>');
        });

        test('generates list type with maxLength restriction', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->listType('BoundedList')
                ->itemType(XsdType::String)
                ->maxLength(10);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:maxLength value="10"/>');
        });

        test('generates list type with pattern restriction', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->listType('PatternList')
                ->itemType(XsdType::String)
                ->pattern('[A-Z]+');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:pattern value="[A-Z]+"/>');
        });

        test('generates list type with enumeration restriction', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->listType('EnumList')
                ->itemType(XsdType::String)
                ->enumeration('value1')
                ->enumeration('value2');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:enumeration value="value1"/>');
            expect($xml)->toContain('<xs:enumeration value="value2"/>');
        });
    });

    describe('Union Types', function (): void {
        test('generates union type with multiple member types', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->unionType('NumberOrString')
                ->memberTypes(XsdType::Int, XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:simpleType name="NumberOrString">');
            expect($xml)->toContain('<xs:union memberTypes="xs:int xs:string"/>');
        });

        test('generates union type with custom types', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->unionType('MixedType')
                ->memberTypes('tns:CustomType1', 'tns:CustomType2', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:union memberTypes="tns:CustomType1 tns:CustomType2 xs:string"/>');
        });
    });

    describe('Complex Types - Basic Elements', function (): void {
        test('generates complex type with basic elements', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('Person')
                ->element('name', XsdType::String)
                ->element('age', XsdType::Int)
                ->element('email', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:complexType name="Person">');
            expect($xml)->toContain('<xs:sequence>');
            expect($xml)->toContain('<xs:element name="name" type="xs:string"/>');
            expect($xml)->toContain('<xs:element name="age" type="xs:int"/>');
            expect($xml)->toContain('<xs:element name="email" type="xs:string"/>');
        });

        test('generates complex type with nullable elements', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('OptionalPerson')
                ->element('name', XsdType::String, nullable: true)
                ->element('email', XsdType::String, nullable: true);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:element name="name" type="xs:string" nillable="true"/>');
            expect($xml)->toContain('<xs:element name="email" type="xs:string" nillable="true"/>');
        });

        test('generates complex type with minOccurs and maxOccurs', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('Container')
                ->element('item', XsdType::String, minOccurs: 0, maxOccurs: -1);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:element name="item" type="xs:string" minOccurs="0" maxOccurs="unbounded"/>');
        });

        test('generates abstract complex type', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('AbstractBase')
                ->abstract(true)
                ->element('id', XsdType::Int);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:complexType name="AbstractBase" abstract="true">');
        });

        test('generates mixed content complex type', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('MixedContent')
                ->mixed(true)
                ->element('emphasis', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:complexType name="MixedContent" mixed="true">');
        });

        test('generates complex type with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('DocumentedType')
                ->documentation('A well-documented complex type', 'en')
                ->element('value', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('A well-documented complex type');
        });
    });

    describe('Complex Types - Extension', function (): void {
        test('generates complex type with extension', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('BaseType')
                ->element('id', XsdType::Int);
            $wsdl->complexType('ExtendedType')
                ->extends('BaseType')
                ->element('name', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:complexType name="ExtendedType">');
            expect($xml)->toContain('<xs:complexContent>');
            expect($xml)->toContain('<xs:extension base="tns:BaseType">');
            expect($xml)->toContain('<xs:element name="name" type="xs:string"/>');
        });
    });

    describe('Complex Types - Attributes', function (): void {
        test('generates complex type with attributes', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $type = $wsdl->complexType('TypeWithAttrs');
            $type->element('value', XsdType::String);
            $type->attribute('id', XsdType::Int);
            $type->attribute('status', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attribute name="id" type="xs:int"/>');
            expect($xml)->toContain('<xs:attribute name="status" type="xs:string"/>');
        });

        test('generates complex type with required attribute', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('RequiredAttr')
                ->element('value', XsdType::String)
                ->attribute('id', XsdType::Int)->use('required');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attribute name="id" type="xs:int" use="required"/>');
        });

        test('generates complex type with default attribute value', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('DefaultAttr')
                ->element('value', XsdType::String)
                ->attribute('status', XsdType::String)->default('active');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attribute name="status" type="xs:string" default="active"/>');
        });

        test('generates complex type with fixed attribute value', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('FixedAttr')
                ->element('value', XsdType::String)
                ->attribute('version', XsdType::String)->fixed('1.0');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attribute name="version" type="xs:string" fixed="1.0"/>');
        });

        test('generates complex type with attribute form', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('QualifiedAttr')
                ->element('value', XsdType::String)
                ->attribute('id', XsdType::Int)->form('qualified');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attribute name="id" type="xs:int" form="qualified"/>');
        });

        test('generates complex type with anyAttribute', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('AnyAttrType')
                ->element('value', XsdType::String)
                ->anyAttribute()->namespace('##any')->processContents('lax');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:anyAttribute namespace="##any" processContents="lax"/>');
        });
    });

    describe('Element Groups', function (): void {
        test('generates element group with sequence', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->elementGroup('AddressGroup')
                ->element('street', XsdType::String)
                ->element('city', XsdType::String)
                ->element('zip', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:group name="AddressGroup">');
            expect($xml)->toContain('<xs:sequence>');
            expect($xml)->toContain('<xs:element name="street" type="xs:string"/>');
            expect($xml)->toContain('<xs:element name="city" type="xs:string"/>');
            expect($xml)->toContain('<xs:element name="zip" type="xs:string"/>');
        });

        test('generates element group with choice', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->elementGroup('PaymentGroup')
                ->choice()
                ->element('creditCard', XsdType::String)
                ->element('bankAccount', XsdType::String)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:group name="PaymentGroup">');
            expect($xml)->toContain('<xs:choice>');
            expect($xml)->toContain('<xs:element name="creditCard" type="xs:string"/>');
            expect($xml)->toContain('<xs:element name="bankAccount" type="xs:string"/>');
        });

        test('generates element group with all compositor', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->elementGroup('UnorderedGroup')
                ->all()
                ->element('field1', XsdType::String)
                ->element('field2', XsdType::String)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:group name="UnorderedGroup">');
            expect($xml)->toContain('<xs:all>');
            expect($xml)->toContain('<xs:element name="field1" type="xs:string"/>');
            expect($xml)->toContain('<xs:element name="field2" type="xs:string"/>');
        });

        test('generates complex type with group reference', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->elementGroup('ContactGroup')
                ->element('email', XsdType::String)
                ->element('phone', XsdType::String);
            $wsdl->complexType('Person')
                ->element('name', XsdType::String)
                ->group('ContactGroup');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:group ref="tns:ContactGroup"/>');
        });
    });

    describe('Attribute Groups', function (): void {
        test('generates attribute group with attributes', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $group = $wsdl->attributeGroup('CommonAttrs');
            $group->attribute('id', XsdType::Int);
            $group->attribute('created', XsdType::DateTime);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attributeGroup name="CommonAttrs">');
            expect($xml)->toContain('<xs:attribute name="id" type="xs:int"/>');
            expect($xml)->toContain('<xs:attribute name="created" type="xs:dateTime"/>');
        });

        test('generates attribute group with anyAttribute', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $group = $wsdl->attributeGroup('ExtensibleAttrs');
            $group->attribute('id', XsdType::Int);
            $group->anyAttribute()->namespace('##other')->processContents('strict');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attributeGroup name="ExtensibleAttrs">');
            expect($xml)->toContain('<xs:anyAttribute namespace="##other" processContents="strict"/>');
        });

        test('generates complex type with attribute group reference', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->attributeGroup('CommonAttrs')
                ->attribute('id', XsdType::Int);
            $wsdl->complexType('Person')
                ->element('name', XsdType::String)
                ->attributeGroup('CommonAttrs');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:attributeGroup ref="tns:CommonAttrs"/>');
        });
    });

    describe('Schema Imports and Includes', function (): void {
        test('generates schema import with namespace and location', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->schemaImport('http://schemas.example.com/common', 'common.xsd');
            $wsdl->complexType('Test')->element('value', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:import namespace="http://schemas.example.com/common" schemaLocation="common.xsd"/>');
        });

        test('generates schema import without location', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->schemaImport('http://schemas.example.com/common');
            $wsdl->complexType('Test')->element('value', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:import namespace="http://schemas.example.com/common"/>');
            expect($xml)->not->toContain('schemaLocation');
        });

        test('generates schema include', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->schemaInclude('types.xsd');
            $wsdl->complexType('Test')->element('value', XsdType::String);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:include schemaLocation="types.xsd"/>');
        });
    });

    describe('Compositors', function (): void {
        test('generates complex type with choice compositor', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('Payment')
                ->choice()
                ->element('creditCard', XsdType::String)
                ->element('bankTransfer', XsdType::String)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:choice>');
            expect($xml)->toContain('<xs:element name="creditCard" type="xs:string"/>');
            expect($xml)->toContain('<xs:element name="bankTransfer" type="xs:string"/>');
        });

        test('generates complex type with choice compositor with minOccurs and maxOccurs', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('MultiPayment')
                ->choice()->minOccurs(0)->maxOccurs(-1)
                ->element('method1', XsdType::String)
                ->element('method2', XsdType::String)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:choice minOccurs="0" maxOccurs="unbounded">');
        });

        test('generates complex type with all compositor', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('UnorderedFields')
                ->all()
                ->element('field1', XsdType::String)
                ->element('field2', XsdType::Int)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:all>');
            expect($xml)->toContain('<xs:element name="field1" type="xs:string"/>');
            expect($xml)->toContain('<xs:element name="field2" type="xs:int"/>');
        });

        test('generates complex type with any compositor', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('Extensible')
                ->element('knownField', XsdType::String)
                ->any()->namespace('##any')->processContents('lax');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:any namespace="##any" processContents="lax"/>');
        });

        test('generates complex type with any compositor with minOccurs and maxOccurs', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('MultiExtensible')
                ->element('knownField', XsdType::String)
                ->any()->namespace('##other')->processContents('skip')->minOccurs(0)->maxOccurs(-1);

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:any namespace="##other" processContents="skip" minOccurs="0" maxOccurs="unbounded"/>');
        });
    });

    describe('Simple Content', function (): void {
        test('generates complex type with simple content extension', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->complexType('Price')
                ->simpleContent()
                ->extension(XsdType::Decimal)
                ->attribute('currency', XsdType::String)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:complexType name="Price">');
            expect($xml)->toContain('<xs:simpleContent>');
            expect($xml)->toContain('<xs:extension base="xs:decimal">');
            expect($xml)->toContain('<xs:attribute name="currency" type="xs:string"/>');
        });

        test('generates complex type with simple content restriction', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('TypeService', 'http://example.com/types');
            $wsdl->simpleType('BasePrice')
                ->base(XsdType::Decimal);
            $wsdl->complexType('RestrictedPrice')
                ->simpleContent()
                ->restriction('BasePrice')
                ->attribute('currency', XsdType::String)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:complexType name="RestrictedPrice">');
            expect($xml)->toContain('<xs:simpleContent>');
            expect($xml)->toContain('<xs:restriction base="tns:BasePrice">');
            expect($xml)->toContain('<xs:attribute name="currency" type="xs:string"/>');
        });
    });

    describe('Interfaces', function (): void {
        test('generates interface with operations', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('UserService', 'http://example.com/users');
            $wsdl->interface('UserInterface')
                ->operation('GetUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('GetUserRequest')
                ->output('GetUserResponse')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:interface name="UserInterface">');
            expect($xml)->toContain('<wsdl:operation name="GetUser"');
            expect($xml)->toContain('pattern="http://www.w3.org/ns/wsdl/in-out"');
            expect($xml)->toContain('<wsdl:input element="tns:GetUserRequest"/>');
            expect($xml)->toContain('<wsdl:output element="tns:GetUserResponse"/>');
        });

        test('generates interface extending another interface', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('UserService', 'http://example.com/users');
            $wsdl->interface('BaseInterface')
                ->operation('GetBase')
                ->end()
                ->end();
            $wsdl->interface('ExtendedInterface')
                ->extends('BaseInterface')
                ->operation('GetExtended')
                ->end()
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:interface name="ExtendedInterface" extends="tns:BaseInterface">');
        });

        test('generates interface with multiple extends', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('MultiInterface')
                ->extends('Interface1')
                ->extends('Interface2')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('extends="tns:Interface1 tns:Interface2"');
        });

        test('generates interface with faults', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('UserService', 'http://example.com/users');
            $wsdl->interface('UserInterface')
                ->fault('InvalidUserFault', 'InvalidUserError')
                ->fault('DatabaseFault', 'DatabaseError')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:fault name="InvalidUserFault" element="tns:InvalidUserError"/>');
            expect($xml)->toContain('<wsdl:fault name="DatabaseFault" element="tns:DatabaseError"/>');
        });

        test('generates interface with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('UserService', 'http://example.com/users');
            $wsdl->interface('UserInterface')
                ->documentation('User management interface', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('User management interface');
        });
    });

    describe('Interface Operations', function (): void {
        test('generates operation with InOut pattern', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('InOutOp')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('Request')
                ->output('Response')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('pattern="http://www.w3.org/ns/wsdl/in-out"');
        });

        test('generates operation with InOnly pattern', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('InOnlyOp')
                ->pattern(MessageExchangePattern::InOnly->value)
                ->input('Request')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('pattern="http://www.w3.org/ns/wsdl/in-only"');
        });

        test('generates operation with RobustInOnly pattern', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('RobustOp')
                ->pattern(MessageExchangePattern::RobustInOnly->value)
                ->input('Request')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('pattern="http://www.w3.org/ns/wsdl/robust-in-only"');
        });

        test('generates safe operation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('SafeOp')
                ->safe(true)
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('safe="true"');
        });

        test('generates operation with style URI', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('StyledOp')
                ->style('http://www.w3.org/ns/wsdl/style/iri')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('style="http://www.w3.org/ns/wsdl/style/iri"');
        });

        test('generates operation with fault references', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->fault('Fault1', 'Error1')
                ->fault('Fault2', 'Error2')
                ->operation('FaultyOp')
                ->input('Request')
                ->output('Response')
                ->fault('Fault1')
                ->fault('Fault2')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:outfault ref="tns:Fault1"/>');
            expect($xml)->toContain('<wsdl:outfault ref="tns:Fault2"/>');
        });

        test('generates operation with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('DocumentedOp')
                ->documentation('This operation does something', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('This operation does something');
        });
    });

    describe('Bindings', function (): void {
        test('generates SOAP binding', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('GetData')
                ->end()
                ->end();
            $wsdl->binding('SoapBinding', 'Interface')
                ->type('http://www.w3.org/ns/wsdl/soap');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:binding name="SoapBinding" interface="tns:Interface"');
            expect($xml)->toContain('type="http://www.w3.org/ns/wsdl/soap"');
        });

        test('generates HTTP binding', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')->end();
            $wsdl->binding('HttpBinding', 'Interface')
                ->type('http://www.w3.org/ns/wsdl/http');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('type="http://www.w3.org/ns/wsdl/http"');
        });

        test('generates binding operation with SOAP action', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('GetData')
                ->end()
                ->end();
            $wsdl->binding('Binding', 'Interface')
                ->operation('GetData')
                ->soapAction('http://example.com/GetData')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('ref="tns:GetData"');
            expect($xml)->toContain('soapAction="http://example.com/GetData"');
        });

        test('generates binding with faults', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->fault('ErrorFault', 'Error')
                ->end();
            $wsdl->binding('Binding', 'Interface')
                ->fault('ErrorFault')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:fault ref="tns:ErrorFault"/>');
        });

        test('generates binding with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')->end();
            $wsdl->binding('Binding', 'Interface')
                ->documentation('SOAP 1.2 binding', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('SOAP 1.2 binding');
        });

        test('generates binding operation with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->operation('GetData')
                ->end()
                ->end();
            $wsdl->binding('Binding', 'Interface')
                ->operation('GetData')
                ->documentation('Get data operation binding', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('Get data operation binding');
        });

        test('generates binding fault with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('Service', 'http://example.com/');
            $wsdl->interface('Interface')
                ->fault('ErrorFault', 'Error')
                ->end();
            $wsdl->binding('Binding', 'Interface')
                ->fault('ErrorFault')
                ->documentation('Error fault binding', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('Error fault binding');
        });
    });

    describe('Services', function (): void {
        test('generates service with single endpoint', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $wsdl->interface('Interface')->end();
            $wsdl->binding('Binding', 'Interface')->end();
            $wsdl->service('UserService')
                ->interface('Interface')
                ->endpoint('MainEndpoint', 'Binding', 'http://example.com/soap')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:service name="UserService" interface="tns:Interface">');
            expect($xml)->toContain('<wsdl:endpoint name="MainEndpoint" binding="tns:Binding" address="http://example.com/soap"/>');
        });

        test('generates service with multiple endpoints', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $wsdl->interface('Interface')->end();
            $wsdl->binding('SoapBinding', 'Interface')->end();
            $wsdl->binding('HttpBinding', 'Interface')->end();
            $wsdl->service('UserService')
                ->endpoint('SoapEndpoint', 'SoapBinding', 'http://example.com/soap')
                ->end()
                ->endpoint('HttpEndpoint', 'HttpBinding', 'http://example.com/http')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:endpoint name="SoapEndpoint"');
            expect($xml)->toContain('<wsdl:endpoint name="HttpEndpoint"');
        });

        test('generates service with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $wsdl->service('UserService')
                ->documentation('User management service', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('User management service');
        });

        test('generates endpoint with documentation', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('MyService', 'http://example.com/');
            $wsdl->binding('Binding', 'Interface')->end();
            $wsdl->service('Service')
                ->endpoint('Endpoint', 'Binding', 'http://example.com/')
                ->documentation('Primary endpoint', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<wsdl:documentation');
            expect($xml)->toContain('Primary endpoint');
        });
    });

    describe('Complete WSDL Documents', function (): void {
        test('generates complete WSDL with all features', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('CompleteService', 'http://example.com/complete')
                ->documentation('Complete WSDL 2.0 demonstration', 'en')
                ->schemaImport('http://schemas.example.com/common', 'common.xsd')
                ->simpleType('Status')
                ->base(XsdType::String)
                ->enumeration('active')
                ->enumeration('inactive')
                ->end()
                ->complexType('User')
                ->element('id', XsdType::Int)
                ->element('name', XsdType::String)
                ->element('email', XsdType::String)
                ->element('status', 'tns:Status')
                ->end()
                ->interface('UserInterface')
                ->fault('InvalidUserFault', 'InvalidUserError')
                ->operation('GetUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('GetUserRequest')
                ->output('GetUserResponse')
                ->fault('InvalidUserFault')
                ->safe(true)
                ->documentation('Retrieve user by ID', 'en')
                ->end()
                ->operation('CreateUser')
                ->pattern(MessageExchangePattern::InOut->value)
                ->input('CreateUserRequest')
                ->output('CreateUserResponse')
                ->documentation('Create new user', 'en')
                ->end()
                ->documentation('User management interface', 'en')
                ->end()
                ->binding('UserBinding', 'UserInterface')
                ->type('http://www.w3.org/ns/wsdl/soap')
                ->operation('GetUser')
                ->soapAction('http://example.com/GetUser')
                ->end()
                ->operation('CreateUser')
                ->soapAction('http://example.com/CreateUser')
                ->end()
                ->documentation('SOAP 1.2 binding', 'en')
                ->end()
                ->service('UserService')
                ->interface('UserInterface')
                ->endpoint('UserEndpoint', 'UserBinding', 'http://example.com/soap')
                ->documentation('Primary SOAP endpoint', 'en')
                ->end()
                ->documentation('User management service', 'en')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<?xml version="1.0" encoding="UTF-8"?>');
            expect($xml)->toContain('<wsdl:description');
            expect($xml)->toContain('targetNamespace="http://example.com/complete"');
            expect($xml)->toContain('<wsdl:types>');
            expect($xml)->toContain('<xs:schema');
            expect($xml)->toContain('<xs:import');
            expect($xml)->toContain('<xs:simpleType name="Status">');
            expect($xml)->toContain('<xs:complexType name="User">');
            expect($xml)->toContain('<wsdl:interface name="UserInterface">');
            expect($xml)->toContain('<wsdl:operation name="GetUser"');
            expect($xml)->toContain('<wsdl:binding name="UserBinding"');
            expect($xml)->toContain('<wsdl:service name="UserService"');
            expect($xml)->toContain('<wsdl:endpoint name="UserEndpoint"');
        });

        test('generates WSDL with complex types using all compositor features', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('CompositorService', 'http://example.com/compositors');
            $type = $wsdl->complexType('Container');
            $type->element('header', XsdType::String);
            $type->choice()->minOccurs(1)->maxOccurs(-1)
                ->element('option1', XsdType::String)
                ->element('option2', XsdType::Int)
                ->end();
            $type->any()->namespace('##other')->processContents('lax')->minOccurs(0)->maxOccurs(-1);
            $type->attribute('id', XsdType::Int)->use('required');
            $type->anyAttribute()->namespace('##any')->processContents('skip');

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:choice minOccurs="1" maxOccurs="unbounded">');
            expect($xml)->toContain('<xs:any namespace="##other" processContents="lax" minOccurs="0" maxOccurs="unbounded"/>');
            expect($xml)->toContain('<xs:anyAttribute namespace="##any" processContents="skip"/>');
        });

        test('generates WSDL with element and attribute groups', function (): void {
            // Arrange
            $wsdl = Wsdl2::create('GroupService', 'http://example.com/groups')
                ->elementGroup('AddressFields')
                ->element('street', XsdType::String)
                ->element('city', XsdType::String)
                ->element('country', XsdType::String)
                ->end();
            $attrGroup = $wsdl->attributeGroup('AuditAttributes');
            $attrGroup->attribute('createdAt', XsdType::DateTime);
            $attrGroup->attribute('updatedAt', XsdType::DateTime);
            $wsdl->complexType('Contact')
                ->element('name', XsdType::String)
                ->group('AddressFields')
                ->attributeGroup('AuditAttributes')
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toMatchSnapshot();
            expect($xml)->toContain('<xs:group name="AddressFields">');
            expect($xml)->toContain('<xs:attributeGroup name="AuditAttributes">');
            expect($xml)->toContain('<xs:group ref="tns:AddressFields"/>');
            expect($xml)->toContain('<xs:attributeGroup ref="tns:AuditAttributes"/>');
        });
    });
});
