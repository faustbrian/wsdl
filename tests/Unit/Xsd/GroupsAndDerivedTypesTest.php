<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Compositors\All;
use Cline\WsdlBuilder\Xsd\Compositors\Choice;
use Cline\WsdlBuilder\Xsd\DerivedTypes\ListType;
use Cline\WsdlBuilder\Xsd\DerivedTypes\UnionType;
use Cline\WsdlBuilder\Xsd\Groups\ElementGroup;

describe('ElementGroup', function (): void {
    describe('Happy Paths', function (): void {
        test('creates element group with name via fluent API', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('AddressGroup');

            expect($group)->toBeInstanceOf(ElementGroup::class)
                ->and($group->getName())->toBe('AddressGroup');
        });

        test('adds element with XsdType enum to group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('AddressGroup')
                ->element('street', XsdType::String);

            $elements = $group->getElements();

            expect($elements)->toHaveCount(1)
                ->and($elements[0]['name'])->toBe('street')
                ->and($elements[0]['type'])->toBe('string')
                ->and($elements[0]['nullable'])->toBeFalse()
                ->and($elements[0]['minOccurs'])->toBeNull()
                ->and($elements[0]['maxOccurs'])->toBeNull();
        });

        test('adds element with string type to group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('AddressGroup')
                ->element('location', 'tns:LocationType');

            $elements = $group->getElements();

            expect($elements[0]['type'])->toBe('tns:LocationType');
        });

        test('adds nullable element to group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('AddressGroup')
                ->element('suite', XsdType::String, true);

            $elements = $group->getElements();

            expect($elements[0]['nullable'])->toBeTrue();
        });

        test('adds element with cardinality constraints to group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('ContactGroup')
                ->element('phone', XsdType::String, false, 1, 5);

            $elements = $group->getElements();

            expect($elements[0]['minOccurs'])->toBe(1)
                ->and($elements[0]['maxOccurs'])->toBe(5);
        });

        test('adds multiple elements to group with chaining', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('PersonGroup')
                ->element('firstName', XsdType::String)
                ->element('lastName', XsdType::String)
                ->element('age', XsdType::Int);

            $elements = $group->getElements();

            expect($elements)->toHaveCount(3)
                ->and($elements[0]['name'])->toBe('firstName')
                ->and($elements[1]['name'])->toBe('lastName')
                ->and($elements[2]['name'])->toBe('age');
        });

        test('creates choice compositor and returns Choice instance', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('PaymentGroup');

            $choice = $group->choice();

            expect($choice)->toBeInstanceOf(Choice::class)
                ->and($group->getChoice())->toBe($choice);
        });

        test('creates all compositor and returns All instance', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('ConfigGroup');

            $all = $group->all();

            expect($all)->toBeInstanceOf(All::class)
                ->and($group->getAll())->toBe($all);
        });

        test('choice compositor allows adding elements with fluent interface', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('PaymentGroup');

            $choice = $group->choice()
                ->element('creditCard', 'tns:CreditCardType')
                ->element('bankTransfer', 'tns:BankTransferType');

            expect($choice->getElements())->toHaveCount(2)
                ->and($choice->getElements()[0]->name)->toBe('creditCard')
                ->and($choice->getElements()[1]->name)->toBe('bankTransfer');
        });

        test('all compositor allows adding elements with fluent interface', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('ConfigGroup');

            $all = $group->all()
                ->element('debug', XsdType::Boolean)
                ->element('timeout', XsdType::Int);

            expect($all->getElements())->toHaveCount(2)
                ->and($all->getElements()[0]->name)->toBe('debug')
                ->and($all->getElements()[1]->name)->toBe('timeout');
        });

        test('choice end returns parent element group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('PaymentGroup');

            $result = $group->choice()
                ->element('creditCard', 'tns:CreditCardType')
                ->end();

            expect($result)->toBe($group);
        });

        test('all end returns parent element group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('ConfigGroup');

            $result = $group->all()
                ->element('debug', XsdType::Boolean)
                ->end();

            expect($result)->toBe($group);
        });

        test('end returns parent wsdl instance', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->elementGroup('TestGroup')->end();

            expect($result)->toBe($wsdl);
        });

        test('getChoice returns null when no choice compositor exists', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('TestGroup');

            expect($group->getChoice())->toBeNull();
        });

        test('getAll returns null when no all compositor exists', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('TestGroup');

            expect($group->getAll())->toBeNull();
        });

        test('stores element group in wsdl element groups collection', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('ContactGroup');

            $groups = $wsdl->getElementGroups();

            expect($groups)->toHaveCount(1)
                ->and($groups['ContactGroup'])->toBe($group);
        });

        test('fluent interface chains element group definition with compositor', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $group = $wsdl->elementGroup('OrderGroup')
                ->element('orderId', XsdType::String)
                ->choice()
                ->element('rush', XsdType::Boolean)
                ->element('standard', XsdType::Boolean)
                ->end();

            expect($group)->toBeInstanceOf(ElementGroup::class)
                ->and($group->getElements())->toHaveCount(1)
                ->and($group->getChoice())->toBeInstanceOf(Choice::class)
                ->and($group->getChoice()->getElements())->toHaveCount(2);
        });
    });

    describe('Edge Cases', function (): void {
        test('allows element with zero minOccurs and unbounded maxOccurs', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('TagsGroup')
                ->element('tag', XsdType::String, false, 0, -1);

            $elements = $group->getElements();

            expect($elements[0]['minOccurs'])->toBe(0)
                ->and($elements[0]['maxOccurs'])->toBe(-1);
        });

        test('handles empty element group with no elements or compositors', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('EmptyGroup');

            expect($group->getElements())->toBeEmpty()
                ->and($group->getChoice())->toBeNull()
                ->and($group->getAll())->toBeNull();
        });

        test('allows creating multiple element groups with different names', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group1 = $wsdl->elementGroup('AddressGroup');
            $group2 = $wsdl->elementGroup('ContactGroup');

            $groups = $wsdl->getElementGroups();

            expect($groups)->toHaveCount(2)
                ->and($groups['AddressGroup'])->toBe($group1)
                ->and($groups['ContactGroup'])->toBe($group2);
        });
    });

    describe('Sad Paths', function (): void {
        test('throws InvalidArgumentException when all compositor element has minOccurs greater than 1', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('ConfigGroup');

            expect(fn (): All => $group->all()->element('setting', XsdType::String, false, 2, 1))
                ->toThrow(InvalidArgumentException::class, 'Elements in <all> can only have minOccurs 0 or 1');
        });

        test('throws InvalidArgumentException when all compositor element has maxOccurs not equal to 1', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->elementGroup('ConfigGroup');

            expect(fn (): All => $group->all()->element('setting', XsdType::String, false, 0, 5))
                ->toThrow(InvalidArgumentException::class, 'Elements in <all> can only have maxOccurs 1');
        });
    });
});

