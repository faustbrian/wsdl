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
use Cline\WsdlBuilder\Xsd\Compositors\Any;
use Cline\WsdlBuilder\Xsd\Compositors\Choice;

describe('Choice Compositor', function (): void {
    describe('Happy Paths', function (): void {
        test('creates choice compositor from complex type', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('TestType');

            // Act
            $choice = $complexType->choice();

            // Assert
            expect($choice)->toBeInstanceOf(Choice::class);
        });

        test('adds elements with XsdType enum to choice', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->element('option1', XsdType::String)
                ->element('option2', XsdType::Int);

            // Assert
            $elements = $choice->getElements();
            expect($elements)->toHaveCount(2)
                ->and($elements[0]->name)->toBe('option1')
                ->and($elements[0]->type)->toBe('string')
                ->and($elements[1]->name)->toBe('option2')
                ->and($elements[1]->type)->toBe('int');
        });

        test('adds elements with string type to choice', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->element('customOption', 'tns:CustomType');

            // Assert
            $elements = $choice->getElements();
            expect($elements[0]->type)->toBe('tns:CustomType');
        });

        test('adds nullable element to choice', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->element('optional', XsdType::String, true);

            // Assert
            $elements = $choice->getElements();
            expect($elements[0]->nullable)->toBeTrue();
        });

        test('adds element with minOccurs and maxOccurs to choice', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->element('repeating', XsdType::String, false, 1, 10);

            // Assert
            $elements = $choice->getElements();
            expect($elements[0]->minOccurs)->toBe(1)
                ->and($elements[0]->maxOccurs)->toBe(10);
        });

        test('sets minOccurs on choice compositor', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->minOccurs(0);

            // Assert
            expect($choice->getMinOccurs())->toBe(0);
        });

        test('sets maxOccurs on choice compositor', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->maxOccurs(5);

            // Assert
            expect($choice->getMaxOccurs())->toBe(5);
        });

        test('sets unbounded maxOccurs on choice compositor', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->maxOccurs(-1);

            // Assert
            expect($choice->getMaxOccurs())->toBe(-1);
        });

        test('end returns parent complex type from choice', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('TestType');

            // Act
            $choice = $complexType->choice();
            $result = $choice->end();

            // Assert
            expect($result)->toBe($complexType);
        });

        test('chains choice methods fluently', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->minOccurs(1)
                ->maxOccurs(5)
                ->element('option1', XsdType::String)
                ->element('option2', XsdType::Int);

            // Assert
            expect($choice)->toBeInstanceOf(Choice::class)
                ->and($choice->getMinOccurs())->toBe(1)
                ->and($choice->getMaxOccurs())->toBe(5)
                ->and($choice->getElements())->toHaveCount(2);
        });

        test('returns to complex type after choice using end', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $complexType = $wsdl->complexType('TestType')
                ->choice()
                ->element('option1', XsdType::String)
                ->end()
                ->element('regularElement', XsdType::Int);

            // Assert
            $elements = $complexType->getElements();
            expect($elements)->toHaveCount(1)
                ->and($elements[0]->name)->toBe('regularElement');
        });
    });

    describe('Edge Cases', function (): void {
        test('creates choice with zero minOccurs', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')
                ->choice()
                ->minOccurs(0)
                ->maxOccurs(1);

            // Assert
            expect($choice->getMinOccurs())->toBe(0)
                ->and($choice->getMaxOccurs())->toBe(1);
        });

        test('creates empty choice without elements', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $choice = $wsdl->complexType('TestType')->choice();

            // Assert
            expect($choice->getElements())->toBeEmpty();
        });
    });
});

