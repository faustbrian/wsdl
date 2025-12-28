<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Imports\SchemaRedefine;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\Element;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;

describe('SchemaRedefine', function (): void {
    describe('Happy Paths', function (): void {
        test('creates schema redefine with location via Wsdl::redefine()', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Assert
            expect($redefine)->toBeInstanceOf(SchemaRedefine::class)
                ->and($redefine->getSchemaLocation())->toBe('http://external.example.com/schema.xsd');
        });

        test('adds simple type to redefine and returns SimpleType instance', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $simpleType = $redefine->simpleType('StatusCodeType');

            // Assert
            expect($simpleType)->toBeInstanceOf(SimpleType::class)
                ->and($simpleType->getName())->toBe('StatusCodeType');
        });

        test('adds complex type to redefine and returns ComplexType instance', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $complexType = $redefine->complexType('PersonType');

            // Assert
            expect($complexType)->toBeInstanceOf(ComplexType::class)
                ->and($complexType->getName())->toBe('PersonType');
        });

        test('adds attribute group to redefine and returns AttributeGroup instance', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $attributeGroup = $redefine->attributeGroup('CommonAttributes');

            // Assert
            expect($attributeGroup)->toBeInstanceOf(AttributeGroup::class)
                ->and($attributeGroup->getName())->toBe('CommonAttributes');
        });

        test('adds element group to redefine and returns ElementGroup instance', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $group = $redefine->group('AddressElements');

            // Assert
            expect($group)->toBeInstanceOf(ElementGroup::class)
                ->and($group->getName())->toBe('AddressElements');
        });

        test('retrieves all simple types from redefine', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');
            $redefine->simpleType('Type1');
            $redefine->simpleType('Type2');

            // Act
            $simpleTypes = $redefine->getSimpleTypes();

            // Assert
            expect($simpleTypes)->toHaveCount(2)
                ->and($simpleTypes['Type1'])->toBeInstanceOf(SimpleType::class)
                ->and($simpleTypes['Type2'])->toBeInstanceOf(SimpleType::class)
                ->and($simpleTypes['Type1']->getName())->toBe('Type1')
                ->and($simpleTypes['Type2']->getName())->toBe('Type2');
        });

        test('retrieves all complex types from redefine', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');
            $redefine->complexType('PersonType');
            $redefine->complexType('AddressType');

            // Act
            $complexTypes = $redefine->getComplexTypes();

            // Assert
            expect($complexTypes)->toHaveCount(2)
                ->and($complexTypes['PersonType'])->toBeInstanceOf(ComplexType::class)
                ->and($complexTypes['AddressType'])->toBeInstanceOf(ComplexType::class)
                ->and($complexTypes['PersonType']->getName())->toBe('PersonType')
                ->and($complexTypes['AddressType']->getName())->toBe('AddressType');
        });

        test('retrieves all attribute groups from redefine', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');
            $redefine->attributeGroup('Group1');
            $redefine->attributeGroup('Group2');

            // Act
            $attributeGroups = $redefine->getAttributeGroups();

            // Assert
            expect($attributeGroups)->toHaveCount(2)
                ->and($attributeGroups['Group1'])->toBeInstanceOf(AttributeGroup::class)
                ->and($attributeGroups['Group2'])->toBeInstanceOf(AttributeGroup::class)
                ->and($attributeGroups['Group1']->getName())->toBe('Group1')
                ->and($attributeGroups['Group2']->getName())->toBe('Group2');
        });

        test('retrieves all element groups from redefine', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');
            $redefine->group('Elements1');
            $redefine->group('Elements2');

            // Act
            $groups = $redefine->getGroups();

            // Assert
            expect($groups)->toHaveCount(2)
                ->and($groups['Elements1'])->toBeInstanceOf(ElementGroup::class)
                ->and($groups['Elements2'])->toBeInstanceOf(ElementGroup::class)
                ->and($groups['Elements1']->getName())->toBe('Elements1')
                ->and($groups['Elements2']->getName())->toBe('Elements2');
        });

        test('end returns parent Wsdl instance', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $result = $redefine->end();

            // Assert
            expect($result)->toBe($wsdl);
        });

        test('stores redefine in Wsdl getRedefines array', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $redefines = $wsdl->getRedefines();

            // Assert
            expect($redefines)->toHaveCount(1)
                ->and($redefines[0])->toBe($redefine);
        });

        test('creates multiple redefines with different schema locations', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $redefine1 = $wsdl->redefine('http://schema1.example.com/types.xsd');
            $redefine2 = $wsdl->redefine('http://schema2.example.com/types.xsd');

            // Assert
            $redefines = $wsdl->getRedefines();
            expect($redefines)->toHaveCount(2)
                ->and($redefines[0]->getSchemaLocation())->toBe('http://schema1.example.com/types.xsd')
                ->and($redefines[1]->getSchemaLocation())->toBe('http://schema2.example.com/types.xsd');
        });

        test('chains redefine operations using fluent interface', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');
            $redefine->simpleType('StatusType')
                ->enumeration('active', 'inactive')
                ->end();
            $redefine->complexType('PersonType')
                ->element('name', XsdType::String)
                ->end();

            // Assert
            expect($redefine->getSimpleTypes())->toHaveCount(1)
                ->and($redefine->getComplexTypes())->toHaveCount(1);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns empty arrays when no types are defined in redefine', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act & Assert
            expect($redefine->getSimpleTypes())->toBeEmpty()
                ->and($redefine->getComplexTypes())->toBeEmpty()
                ->and($redefine->getAttributeGroups())->toBeEmpty()
                ->and($redefine->getGroups())->toBeEmpty();
        });

        test('overwrites simple type when adding same name twice', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $type1 = $redefine->simpleType('DuplicateType');
            $type2 = $redefine->simpleType('DuplicateType');

            // Assert
            $simpleTypes = $redefine->getSimpleTypes();
            expect($simpleTypes)->toHaveCount(1)
                ->and($simpleTypes['DuplicateType'])->toBe($type2);
        });

        test('overwrites complex type when adding same name twice', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $redefine = $wsdl->redefine('http://external.example.com/schema.xsd');

            // Act
            $type1 = $redefine->complexType('DuplicateType');
            $type2 = $redefine->complexType('DuplicateType');

            // Assert
            $complexTypes = $redefine->getComplexTypes();
            expect($complexTypes)->toHaveCount(1)
                ->and($complexTypes['DuplicateType'])->toBe($type2);
        });
    });
});

