<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Attributes\AnyAttribute;
use Cline\WsdlBuilder\Xsd\Attributes\Attribute;
use Cline\WsdlBuilder\Xsd\Attributes\AttributeGroup;

describe('Attribute', function (): void {
    describe('Happy Paths', function (): void {
        test('creates attribute with name and XsdType enum', function (): void {
            $attribute = Attribute::create('id', XsdType::Int);

            expect($attribute->getName())->toBe('id')
                ->and($attribute->getType())->toBe('xsd:int');
        });

        test('creates attribute with name and string type', function (): void {
            $attribute = Attribute::create('status', 'tns:StatusType');

            expect($attribute->getName())->toBe('status')
                ->and($attribute->getType())->toBe('tns:StatusType');
        });

        test('sets use constraint to required', function (): void {
            $attribute = Attribute::create('id', XsdType::String)
                ->use('required');

            expect($attribute->getUse())->toBe('required');
        });

        test('sets use constraint to optional', function (): void {
            $attribute = Attribute::create('optional', XsdType::String)
                ->use('optional');

            expect($attribute->getUse())->toBe('optional');
        });

        test('sets use constraint to prohibited', function (): void {
            $attribute = Attribute::create('legacy', XsdType::String)
                ->use('prohibited');

            expect($attribute->getUse())->toBe('prohibited');
        });

        test('sets default value', function (): void {
            $attribute = Attribute::create('version', XsdType::String)
                ->default('1.0');

            expect($attribute->getDefault())->toBe('1.0');
        });

        test('sets fixed value', function (): void {
            $attribute = Attribute::create('namespace', XsdType::String)
                ->fixed('http://example.com');

            expect($attribute->getFixed())->toBe('http://example.com');
        });

        test('sets form to qualified', function (): void {
            $attribute = Attribute::create('name', XsdType::String)
                ->form('qualified');

            expect($attribute->getForm())->toBe('qualified');
        });

        test('sets form to unqualified', function (): void {
            $attribute = Attribute::create('name', XsdType::String)
                ->form('unqualified');

            expect($attribute->getForm())->toBe('unqualified');
        });

        test('chains multiple setters with fluent interface', function (): void {
            $attribute = Attribute::create('id', XsdType::String)
                ->use('required')
                ->default('default-id')
                ->form('qualified');

            expect($attribute)
                ->toBeInstanceOf(Attribute::class)
                ->and($attribute->getUse())->toBe('required')
                ->and($attribute->getDefault())->toBe('default-id')
                ->and($attribute->getForm())->toBe('qualified');
        });

        test('returns null for unset optional properties', function (): void {
            $attribute = Attribute::create('simple', XsdType::String);

            expect($attribute->getUse())->toBeNull()
                ->and($attribute->getDefault())->toBeNull()
                ->and($attribute->getFixed())->toBeNull()
                ->and($attribute->getForm())->toBeNull();
        });
    });

    describe('Edge Cases', function (): void {
        test('handles empty string as default value', function (): void {
            $attribute = Attribute::create('empty', XsdType::String)
                ->default('');

            expect($attribute->getDefault())->toBe('');
        });

        test('handles empty string as fixed value', function (): void {
            $attribute = Attribute::create('empty', XsdType::String)
                ->fixed('');

            expect($attribute->getFixed())->toBe('');
        });

        test('allows setting both default and fixed values', function (): void {
            $attribute = Attribute::create('conflicting', XsdType::String)
                ->default('default-value')
                ->fixed('fixed-value');

            expect($attribute->getDefault())->toBe('default-value')
                ->and($attribute->getFixed())->toBe('fixed-value');
        });

        test('overwrites use constraint when set multiple times', function (): void {
            $attribute = Attribute::create('changing', XsdType::String)
                ->use('required')
                ->use('optional');

            expect($attribute->getUse())->toBe('optional');
        });
    });
});

