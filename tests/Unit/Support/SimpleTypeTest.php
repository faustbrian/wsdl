<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Types\SimpleType;

describe('SimpleType', function (): void {
    describe('Happy Paths', function (): void {
        test('creates simple type with name', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('PhoneNumber');

            expect($type->getName())->toBe('PhoneNumber');
        });

        test('defaults to xsd:string base', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('CustomString');

            expect($type->getBase())->toBe('string');
        });

        test('sets base type with XsdType enum', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('PositiveNumber')
                ->base(XsdType::Int);

            expect($type->getBase())->toBe('int');
        });

        test('sets base type with string', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('CustomType')
                ->base('xsd:decimal');

            expect($type->getBase())->toBe('decimal');
        });

        test('sets minLength restriction', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Username')
                ->minLength(3);

            expect($type->getMinLength())->toBe(3);
        });

        test('sets maxLength restriction', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Username')
                ->maxLength(50);

            expect($type->getMaxLength())->toBe(50);
        });

        test('sets pattern restriction', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Email')
                ->pattern('[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}');

            expect($type->getPattern())->toBe('[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}');
        });

        test('adds enumeration values', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Status')
                ->enumeration('active', 'inactive');

            expect($type->getEnumeration())->toBe(['active', 'inactive']);
        });

        test('appends enumeration values', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Status')
                ->enumeration('active', 'inactive')
                ->enumeration('pending');

            expect($type->getEnumeration())->toBe(['active', 'inactive', 'pending']);
        });

        test('sets minInclusive with integer', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Age')
                ->base(XsdType::Int)
                ->minInclusive(0);

            expect($type->getMinInclusive())->toBe('0');
        });

        test('sets maxInclusive with integer', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Age')
                ->base(XsdType::Int)
                ->maxInclusive(150);

            expect($type->getMaxInclusive())->toBe('150');
        });

        test('sets minExclusive with float', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('PositiveDecimal')
                ->base(XsdType::Decimal)
                ->minExclusive(0.0);

            expect($type->getMinExclusive())->toBe('0');
        });

        test('sets maxExclusive with float', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('Percentage')
                ->base(XsdType::Decimal)
                ->maxExclusive(100.0);

            expect($type->getMaxExclusive())->toBe('100');
        });

        test('end returns parent wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->simpleType('TestType')->end();

            expect($result)->toBe($wsdl);
        });

        test('fluent interface chains all restrictions', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('RestrictedString')
                ->base(XsdType::String)
                ->minLength(1)
                ->maxLength(100)
                ->pattern('[A-Z][a-z]+');

            expect($type)
                ->toBeInstanceOf(SimpleType::class)
                ->and($type->getBase())->toBe('string')
                ->and($type->getMinLength())->toBe(1)
                ->and($type->getMaxLength())->toBe(100)
                ->and($type->getPattern())->toBe('[A-Z][a-z]+');
        });
    });

    describe('Default Values', function (): void {
        test('has null minLength by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('TestType');

            expect($type->getMinLength())->toBeNull();
        });

        test('has null maxLength by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('TestType');

            expect($type->getMaxLength())->toBeNull();
        });

        test('has null pattern by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('TestType');

            expect($type->getPattern())->toBeNull();
        });

        test('has empty enumeration by default', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->simpleType('TestType');

            expect($type->getEnumeration())->toBe([]);
        });
    });
});