describe('ComplexType block and final attributes', function (): void {
    describe('Happy Paths', function (): void {
        test('sets block attribute with #all value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('RestrictedType')
                ->block('#all');

            // Assert
            expect($type->getBlock())->toBe('#all');
        });

        test('sets block attribute with extension value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('RestrictedType')
                ->block('extension');

            // Assert
            expect($type->getBlock())->toBe('extension');
        });

        test('sets block attribute with restriction value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('RestrictedType')
                ->block('restriction');

            // Assert
            expect($type->getBlock())->toBe('restriction');
        });

        test('sets block attribute with space-separated values', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('RestrictedType')
                ->block('extension restriction');

            // Assert
            expect($type->getBlock())->toBe('extension restriction');
        });

        test('sets final attribute with #all value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('FinalType')
                ->final('#all');

            // Assert
            expect($type->getFinal())->toBe('#all');
        });

        test('sets final attribute with extension value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('FinalType')
                ->final('extension');

            // Assert
            expect($type->getFinal())->toBe('extension');
        });

        test('sets final attribute with restriction value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('FinalType')
                ->final('restriction');

            // Assert
            expect($type->getFinal())->toBe('restriction');
        });

        test('sets final attribute with space-separated values', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('FinalType')
                ->final('extension restriction');

            // Assert
            expect($type->getFinal())->toBe('extension restriction');
        });

        test('returns null for block attribute by default', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('UnrestrictedType');

            // Assert
            expect($type->getBlock())->toBeNull();
        });

        test('returns null for final attribute by default', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('UnfinalizedType');

            // Assert
            expect($type->getFinal())->toBeNull();
        });

        test('chains block and final methods in fluent interface', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('ConstrainedType')
                ->block('extension')
                ->final('restriction')
                ->element('id', XsdType::Int);

            // Assert
            expect($type->getBlock())->toBe('extension')
                ->and($type->getFinal())->toBe('restriction')
                ->and($type->getElements())->toHaveCount(1);
        });
    });

    describe('Edge Cases', function (): void {
        test('sets block attribute to null explicitly', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('NullableBlockType')
                ->block('extension')
                ->block(null);

            // Assert
            expect($type->getBlock())->toBeNull();
        });

        test('sets final attribute to null explicitly', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('NullableFinalType')
                ->final('restriction')
                ->final(null);

            // Assert
            expect($type->getFinal())->toBeNull();
        });

        test('overwrites block attribute when set multiple times', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('OverwrittenBlockType')
                ->block('extension')
                ->block('restriction');

            // Assert
            expect($type->getBlock())->toBe('restriction');
        });

        test('overwrites final attribute when set multiple times', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('OverwrittenFinalType')
                ->final('extension')
                ->final('#all');

            // Assert
            expect($type->getFinal())->toBe('#all');
        });
    });
});

