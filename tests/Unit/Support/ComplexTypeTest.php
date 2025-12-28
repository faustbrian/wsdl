<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;
use Cline\WsdlBuilder\Xsd\Types\Element;

describe('ComplexType', function (): void {
    describe('Happy Paths', function (): void {
        test('creates complex type with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType');

            expect($type->getName())->toBe('PersonType');
        });

        test('adds elements with XsdType enum', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType')
                ->element('id', XsdType::Int)
                ->element('name', XsdType::String);

            $elements = $type->getElements();

            expect($elements)->toHaveCount(2)
                ->and($elements[0]->name)->toBe('id')
                ->and($elements[0]->type)->toBe('int')
                ->and($elements[1]->name)->toBe('name')
                ->and($elements[1]->type)->toBe('string');
        });

        test('adds elements with string type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType')
                ->element('address', 'tns:AddressType');

            $elements = $type->getElements();

            expect($elements[0]->type)->toBe('tns:AddressType');
        });

        test('adds nullable element', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType')
                ->element('middleName', XsdType::String, true);

            $elements = $type->getElements();

            expect($elements[0]->nullable)->toBeTrue();
        });

        test('adds element with cardinality', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType')
                ->element('emails', XsdType::String, false, 0, -1);

            $elements = $type->getElements();

            expect($elements[0]->minOccurs)->toBe(0)
                ->and($elements[0]->maxOccurs)->toBe(-1);
        });

        test('extends another type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('EmployeeType')
                ->extends('PersonType')
                ->element('employeeId', XsdType::String);

            expect($type->getExtends())->toBe('PersonType');
        });

        test('marks type as abstract', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('AbstractEntity')
                ->abstract();

            expect($type->isAbstract())->toBeTrue();
        });

        test('marks type as not abstract by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('ConcreteEntity');

            expect($type->isAbstract())->toBeFalse();
        });

        test('end returns parent wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->complexType('TestType')->end();

            expect($result)->toBe($wsdl);
        });

        test('fluent interface chains methods', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('FullType')
                ->abstract()
                ->extends('BaseType')
                ->element('id', XsdType::Int)
                ->element('name', XsdType::String, true, 1, 1);

            expect($type)
                ->toBeInstanceOf(ComplexType::class)
                ->and($type->isAbstract())->toBeTrue()
                ->and($type->getExtends())->toBe('BaseType')
                ->and($type->getElements())->toHaveCount(2);
        });

        test('adds element group reference', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType')
                ->group('tns:AddressGroup')
                ->group('tns:ContactGroup');

            $groupRefs = $type->getGroupRefs();

            expect($groupRefs)->toHaveCount(2)
                ->and($groupRefs[0])->toBe('tns:AddressGroup')
                ->and($groupRefs[1])->toBe('tns:ContactGroup');
        });

        test('creates simple content for complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('AnnotatedString');

            $simpleContent = $type->simpleContent();

            expect($simpleContent)->toBeInstanceOf(\Cline\WsdlBuilder\Xsd\SimpleContent::class)
                ->and($type->getSimpleContent())->toBe($simpleContent);
        });

        test('enables mixed content with default true', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('MixedContentType')
                ->mixed();

            expect($type->isMixed())->toBeTrue();
        });

        test('enables mixed content with explicit true', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('MixedContentType')
                ->mixed(true);

            expect($type->isMixed())->toBeTrue();
        });

        test('disables mixed content with explicit false', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('MixedContentType')
                ->mixed(false);

            expect($type->isMixed())->toBeFalse();
        });
    });

    describe('Element Properties', function (): void {
        test('element is readonly', function (): void {
            $element = new Element('test', 'string', true, 0, 1);

            expect($element->name)->toBe('test')
                ->and($element->type)->toBe('string')
                ->and($element->nullable)->toBeTrue()
                ->and($element->minOccurs)->toBe(0)
                ->and($element->maxOccurs)->toBe(1);
        });
    });
});