describe('All Compositor', function (): void {
    describe('Happy Paths', function (): void {
        test('creates all compositor from complex type', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('TestType');

            // Act
            $all = $complexType->all();

            // Assert
            expect($all)->toBeInstanceOf(All::class);
        });

        test('adds elements with XsdType enum to all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')
                ->all()
                ->element('field1', XsdType::String)
                ->element('field2', XsdType::Int);

            // Assert
            $elements = $all->getElements();
            expect($elements)->toHaveCount(2)
                ->and($elements[0]->name)->toBe('field1')
                ->and($elements[0]->type)->toBe('string')
                ->and($elements[1]->name)->toBe('field2')
                ->and($elements[1]->type)->toBe('int');
        });

        test('adds elements with string type to all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')
                ->all()
                ->element('customField', 'tns:CustomType');

            // Assert
            $elements = $all->getElements();
            expect($elements[0]->type)->toBe('tns:CustomType');
        });

        test('adds nullable element to all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')
                ->all()
                ->element('optional', XsdType::String, true);

            // Assert
            $elements = $all->getElements();
            expect($elements[0]->nullable)->toBeTrue();
        });

        test('adds element with minOccurs 0 to all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')
                ->all()
                ->element('optional', XsdType::String, false, 0, 1);

            // Assert
            $elements = $all->getElements();
            expect($elements[0]->minOccurs)->toBe(0)
                ->and($elements[0]->maxOccurs)->toBe(1);
        });

        test('adds element with minOccurs 1 to all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')
                ->all()
                ->element('required', XsdType::String, false, 1, 1);

            // Assert
            $elements = $all->getElements();
            expect($elements[0]->minOccurs)->toBe(1)
                ->and($elements[0]->maxOccurs)->toBe(1);
        });

        test('end returns parent complex type from all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('TestType');

            // Act
            $all = $complexType->all();
            $result = $all->end();

            // Assert
            expect($result)->toBe($complexType);
        });

        test('chains all methods fluently', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')
                ->all()
                ->element('field1', XsdType::String)
                ->element('field2', XsdType::Int);

            // Assert
            expect($all)->toBeInstanceOf(All::class)
                ->and($all->getElements())->toHaveCount(2);
        });

        test('returns to complex type after all using end', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $complexType = $wsdl->complexType('TestType')
                ->all()
                ->element('field1', XsdType::String)
                ->end()
                ->element('regularElement', XsdType::Int);

            // Assert
            $elements = $complexType->getElements();
            expect($elements)->toHaveCount(1)
                ->and($elements[0]->name)->toBe('regularElement');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws InvalidArgumentException for minOccurs greater than 1', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act & Assert
            expect(fn () => $wsdl->complexType('TestType')
                ->all()
                ->element('field', XsdType::String, false, 2, 1))
                ->toThrow(InvalidArgumentException::class, 'Elements in <all> can only have minOccurs 0 or 1');
        });

        test('throws InvalidArgumentException for maxOccurs not equal to 1', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act & Assert
            expect(fn () => $wsdl->complexType('TestType')
                ->all()
                ->element('field', XsdType::String, false, 0, 5))
                ->toThrow(InvalidArgumentException::class, 'Elements in <all> can only have maxOccurs 1');
        });

        test('throws InvalidArgumentException for unbounded maxOccurs', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act & Assert
            expect(fn () => $wsdl->complexType('TestType')
                ->all()
                ->element('field', XsdType::String, false, 0, -1))
                ->toThrow(InvalidArgumentException::class, 'Elements in <all> can only have maxOccurs 1');
        });
    });

    describe('Edge Cases', function (): void {
        test('creates empty all without elements', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')->all();

            // Assert
            expect($all->getElements())->toBeEmpty();
        });

        test('allows null minOccurs and maxOccurs in all', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $all = $wsdl->complexType('TestType')
                ->all()
                ->element('field', XsdType::String, false, null, null);

            // Assert
            $elements = $all->getElements();
            expect($elements[0]->minOccurs)->toBeNull()
                ->and($elements[0]->maxOccurs)->toBeNull();
        });
    });
});

