<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Attributes\Attribute;
use Cline\WsdlBuilder\Xsd\SimpleContent;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;

describe('SimpleContent', function (): void {
    describe('Happy Paths', function (): void {
        describe('extension()', function (): void {
            test('sets base to XsdType enum value and derivation type to extension', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('AddressType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent->extension(XsdType::String);

                expect($result)->toBe($simpleContent)
                    ->and($simpleContent->getBase())->toBe('xsd:string')
                    ->and($simpleContent->getDerivationType())->toBe('extension');
            });

            test('sets base to string value and derivation type to extension', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('CustomType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent->extension('tns:CustomBaseType');

                expect($result)->toBe($simpleContent)
                    ->and($simpleContent->getBase())->toBe('tns:CustomBaseType')
                    ->and($simpleContent->getDerivationType())->toBe('extension');
            });

            test('extension with XsdType::Int', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('QuantityType');
                $simpleContent = $complexType->simpleContent();

                $simpleContent->extension(XsdType::Int);

                expect($simpleContent->getBase())->toBe('xsd:int')
                    ->and($simpleContent->getDerivationType())->toBe('extension');
            });

            test('extension with XsdType::DateTime', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TimestampType');
                $simpleContent = $complexType->simpleContent();

                $simpleContent->extension(XsdType::DateTime);

                expect($simpleContent->getBase())->toBe('xsd:dateTime')
                    ->and($simpleContent->getDerivationType())->toBe('extension');
            });

            test('extension returns self for fluent interface', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('FluentType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent->extension(XsdType::String);

                expect($result)->toBeInstanceOf(SimpleContent::class)
                    ->and($result)->toBe($simpleContent);
            });
        });

        describe('restriction()', function (): void {
            test('sets base to XsdType enum value and derivation type to restriction', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('RestrictedType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent->restriction(XsdType::String);

                expect($result)->toBe($simpleContent)
                    ->and($simpleContent->getBase())->toBe('xsd:string')
                    ->and($simpleContent->getDerivationType())->toBe('restriction');
            });

            test('sets base to string value and derivation type to restriction', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('CustomRestrictedType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent->restriction('tns:BaseType');

                expect($result)->toBe($simpleContent)
                    ->and($simpleContent->getBase())->toBe('tns:BaseType')
                    ->and($simpleContent->getDerivationType())->toBe('restriction');
            });

            test('restriction with XsdType::Decimal', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('PriceType');
                $simpleContent = $complexType->simpleContent();

                $simpleContent->restriction(XsdType::Decimal);

                expect($simpleContent->getBase())->toBe('xsd:decimal')
                    ->and($simpleContent->getDerivationType())->toBe('restriction');
            });

            test('restriction returns self for fluent interface', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('FluentType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent->restriction(XsdType::Int);

                expect($result)->toBeInstanceOf(SimpleContent::class)
                    ->and($result)->toBe($simpleContent);
            });
        });

        describe('attribute()', function (): void {
            test('adds single attribute with XsdType enum', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('PersonType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->extension(XsdType::String);

                $result = $simpleContent->attribute('id', XsdType::Int);

                $attributes = $simpleContent->getAttributes();

                expect($result)->toBe($simpleContent)
                    ->and($attributes)->toHaveCount(1)
                    ->and($attributes[0])->toBeInstanceOf(Attribute::class)
                    ->and($attributes[0]->getName())->toBe('id')
                    ->and($attributes[0]->getType())->toBe('xsd:int');
            });

            test('adds single attribute with string type', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('EntityType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->extension(XsdType::String);

                $result = $simpleContent->attribute('status', 'tns:StatusType');

                $attributes = $simpleContent->getAttributes();

                expect($result)->toBe($simpleContent)
                    ->and($attributes)->toHaveCount(1)
                    ->and($attributes[0]->getName())->toBe('status')
                    ->and($attributes[0]->getType())->toBe('tns:StatusType');
            });

            test('adds multiple attributes via chaining', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('ComplexType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->extension(XsdType::String);

                $simpleContent
                    ->attribute('id', XsdType::Int)
                    ->attribute('version', XsdType::String)
                    ->attribute('timestamp', XsdType::DateTime);

                $attributes = $simpleContent->getAttributes();

                expect($attributes)->toHaveCount(3)
                    ->and($attributes[0]->getName())->toBe('id')
                    ->and($attributes[0]->getType())->toBe('xsd:int')
                    ->and($attributes[1]->getName())->toBe('version')
                    ->and($attributes[1]->getType())->toBe('xsd:string')
                    ->and($attributes[2]->getName())->toBe('timestamp')
                    ->and($attributes[2]->getType())->toBe('xsd:dateTime');
            });

            test('attribute returns self for fluent interface', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('FluentType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent
                    ->extension(XsdType::String)
                    ->attribute('name', XsdType::String);

                expect($result)->toBeInstanceOf(SimpleContent::class)
                    ->and($result)->toBe($simpleContent);
            });

            test('attributes preserve insertion order', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('OrderedType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->extension(XsdType::String);

                $simpleContent
                    ->attribute('first', XsdType::String)
                    ->attribute('second', XsdType::Int)
                    ->attribute('third', XsdType::Boolean);

                $attributes = $simpleContent->getAttributes();

                expect($attributes[0]->getName())->toBe('first')
                    ->and($attributes[1]->getName())->toBe('second')
                    ->and($attributes[2]->getName())->toBe('third');
            });
        });

        describe('end()', function (): void {
            test('returns parent ComplexType instance', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('ParentType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent->end();

                expect($result)->toBeInstanceOf(ComplexType::class)
                    ->and($result)->toBe($complexType)
                    ->and($result->getName())->toBe('ParentType');
            });

            test('end allows continuing to build parent ComplexType', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('BuilderType');
                $simpleContent = $complexType->simpleContent();

                $result = $simpleContent
                    ->extension(XsdType::String)
                    ->attribute('id', XsdType::Int)
                    ->end();

                expect($result)->toBe($complexType)
                    ->and($result->getSimpleContent())->toBe($simpleContent);
            });
        });

        describe('getters', function (): void {
            test('getBase returns base value after extension', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->extension(XsdType::String);

                expect($simpleContent->getBase())->toBe('xsd:string');
            });

            test('getBase returns base value after restriction', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->restriction('tns:CustomType');

                expect($simpleContent->getBase())->toBe('tns:CustomType');
            });

            test('getDerivationType returns extension', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->extension(XsdType::String);

                expect($simpleContent->getDerivationType())->toBe('extension');
            });

            test('getDerivationType returns restriction', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->restriction(XsdType::Int);

                expect($simpleContent->getDerivationType())->toBe('restriction');
            });

            test('getAttributes returns empty array initially', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();

                expect($simpleContent->getAttributes())->toBeArray()
                    ->and($simpleContent->getAttributes())->toBeEmpty();
            });

            test('getAttributes returns array of Attribute instances', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();
                $simpleContent->extension(XsdType::String);
                $simpleContent->attribute('id', XsdType::Int);
                $simpleContent->attribute('name', XsdType::String);

                $attributes = $simpleContent->getAttributes();

                expect($attributes)->toHaveCount(2)
                    ->and($attributes[0])->toBeInstanceOf(Attribute::class)
                    ->and($attributes[1])->toBeInstanceOf(Attribute::class);
            });

            test('getBase returns null initially', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();

                expect($simpleContent->getBase())->toBeNull();
            });

            test('getDerivationType returns null initially', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('TestType');
                $simpleContent = $complexType->simpleContent();

                expect($simpleContent->getDerivationType())->toBeNull();
            });
        });

        describe('complete workflow', function (): void {
            test('builds complete simpleContent with extension and attributes', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('InternationalPrice');

                $complexType->simpleContent()
                    ->extension(XsdType::Decimal)
                    ->attribute('currency', XsdType::String)
                    ->attribute('locale', XsdType::String)
                    ->end();

                $simpleContent = $complexType->getSimpleContent();

                expect($simpleContent)->toBeInstanceOf(SimpleContent::class)
                    ->and($simpleContent->getBase())->toBe('xsd:decimal')
                    ->and($simpleContent->getDerivationType())->toBe('extension')
                    ->and($simpleContent->getAttributes())->toHaveCount(2)
                    ->and($simpleContent->getAttributes()[0]->getName())->toBe('currency')
                    ->and($simpleContent->getAttributes()[1]->getName())->toBe('locale');
            });

            test('builds complete simpleContent with restriction and attributes', function (): void {
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('RestrictedValue');

                $complexType->simpleContent()
                    ->restriction('tns:BaseValueType')
                    ->attribute('id', XsdType::Int)
                    ->attribute('version', XsdType::String)
                    ->end();

                $simpleContent = $complexType->getSimpleContent();

                expect($simpleContent)->toBeInstanceOf(SimpleContent::class)
                    ->and($simpleContent->getBase())->toBe('tns:BaseValueType')
                    ->and($simpleContent->getDerivationType())->toBe('restriction')
                    ->and($simpleContent->getAttributes())->toHaveCount(2);
            });
        });
    });

    describe('Edge Cases', function (): void {
        test('simpleContent with extension but no attributes', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('SimpleType');
            $simpleContent = $complexType->simpleContent();
            $simpleContent->extension(XsdType::String);

            expect($simpleContent->getBase())->toBe('xsd:string')
                ->and($simpleContent->getDerivationType())->toBe('extension')
                ->and($simpleContent->getAttributes())->toBeEmpty();
        });

        test('simpleContent with restriction but no attributes', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('SimpleType');
            $simpleContent = $complexType->simpleContent();
            $simpleContent->restriction(XsdType::Int);

            expect($simpleContent->getBase())->toBe('xsd:int')
                ->and($simpleContent->getDerivationType())->toBe('restriction')
                ->and($simpleContent->getAttributes())->toBeEmpty();
        });

        test('overwrites base and derivation type when extension called after restriction', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('ChangingType');
            $simpleContent = $complexType->simpleContent();

            $simpleContent->restriction(XsdType::Int);
            $simpleContent->extension(XsdType::String);

            expect($simpleContent->getBase())->toBe('xsd:string')
                ->and($simpleContent->getDerivationType())->toBe('extension');
        });

        test('overwrites base and derivation type when restriction called after extension', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('ChangingType');
            $simpleContent = $complexType->simpleContent();

            $simpleContent->extension(XsdType::String);
            $simpleContent->restriction(XsdType::Int);

            expect($simpleContent->getBase())->toBe('xsd:int')
                ->and($simpleContent->getDerivationType())->toBe('restriction');
        });

        test('attributes added before setting derivation type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('UnorderedType');
            $simpleContent = $complexType->simpleContent();

            $simpleContent->attribute('id', XsdType::Int);
            $simpleContent->extension(XsdType::String);
            $simpleContent->attribute('version', XsdType::String);

            $attributes = $simpleContent->getAttributes();

            expect($attributes)->toHaveCount(2)
                ->and($simpleContent->getBase())->toBe('xsd:string')
                ->and($simpleContent->getDerivationType())->toBe('extension')
                ->and($attributes[0]->getName())->toBe('id')
                ->and($attributes[1]->getName())->toBe('version');
        });

        test('adding many attributes maintains order', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('ManyAttributesType');
            $simpleContent = $complexType->simpleContent();
            $simpleContent->extension(XsdType::String);

            $simpleContent
                ->attribute('attr1', XsdType::String)
                ->attribute('attr2', XsdType::Int)
                ->attribute('attr3', XsdType::Boolean)
                ->attribute('attr4', XsdType::DateTime)
                ->attribute('attr5', XsdType::Decimal);

            $attributes = $simpleContent->getAttributes();

            expect($attributes)->toHaveCount(5)
                ->and($attributes[0]->getName())->toBe('attr1')
                ->and($attributes[1]->getName())->toBe('attr2')
                ->and($attributes[2]->getName())->toBe('attr3')
                ->and($attributes[3]->getName())->toBe('attr4')
                ->and($attributes[4]->getName())->toBe('attr5');
        });

        test('extension with custom namespace prefix in string type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('CustomType');
            $simpleContent = $complexType->simpleContent();

            $simpleContent->extension('custom:MyBaseType');

            expect($simpleContent->getBase())->toBe('custom:MyBaseType')
                ->and($simpleContent->getDerivationType())->toBe('extension');
        });

        test('restriction with custom namespace prefix in string type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('CustomType');
            $simpleContent = $complexType->simpleContent();

            $simpleContent->restriction('custom:MyBaseType');

            expect($simpleContent->getBase())->toBe('custom:MyBaseType')
                ->and($simpleContent->getDerivationType())->toBe('restriction');
        });

        test('simpleContent initially has null values', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('FreshType');
            $simpleContent = $complexType->simpleContent();

            expect($simpleContent->getBase())->toBeNull()
                ->and($simpleContent->getDerivationType())->toBeNull()
                ->and($simpleContent->getAttributes())->toBeEmpty();
        });

        test('multiple end calls return same parent', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('ConsistentType');
            $simpleContent = $complexType->simpleContent();

            $result1 = $simpleContent->end();
            $result2 = $simpleContent->end();

            expect($result1)->toBe($complexType)
                ->and($result2)->toBe($complexType)
                ->and($result1)->toBe($result2);
        });
    });
});