describe('ListType', function (): void {
    describe('Happy Paths', function (): void {
        test('creates list type with name via fluent API', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('IntegerList');

            expect($list)->toBeInstanceOf(ListType::class)
                ->and($list->getName())->toBe('IntegerList');
        });

        test('sets item type with XsdType enum', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('IntegerList')
                ->itemType(XsdType::Int);

            expect($list->getItemType())->toBe('int');
        });

        test('sets item type with string type reference', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('ProductCodeList')
                ->itemType('tns:ProductCodeType');

            expect($list->getItemType())->toBe('tns:ProductCodeType');
        });

        test('defaults to xsd:string item type when not specified', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('StringList');

            expect($list->getItemType())->toBe('string');
        });

        test('sets minimum length restriction for list', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('TagList')
                ->minLength(1);

            expect($list->getMinLength())->toBe(1);
        });

        test('sets maximum length restriction for list', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('TagList')
                ->maxLength(10);

            expect($list->getMaxLength())->toBe(10);
        });

        test('sets pattern restriction for list', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('FormattedList')
                ->pattern('[A-Z]+');

            expect($list->getPattern())->toBe('[A-Z]+');
        });

        test('adds enumeration values to list type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('ColorList')
                ->enumeration('red', 'green', 'blue');

            expect($list->getEnumeration())->toBe(['red', 'green', 'blue']);
        });

        test('chains multiple enumeration calls together', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('ColorList')
                ->enumeration('red', 'green')
                ->enumeration('blue', 'yellow');

            expect($list->getEnumeration())->toBe(['red', 'green', 'blue', 'yellow']);
        });

        test('end returns parent wsdl instance', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->listType('TestList')->end();

            expect($result)->toBe($wsdl);
        });

        test('stores list type in wsdl list types collection', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('IntegerList');

            $lists = $wsdl->getListTypes();

            expect($lists)->toHaveCount(1)
                ->and($lists['IntegerList'])->toBe($list);
        });

        test('fluent interface chains all list type methods', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $list = $wsdl->listType('ConstrainedList')
                ->itemType(XsdType::Int)
                ->minLength(2)
                ->maxLength(10)
                ->pattern('[0-9]+')
                ->enumeration('1', '2', '3');

            expect($list)
                ->toBeInstanceOf(ListType::class)
                ->and($list->getItemType())->toBe('int')
                ->and($list->getMinLength())->toBe(2)
                ->and($list->getMaxLength())->toBe(10)
                ->and($list->getPattern())->toBe('[0-9]+')
                ->and($list->getEnumeration())->toBe(['1', '2', '3']);
        });
    });

    describe('Edge Cases', function (): void {
        test('handles list type with zero minimum length', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('OptionalList')
                ->minLength(0);

            expect($list->getMinLength())->toBe(0);
        });

        test('handles empty enumeration list by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('UnconstrainedList');

            expect($list->getEnumeration())->toBeEmpty();
        });

        test('allows creating multiple list types with different names', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list1 = $wsdl->listType('IntList');
            $list2 = $wsdl->listType('StringList');

            $lists = $wsdl->getListTypes();

            expect($lists)->toHaveCount(2)
                ->and($lists['IntList'])->toBe($list1)
                ->and($lists['StringList'])->toBe($list2);
        });

        test('returns null for restrictions when not set', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $list = $wsdl->listType('SimpleList');

            expect($list->getMinLength())->toBeNull()
                ->and($list->getMaxLength())->toBeNull()
                ->and($list->getPattern())->toBeNull();
        });
    });
});