describe('AttributeGroup', function (): void {
    describe('Happy Paths', function (): void {
        test('creates attribute group with name via Wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('CommonAttributes');

            expect($group->getName())->toBe('CommonAttributes');
        });

        test('adds single attribute to group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('PersonAttributes');
            $group->attribute('id', XsdType::Int);

            $attributes = $group->getAttributes();

            expect($attributes)->toHaveCount(1)
                ->and($attributes[0]->getName())->toBe('id')
                ->and($attributes[0]->getType())->toBe('xsd:int');
        });

        test('adds multiple attributes to group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('EntityAttributes');
            $group->attribute('id', XsdType::Int);
            $group->attribute('version', XsdType::String);
            $group->attribute('created', XsdType::DateTime);

            $attributes = $group->getAttributes();

            expect($attributes)->toHaveCount(3)
                ->and($attributes[0]->getName())->toBe('id')
                ->and($attributes[1]->getName())->toBe('version')
                ->and($attributes[2]->getName())->toBe('created');
        });

        test('attribute method returns Attribute instance for chaining', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('ConfigAttributes');
            $attribute = $group->attribute('key', XsdType::String);

            expect($attribute)->toBeInstanceOf(Attribute::class)
                ->and($attribute->getName())->toBe('key');
        });

        test('chains attribute setters via attribute method', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('StrictAttributes');
            $group->attribute('id', XsdType::String)
                ->use('required')
                ->form('qualified');

            $attributes = $group->getAttributes();

            expect($attributes[0]->getUse())->toBe('required')
                ->and($attributes[0]->getForm())->toBe('qualified');
        });

        test('adds anyAttribute to group', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('ExtensibleAttributes');
            $group->anyAttribute();

            $anyAttribute = $group->getAnyAttribute();

            expect($anyAttribute)->toBeInstanceOf(AnyAttribute::class)
                ->and($anyAttribute)->not->toBeNull();
        });

        test('anyAttribute method returns AnyAttribute for chaining', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('FlexibleAttributes');
            $anyAttribute = $group->anyAttribute();

            expect($anyAttribute)->toBeInstanceOf(AnyAttribute::class);
        });

        test('chains anyAttribute setters', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('CustomAttributes');
            $group->anyAttribute()
                ->namespace('http://custom.example.com/')
                ->processContents('lax');

            $anyAttribute = $group->getAnyAttribute();

            expect($anyAttribute->getNamespace())->toBe('http://custom.example.com/')
                ->and($anyAttribute->getProcessContents())->toBe('lax');
        });

        test('end method returns parent Wsdl instance', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $result = $wsdl->attributeGroup('TestGroup')->end();

            expect($result)->toBe($wsdl);
        });

        test('creates multiple attribute groups via Wsdl', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group1 = $wsdl->attributeGroup('CommonAttributes');
            $group1->attribute('id', XsdType::Int);

            $group2 = $wsdl->attributeGroup('AuditAttributes');
            $group2->attribute('created', XsdType::DateTime);

            $groups = $wsdl->getAttributeGroups();

            expect($groups)->toHaveCount(2)
                ->and($groups['CommonAttributes']->getName())->toBe('CommonAttributes')
                ->and($groups['AuditAttributes']->getName())->toBe('AuditAttributes');
        });

        test('combines regular attributes with anyAttribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('MixedAttributes');
            $group->attribute('id', XsdType::Int);
            $group->attribute('name', XsdType::String);
            $group->anyAttribute();

            expect($group->getAttributes())->toHaveCount(2)
                ->and($group->getAnyAttribute())->toBeInstanceOf(AnyAttribute::class);
        });
    });

    describe('Edge Cases', function (): void {
        test('returns empty array when no attributes added', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('EmptyGroup');

            expect($group->getAttributes())->toBeEmpty();
        });

        test('returns null when anyAttribute not set', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('SimpleGroup');

            expect($group->getAnyAttribute())->toBeNull();
        });

        test('overwrites anyAttribute when called multiple times', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('OverwriteGroup');

            $first = $group->anyAttribute();
            $first->namespace('http://first.com/');

            $second = $group->anyAttribute();
            $second->namespace('http://second.com/');

            $anyAttribute = $group->getAnyAttribute();

            expect($anyAttribute->getNamespace())->toBe('http://second.com/');
        });
    });
});

describe('AnyAttribute', function (): void {
    describe('Happy Paths', function (): void {
        test('has default namespace value of ##any', function (): void {
            $anyAttribute = new AnyAttribute();

            expect($anyAttribute->getNamespace())->toBe('##any');
        });

        test('has default processContents value of strict', function (): void {
            $anyAttribute = new AnyAttribute();

            expect($anyAttribute->getProcessContents())->toBe('strict');
        });

        test('sets namespace to ##other', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->namespace('##other');

            expect($anyAttribute->getNamespace())->toBe('##other');
        });

        test('sets namespace to ##local', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->namespace('##local');

            expect($anyAttribute->getNamespace())->toBe('##local');
        });

        test('sets namespace to ##targetNamespace', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->namespace('##targetNamespace');

            expect($anyAttribute->getNamespace())->toBe('##targetNamespace');
        });

        test('sets namespace to custom URI', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->namespace('http://custom.example.com/');

            expect($anyAttribute->getNamespace())->toBe('http://custom.example.com/');
        });

        test('sets namespace to URI list', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->namespace('http://example1.com/ http://example2.com/');

            expect($anyAttribute->getNamespace())->toBe('http://example1.com/ http://example2.com/');
        });

        test('sets processContents to lax', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->processContents('lax');

            expect($anyAttribute->getProcessContents())->toBe('lax');
        });

        test('sets processContents to skip', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->processContents('skip');

            expect($anyAttribute->getProcessContents())->toBe('skip');
        });

        test('sets processContents to strict', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->processContents('strict');

            expect($anyAttribute->getProcessContents())->toBe('strict');
        });

        test('chains namespace and processContents with fluent interface', function (): void {
            $anyAttribute = new AnyAttribute();
            $result = $anyAttribute
                ->namespace('http://example.com/')
                ->processContents('lax');

            expect($result)->toBeInstanceOf(AnyAttribute::class)
                ->and($anyAttribute->getNamespace())->toBe('http://example.com/')
                ->and($anyAttribute->getProcessContents())->toBe('lax');
        });
    });

    describe('Edge Cases', function (): void {
        test('overwrites namespace when set multiple times', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->namespace('##other');
            $anyAttribute->namespace('##local');

            expect($anyAttribute->getNamespace())->toBe('##local');
        });

        test('overwrites processContents when set multiple times', function (): void {
            $anyAttribute = new AnyAttribute();
            $anyAttribute->processContents('lax');
            $anyAttribute->processContents('skip');

            expect($anyAttribute->getProcessContents())->toBe('skip');
        });
    });
});