describe('Any Compositor', function (): void {
    describe('Happy Paths', function (): void {
        test('creates any compositor from complex type', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('TestType');

            // Act
            $any = $complexType->any();

            // Assert
            expect($any)->toBeInstanceOf(Any::class);
        });

        test('sets default namespace to ##any', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')->any();

            // Assert
            expect($any->getNamespace())->toBe('##any');
        });

        test('sets default processContents to strict', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')->any();

            // Assert
            expect($any->getProcessContents())->toBe('strict');
        });

        test('sets namespace to ##other', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->namespace('##other');

            // Assert
            expect($any->getNamespace())->toBe('##other');
        });

        test('sets namespace to ##local', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->namespace('##local');

            // Assert
            expect($any->getNamespace())->toBe('##local');
        });

        test('sets namespace to ##targetNamespace', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->namespace('##targetNamespace');

            // Assert
            expect($any->getNamespace())->toBe('##targetNamespace');
        });

        test('sets namespace to custom URI', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->namespace('http://custom.namespace.com/');

            // Assert
            expect($any->getNamespace())->toBe('http://custom.namespace.com/');
        });

        test('sets processContents to lax', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->processContents('lax');

            // Assert
            expect($any->getProcessContents())->toBe('lax');
        });

        test('sets processContents to skip', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->processContents('skip');

            // Assert
            expect($any->getProcessContents())->toBe('skip');
        });

        test('sets processContents to strict', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->processContents('strict');

            // Assert
            expect($any->getProcessContents())->toBe('strict');
        });

        test('sets minOccurs on any', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->minOccurs(0);

            // Assert
            expect($any->getMinOccurs())->toBe(0);
        });

        test('sets maxOccurs on any', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->maxOccurs(5);

            // Assert
            expect($any->getMaxOccurs())->toBe(5);
        });

        test('sets unbounded maxOccurs on any', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->maxOccurs(-1);

            // Assert
            expect($any->getMaxOccurs())->toBe(-1);
        });

        test('end returns parent complex type from any', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
            $complexType = $wsdl->complexType('TestType');

            // Act
            $any = $complexType->any();
            $result = $any->end();

            // Assert
            expect($result)->toBe($complexType);
        });

        test('chains any methods fluently', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->namespace('##other')
                ->processContents('lax')
                ->minOccurs(1)
                ->maxOccurs(10);

            // Assert
            expect($any)->toBeInstanceOf(Any::class)
                ->and($any->getNamespace())->toBe('##other')
                ->and($any->getProcessContents())->toBe('lax')
                ->and($any->getMinOccurs())->toBe(1)
                ->and($any->getMaxOccurs())->toBe(10);
        });

        test('returns to complex type after any using end', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $complexType = $wsdl->complexType('TestType')
                ->any()
                ->namespace('##other')
                ->end()
                ->element('regularElement', XsdType::Int);

            // Assert
            $elements = $complexType->getElements();
            expect($elements)->toHaveCount(1)
                ->and($elements[0]->name)->toBe('regularElement');
        });
    });

    describe('Sad Paths', function (): void {
        test('throws InvalidArgumentException for invalid processContents value', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act & Assert
            expect(fn () => $wsdl->complexType('TestType')
                ->any()
                ->processContents('invalid'))
                ->toThrow(InvalidArgumentException::class, 'processContents must be one of: strict, lax, skip');
        });
    });

    describe('Edge Cases', function (): void {
        test('creates any with default minOccurs and maxOccurs as null', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')->any();

            // Assert
            expect($any->getMinOccurs())->toBeNull()
                ->and($any->getMaxOccurs())->toBeNull();
        });

        test('sets minOccurs to zero for optional any wildcard', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $any = $wsdl->complexType('TestType')
                ->any()
                ->minOccurs(0)
                ->maxOccurs(1);

            // Assert
            expect($any->getMinOccurs())->toBe(0)
                ->and($any->getMaxOccurs())->toBe(1);
        });
    });
});

describe('Fluent Interface Integration', function (): void {
    describe('Happy Paths', function (): void {
        test('combines multiple compositors in single complex type', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $complexType = $wsdl->complexType('MixedType')
                ->element('regularField', XsdType::String)
                ->choice()
                ->element('option1', XsdType::Int)
                ->element('option2', XsdType::Boolean)
                ->end()
                ->any()
                ->namespace('##other')
                ->end();

            // Assert
            expect($complexType->getElements())->toHaveCount(1)
                ->and($complexType->getElements()[0]->name)->toBe('regularField');
        });

        test('creates complex nested structure with compositors', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

            // Act
            $wsdl->complexType('NestedType')
                ->element('id', XsdType::Int)
                ->choice()
                ->minOccurs(1)
                ->maxOccurs(1)
                ->element('emailContact', XsdType::String)
                ->element('phoneContact', XsdType::String)
                ->end()
                ->all()
                ->element('firstName', XsdType::String)
                ->element('lastName', XsdType::String)
                ->end()
                ->any()
                ->namespace('##other')
                ->processContents('lax')
                ->minOccurs(0)
                ->maxOccurs(-1)
                ->end();

            // Assert - if we reach here without exceptions, the fluent interface works
            expect($wsdl->getComplexTypes())->toHaveKey('NestedType');
        });

        test('builds WSDL with compositors without errors', function (): void {
            // Arrange
            $wsdl = Wsdl::create('TestService', 'http://test.example.com/')
                ->complexType('PersonType')
                ->choice()
                ->element('individual', XsdType::String)
                ->element('organization', XsdType::String)
                ->end()
                ->end();

            // Act
            $xml = $wsdl->build();

            // Assert
            expect($xml)->toBeString()
                ->and($xml)->toContain('xsd:choice');
        });
    });
});