describe('UnionType', function (): void {
    describe('Happy Paths', function (): void {
        test('creates union type with name via fluent API', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('NumberOrString');

            expect($union)->toBeInstanceOf(UnionType::class)
                ->and($union->getName())->toBe('NumberOrString');
        });

        test('sets member types with XsdType enum values', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('IntOrString')
                ->memberTypes(XsdType::Int, XsdType::String);

            expect($union->getMemberTypes())->toBe(['int', 'string']);
        });

        test('sets member types with string type references', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('CustomUnion')
                ->memberTypes('tns:TypeA', 'tns:TypeB', 'tns:TypeC');

            expect($union->getMemberTypes())->toBe(['tns:TypeA', 'tns:TypeB', 'tns:TypeC']);
        });

        test('sets member types with mixed XsdType and string references', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('MixedUnion')
                ->memberTypes(XsdType::Int, 'tns:CustomType', XsdType::String);

            expect($union->getMemberTypes())->toBe(['int', 'tns:CustomType', 'string']);
        });

        test('end returns parent wsdl instance', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->unionType('TestUnion')->end();

            expect($result)->toBe($wsdl);
        });

        test('stores union type in wsdl union types collection', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('IntOrString');

            $unions = $wsdl->getUnionTypes();

            expect($unions)->toHaveCount(1)
                ->and($unions['IntOrString'])->toBe($union);
        });

        test('fluent interface chains union type definition', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            $result = $wsdl->unionType('MultiType')
                ->memberTypes(XsdType::Int, XsdType::String, XsdType::Boolean)
                ->end();

            expect($result)->toBe($wsdl)
                ->and($wsdl->getUnionTypes()['MultiType']->getMemberTypes())
                ->toBe(['int', 'string', 'boolean']);
        });

        test('allows single member type in union', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('SingleType')
                ->memberTypes(XsdType::Int);

            expect($union->getMemberTypes())->toBe(['int']);
        });
    });

    describe('Edge Cases', function (): void {
        test('defaults to empty member types array when not set', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('EmptyUnion');

            expect($union->getMemberTypes())->toBeEmpty();
        });

        test('allows creating multiple union types with different names', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union1 = $wsdl->unionType('Union1');
            $union2 = $wsdl->unionType('Union2');

            $unions = $wsdl->getUnionTypes();

            expect($unions)->toHaveCount(2)
                ->and($unions['Union1'])->toBe($union1)
                ->and($unions['Union2'])->toBe($union2);
        });

        test('handles union with many member types', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('ManyTypes')
                ->memberTypes(
                    XsdType::Int,
                    XsdType::String,
                    XsdType::Boolean,
                    XsdType::Float,
                    XsdType::Double,
                    'tns:CustomType',
                );

            expect($union->getMemberTypes())->toHaveCount(6);
        });

        test('replaces previous member types when memberTypes called again', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $union = $wsdl->unionType('ReplaceableUnion')
                ->memberTypes(XsdType::Int, XsdType::String)
                ->memberTypes(XsdType::Boolean, XsdType::Float);

            expect($union->getMemberTypes())->toBe(['boolean', 'float']);
        });
    });
});