describe('ComplexType Attribute Integration', function (): void {
    describe('Happy Paths', function (): void {
        test('adds attribute to complex type via attribute method', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType');
            $type->attribute('id', XsdType::Int);

            $attributes = $type->getAttributes();

            expect($attributes)->toHaveCount(1)
                ->and($attributes[0]->getName())->toBe('id')
                ->and($attributes[0]->getType())->toBe('xsd:int');
        });

        test('adds multiple attributes to complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('EntityType');
            $type->attribute('id', XsdType::Int);
            $type->attribute('version', XsdType::String);

            $attributes = $type->getAttributes();

            expect($attributes)->toHaveCount(2)
                ->and($attributes[0]->getName())->toBe('id')
                ->and($attributes[1]->getName())->toBe('version');
        });

        test('chains attribute setters on complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('StrictType');
            $type->attribute('id', XsdType::String)
                ->use('required')
                ->form('qualified');

            $attributes = $type->getAttributes();

            expect($attributes[0]->getUse())->toBe('required')
                ->and($attributes[0]->getForm())->toBe('qualified');
        });

        test('mixes elements and attributes in complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('PersonType');
            $type->element('name', XsdType::String);
            $type->element('email', XsdType::String);
            $type->attribute('id', XsdType::Int);

            expect($type->getElements())->toHaveCount(2)
                ->and($type->getAttributes())->toHaveCount(1);
        });

        test('adds attribute group reference to complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $group = $wsdl->attributeGroup('CommonAttributes');
            $group->attribute('id', XsdType::Int);

            $type = $wsdl->complexType('EntityType');
            $type->attributeGroup('CommonAttributes');

            $refs = $type->getAttributeGroupRefs();

            expect($refs)->toContain('CommonAttributes');
        });

        test('adds multiple attribute group references to complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('FullType')
                ->attributeGroup('CommonAttributes')
                ->attributeGroup('AuditAttributes');

            $refs = $type->getAttributeGroupRefs();

            expect($refs)->toHaveCount(2)
                ->and($refs)->toContain('CommonAttributes')
                ->and($refs)->toContain('AuditAttributes');
        });

        test('combines direct attributes and attribute group references', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('MixedType');
            $type->attribute('customAttr', XsdType::String);
            $type->attributeGroup('CommonAttributes');

            expect($type->getAttributes())->toHaveCount(1)
                ->and($type->getAttributeGroupRefs())->toHaveCount(1);
        });

        test('adds anyAttribute to complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('ExtensibleType');
            $type->anyAttribute();

            $anyAttribute = $type->getAnyAttribute();

            expect($anyAttribute)->toBeInstanceOf(AnyAttribute::class);
        });

        test('chains anyAttribute setters on complex type', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('FlexibleType');
            $type->anyAttribute()
                ->namespace('http://custom.com/')
                ->processContents('lax');

            $anyAttribute = $type->getAnyAttribute();

            expect($anyAttribute->getNamespace())->toBe('http://custom.com/')
                ->and($anyAttribute->getProcessContents())->toBe('lax');
        });

        test('builds complex type with elements, attributes, and anyAttribute', function (): void {
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $type = $wsdl->complexType('CompleteType');
            $type->element('name', XsdType::String);
            $type->element('email', XsdType::String);
            $type->attribute('id', XsdType::Int)
                ->use('required');
            $type->attribute('version', XsdType::String)
                ->default('1.0');
            $type->anyAttribute()
                ->namespace('##other');

            expect($type->getElements())->toHaveCount(2)
                ->and($type->getAttributes())->toHaveCount(2)
                ->and($type->getAnyAttribute())->toBeInstanceOf(AnyAttribute::class)
                ->and($type->getAttributes()[0]->getUse())->toBe('required')
                ->and($type->getAttributes()[1]->getDefault())->toBe('1.0')
                ->and($type->getAnyAttribute()->getNamespace())->toBe('##other');
        });
    });
});