describe('SimpleType final attribute', function (): void {
    describe('Happy Paths', function (): void {
        test('sets final attribute with #all value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('FinalSimpleType')
                ->final('#all');

            // Assert
            expect($type->getFinal())->toBe('#all');
        });

        test('sets final attribute with list value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('FinalSimpleType')
                ->final('list');

            // Assert
            expect($type->getFinal())->toBe('list');
        });

        test('sets final attribute with union value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('FinalSimpleType')
                ->final('union');

            // Assert
            expect($type->getFinal())->toBe('union');
        });

        test('sets final attribute with restriction value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('FinalSimpleType')
                ->final('restriction');

            // Assert
            expect($type->getFinal())->toBe('restriction');
        });

        test('sets final attribute with space-separated values', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('FinalSimpleType')
                ->final('list union restriction');

            // Assert
            expect($type->getFinal())->toBe('list union restriction');
        });

        test('returns null for final attribute by default', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('UnfinalizedSimpleType');

            // Assert
            expect($type->getFinal())->toBeNull();
        });

        test('chains final method with other restrictions in fluent interface', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('ConstrainedSimpleType')
                ->base(XsdType::String)
                ->maxLength(50)
                ->final('restriction');

            // Assert
            expect($type->getFinal())->toBe('restriction')
                ->and($type->getMaxLength())->toBe(50);
        });
    });

    describe('Edge Cases', function (): void {
        test('sets final attribute to null explicitly', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('NullableFinalSimpleType')
                ->final('restriction')
                ->final(null);

            // Assert
            expect($type->getFinal())->toBeNull();
        });

        test('overwrites final attribute when set multiple times', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->simpleType('OverwrittenFinalSimpleType')
                ->final('list')
                ->final('#all');

            // Assert
            expect($type->getFinal())->toBe('#all');
        });
    });
});

describe('Element substitutionGroup and block attributes', function (): void {
    describe('Happy Paths', function (): void {
        test('creates element with substitutionGroup via ComplexType::element()', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('substitutableElement', XsdType::String, false, null, null, 'tns:baseElement');

            // Assert
            $elements = $type->getElements();
            expect($elements)->toHaveCount(1)
                ->and($elements[0]->substitutionGroup)->toBe('tns:baseElement');
        });

        test('creates element with block attribute via ComplexType::element()', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('blockedElement', XsdType::String, false, null, null, null, 'extension');

            // Assert
            $elements = $type->getElements();
            expect($elements)->toHaveCount(1)
                ->and($elements[0]->block)->toBe('extension');
        });

        test('creates element with both substitutionGroup and block attributes', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('fullElement', XsdType::String, false, null, null, 'tns:baseElement', 'restriction');

            // Assert
            $elements = $type->getElements();
            expect($elements)->toHaveCount(1)
                ->and($elements[0]->substitutionGroup)->toBe('tns:baseElement')
                ->and($elements[0]->block)->toBe('restriction');
        });

        test('accesses substitutionGroup property from Element instance', function (): void {
            // Arrange
            $element = new Element('test', 'string', false, null, null, 'tns:baseElement', null);

            // Act & Assert
            expect($element->substitutionGroup)->toBe('tns:baseElement');
        });

        test('accesses block property from Element instance', function (): void {
            // Arrange
            $element = new Element('test', 'string', false, null, null, null, 'extension');

            // Act & Assert
            expect($element->block)->toBe('extension');
        });

        test('creates element with substitutionGroup as null by default', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('normalElement', XsdType::String);

            // Assert
            $elements = $type->getElements();
            expect($elements[0]->substitutionGroup)->toBeNull();
        });

        test('creates element with block as null by default', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('normalElement', XsdType::String);

            // Assert
            $elements = $type->getElements();
            expect($elements[0]->block)->toBeNull();
        });

        test('creates element with block value #all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('blockedAllElement', XsdType::String, false, null, null, null, '#all');

            // Assert
            $elements = $type->getElements();
            expect($elements[0]->block)->toBe('#all');
        });

        test('creates element with block value space-separated', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('blockedElement', XsdType::String, false, null, null, null, 'extension restriction');

            // Assert
            $elements = $type->getElements();
            expect($elements[0]->block)->toBe('extension restriction');
        });
    });

    describe('Edge Cases', function (): void {
        test('creates multiple elements with different substitutionGroup values', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('element1', XsdType::String, false, null, null, 'tns:base1')
                ->element('element2', XsdType::String, false, null, null, 'tns:base2')
                ->element('element3', XsdType::String);

            // Assert
            $elements = $type->getElements();
            expect($elements)->toHaveCount(3)
                ->and($elements[0]->substitutionGroup)->toBe('tns:base1')
                ->and($elements[1]->substitutionGroup)->toBe('tns:base2')
                ->and($elements[2]->substitutionGroup)->toBeNull();
        });

        test('creates multiple elements with different block values', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $type = $wsdl->complexType('TestType')
                ->element('element1', XsdType::String, false, null, null, null, 'extension')
                ->element('element2', XsdType::String, false, null, null, null, 'restriction')
                ->element('element3', XsdType::String);

            // Assert
            $elements = $type->getElements();
            expect($elements)->toHaveCount(3)
                ->and($elements[0]->block)->toBe('extension')
                ->and($elements[1]->block)->toBe('restriction')
                ->and($elements[2]->block)->toBeNull();
        });

        test('Element instance is readonly and immutable', function (): void {
            // Arrange
            $element = new Element('test', 'string', false, null, null, 'tns:base', 'extension');

            // Act & Assert
            expect($element->name)->toBe('test')
                ->and($element->type)->toBe('string')
                ->and($element->substitutionGroup)->toBe('tns:base')
                ->and($element->block)->toBe('extension');
        });
    });
});
