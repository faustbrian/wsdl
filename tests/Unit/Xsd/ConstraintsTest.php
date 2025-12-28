<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\Xsd\Constraints\Field;
use Cline\WsdlBuilder\Xsd\Constraints\Key;
use Cline\WsdlBuilder\Xsd\Constraints\KeyRef;
use Cline\WsdlBuilder\Xsd\Constraints\Selector;
use Cline\WsdlBuilder\Xsd\Constraints\Unique;
use Cline\WsdlBuilder\Xsd\Types\ComplexType;

describe('XSD Identity Constraints', function (): void {
    describe('Key', function (): void {
        describe('Happy Paths', function (): void {
            test('creates key constraint with name on complex type', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('PersonType');

                // Act
                $key = $complexType->key('personKey');

                // Assert
                expect($key)->toBeInstanceOf(Key::class)
                    ->and($key->getName())->toBe('personKey');
            });

            test('adds selector with xpath to key constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = $key->selector('.//person');

                // Assert
                expect($selector)->toBeInstanceOf(Selector::class)
                    ->and($selector->getXpath())->toBe('.//person')
                    ->and($key->getSelector())->toBe($selector);
            });

            test('adds single field with xpath to key constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $result = $key->field('@id');

                // Assert
                expect($result)->toBe($key)
                    ->and($key->getFields())->toHaveCount(1)
                    ->and($key->getFields()[0])->toBeInstanceOf(Field::class)
                    ->and($key->getFields()[0]->getXpath())->toBe('@id');
            });

            test('adds multiple fields to key constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $key->field('@id')
                    ->field('@ssn')
                    ->field('name');

                // Assert
                $fields = $key->getFields();
                expect($fields)->toHaveCount(3)
                    ->and($fields[0]->getXpath())->toBe('@id')
                    ->and($fields[1]->getXpath())->toBe('@ssn')
                    ->and($fields[2]->getXpath())->toBe('name');
            });

            test('end method returns parent complex type from key', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('PersonType');
                $key = $complexType->key('personKey');

                // Act
                $result = $key->end();

                // Assert
                expect($result)->toBe($complexType);
            });

            test('chains key constraint methods fluently', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $key = $wsdl->complexType('PersonType')
                    ->key('personKey')
                    ->field('@id')
                    ->field('@ssn');

                // Assert
                expect($key)->toBeInstanceOf(Key::class)
                    ->and($key->getName())->toBe('personKey')
                    ->and($key->getFields())->toHaveCount(2);
            });

            test('returns null selector when selector not set on key', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = $key->getSelector();

                // Assert
                expect($selector)->toBeNull();
            });
        });

        describe('Edge Cases', function (): void {
            test('creates key constraint with empty xpath in selector', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = $key->selector('');

                // Assert
                expect($selector->getXpath())->toBe('');
            });

            test('creates key constraint with empty xpath in field', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $key->field('');

                // Assert
                expect($key->getFields()[0]->getXpath())->toBe('');
            });

            test('creates key constraint with complex xpath expressions', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = $key->selector('.//ns:person[@type="employee"]');
                $key->field('@ns:id')
                    ->field('ns:department/@code');

                // Assert
                expect($selector->getXpath())->toBe('.//ns:person[@type="employee"]')
                    ->and($key->getFields()[0]->getXpath())->toBe('@ns:id')
                    ->and($key->getFields()[1]->getXpath())->toBe('ns:department/@code');
            });

            test('creates key constraint without any fields', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $fields = $key->getFields();

                // Assert
                expect($fields)->toBeEmpty();
            });
        });
    });

    describe('KeyRef', function (): void {
        describe('Happy Paths', function (): void {
            test('creates keyref constraint with name on complex type', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('OrderType');

                // Act
                $keyref = $complexType->keyRef('orderPersonRef');

                // Assert
                expect($keyref)->toBeInstanceOf(KeyRef::class)
                    ->and($keyref->getName())->toBe('orderPersonRef');
            });

            test('adds selector with xpath to keyref constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $selector = $keyref->selector('.//order');

                // Assert
                expect($selector)->toBeInstanceOf(Selector::class)
                    ->and($selector->getXpath())->toBe('.//order')
                    ->and($keyref->getSelector())->toBe($selector);
            });

            test('adds single field with xpath to keyref constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $result = $keyref->field('@personId');

                // Assert
                expect($result)->toBe($keyref)
                    ->and($keyref->getFields())->toHaveCount(1)
                    ->and($keyref->getFields()[0])->toBeInstanceOf(Field::class)
                    ->and($keyref->getFields()[0]->getXpath())->toBe('@personId');
            });

            test('adds multiple fields to keyref constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $keyref->field('@personId')
                    ->field('@departmentId')
                    ->field('regionCode');

                // Assert
                $fields = $keyref->getFields();
                expect($fields)->toHaveCount(3)
                    ->and($fields[0]->getXpath())->toBe('@personId')
                    ->and($fields[1]->getXpath())->toBe('@departmentId')
                    ->and($fields[2]->getXpath())->toBe('regionCode');
            });

            test('sets refer attribute to reference key constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $result = $keyref->refer('personKey');

                // Assert
                expect($result)->toBe($keyref)
                    ->and($keyref->getRefer())->toBe('personKey');
            });

            test('end method returns parent complex type from keyref', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('OrderType');
                $keyref = $complexType->keyRef('orderPersonRef');

                // Act
                $result = $keyref->end();

                // Assert
                expect($result)->toBe($complexType);
            });

            test('chains keyref constraint methods fluently', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $keyref = $wsdl->complexType('OrderType')
                    ->keyRef('orderPersonRef')
                    ->refer('personKey')
                    ->field('@personId')
                    ->field('@departmentId');

                // Assert
                expect($keyref)->toBeInstanceOf(KeyRef::class)
                    ->and($keyref->getName())->toBe('orderPersonRef')
                    ->and($keyref->getRefer())->toBe('personKey')
                    ->and($keyref->getFields())->toHaveCount(2);
            });

            test('returns null selector when selector not set on keyref', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $selector = $keyref->getSelector();

                // Assert
                expect($selector)->toBeNull();
            });

            test('returns null when refer not set on keyref', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $refer = $keyref->getRefer();

                // Assert
                expect($refer)->toBeNull();
            });
        });

        describe('Edge Cases', function (): void {
            test('creates keyref constraint with empty refer value', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $keyref->refer('');

                // Assert
                expect($keyref->getRefer())->toBe('');
            });

            test('creates keyref constraint with namespaced refer value', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $keyref->refer('tns:personKey');

                // Assert
                expect($keyref->getRefer())->toBe('tns:personKey');
            });

            test('creates keyref constraint without any fields', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $fields = $keyref->getFields();

                // Assert
                expect($fields)->toBeEmpty();
            });

            test('updates refer value when called multiple times', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $keyref->refer('firstKey')
                    ->refer('secondKey');

                // Assert
                expect($keyref->getRefer())->toBe('secondKey');
            });
        });
    });

    describe('Unique', function (): void {
        describe('Happy Paths', function (): void {
            test('creates unique constraint with name on complex type', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('PersonType');

                // Act
                $unique = $complexType->unique('emailUnique');

                // Assert
                expect($unique)->toBeInstanceOf(Unique::class)
                    ->and($unique->getName())->toBe('emailUnique');
            });

            test('adds selector with xpath to unique constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $selector = $unique->selector('.//person');

                // Assert
                expect($selector)->toBeInstanceOf(Selector::class)
                    ->and($selector->getXpath())->toBe('.//person')
                    ->and($unique->getSelector())->toBe($selector);
            });

            test('adds single field with xpath to unique constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $result = $unique->field('email');

                // Assert
                expect($result)->toBe($unique)
                    ->and($unique->getFields())->toHaveCount(1)
                    ->and($unique->getFields()[0])->toBeInstanceOf(Field::class)
                    ->and($unique->getFields()[0]->getXpath())->toBe('email');
            });

            test('adds multiple fields to unique constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $unique->field('email')
                    ->field('@domain')
                    ->field('username');

                // Assert
                $fields = $unique->getFields();
                expect($fields)->toHaveCount(3)
                    ->and($fields[0]->getXpath())->toBe('email')
                    ->and($fields[1]->getXpath())->toBe('@domain')
                    ->and($fields[2]->getXpath())->toBe('username');
            });

            test('end method returns parent complex type from unique', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $complexType = $wsdl->complexType('PersonType');
                $unique = $complexType->unique('emailUnique');

                // Act
                $result = $unique->end();

                // Assert
                expect($result)->toBe($complexType);
            });

            test('chains unique constraint methods fluently', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $unique = $wsdl->complexType('PersonType')
                    ->unique('emailUnique')
                    ->field('email')
                    ->field('@domain');

                // Assert
                expect($unique)->toBeInstanceOf(Unique::class)
                    ->and($unique->getName())->toBe('emailUnique')
                    ->and($unique->getFields())->toHaveCount(2);
            });

            test('returns null selector when selector not set on unique', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $selector = $unique->getSelector();

                // Assert
                expect($selector)->toBeNull();
            });
        });

        describe('Edge Cases', function (): void {
            test('creates unique constraint with empty xpath in selector', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $selector = $unique->selector('');

                // Assert
                expect($selector->getXpath())->toBe('');
            });

            test('creates unique constraint with empty xpath in field', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $unique->field('');

                // Assert
                expect($unique->getFields()[0]->getXpath())->toBe('');
            });

            test('creates unique constraint without any fields', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $fields = $unique->getFields();

                // Assert
                expect($fields)->toBeEmpty();
            });

            test('creates unique constraint with complex xpath expressions', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $selector = $unique->selector('.//ns:person[@status="active"]');
                $unique->field('ns:contact/email')
                    ->field('@ns:domain');

                // Assert
                expect($selector->getXpath())->toBe('.//ns:person[@status="active"]')
                    ->and($unique->getFields()[0]->getXpath())->toBe('ns:contact/email')
                    ->and($unique->getFields()[1]->getXpath())->toBe('@ns:domain');
            });
        });
    });

    describe('Selector', function (): void {
        describe('Happy Paths', function (): void {
            test('creates selector with xpath for key constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = new Selector($key, './/person');

                // Assert
                expect($selector->getXpath())->toBe('.//person');
            });

            test('creates selector with xpath for keyref constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $selector = new Selector($keyref, './/order');

                // Assert
                expect($selector->getXpath())->toBe('.//order');
            });

            test('creates selector with xpath for unique constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $selector = new Selector($unique, './/person');

                // Assert
                expect($selector->getXpath())->toBe('.//person');
            });

            test('end method returns parent key constraint from selector', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');
                $selector = $key->selector('.//person');

                // Act
                $result = $selector->end();

                // Assert
                expect($result)->toBe($key);
            });

            test('end method returns parent keyref constraint from selector', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');
                $selector = $keyref->selector('.//order');

                // Act
                $result = $selector->end();

                // Assert
                expect($result)->toBe($keyref);
            });

            test('end method returns parent unique constraint from selector', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');
                $selector = $unique->selector('.//person');

                // Act
                $result = $selector->end();

                // Assert
                expect($result)->toBe($unique);
            });
        });

        describe('Edge Cases', function (): void {
            test('creates selector with empty xpath', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = new Selector($key, '');

                // Assert
                expect($selector->getXpath())->toBe('');
            });

            test('creates selector with complex xpath expression', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = new Selector($key, './/ns:person[@type="employee" and @status="active"]/ns:profile');

                // Assert
                expect($selector->getXpath())->toBe('.//ns:person[@type="employee" and @status="active"]/ns:profile');
            });

            test('creates selector with xpath containing special characters', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $selector = new Selector($key, './/*[@id="special-chars-123_test"]');

                // Assert
                expect($selector->getXpath())->toBe('.//*[@id="special-chars-123_test"]');
            });
        });
    });

    describe('Field', function (): void {
        describe('Happy Paths', function (): void {
            test('creates field with xpath for key constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $field = new Field($key, '@id');

                // Assert
                expect($field->getXpath())->toBe('@id');
            });

            test('creates field with xpath for keyref constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');

                // Act
                $field = new Field($keyref, '@personId');

                // Assert
                expect($field->getXpath())->toBe('@personId');
            });

            test('creates field with xpath for unique constraint', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');

                // Act
                $field = new Field($unique, 'email');

                // Assert
                expect($field->getXpath())->toBe('email');
            });

            test('end method returns parent key constraint from field', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');
                $field = new Field($key, '@id');

                // Act
                $result = $field->end();

                // Assert
                expect($result)->toBe($key);
            });

            test('end method returns parent keyref constraint from field', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $keyref = $wsdl->complexType('OrderType')->keyRef('orderPersonRef');
                $field = new Field($keyref, '@personId');

                // Act
                $result = $field->end();

                // Assert
                expect($result)->toBe($keyref);
            });

            test('end method returns parent unique constraint from field', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $unique = $wsdl->complexType('PersonType')->unique('emailUnique');
                $field = new Field($unique, 'email');

                // Act
                $result = $field->end();

                // Assert
                expect($result)->toBe($unique);
            });
        });

        describe('Edge Cases', function (): void {
            test('creates field with empty xpath', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $field = new Field($key, '');

                // Assert
                expect($field->getXpath())->toBe('');
            });

            test('creates field with complex xpath expression', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $field = new Field($key, 'ns:contact/ns:email/@domain');

                // Assert
                expect($field->getXpath())->toBe('ns:contact/ns:email/@domain');
            });

            test('creates field with attribute xpath', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');
                $key = $wsdl->complexType('PersonType')->key('personKey');

                // Act
                $field = new Field($key, '@ns:id');

                // Assert
                expect($field->getXpath())->toBe('@ns:id');
            });
        });
    });

    describe('Integration Tests', function (): void {
        describe('Happy Paths', function (): void {
            test('creates complex type with key constraint via fluent api', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->complexType('PersonType')
                    ->key('personKey')
                    ->selector('.//person')
                    ->end()
                    ->field('@id')
                    ->end();

                // Assert
                expect($result)->toBeInstanceOf(ComplexType::class);
            });

            test('creates complex type with keyref constraint via fluent api', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->complexType('OrderType')
                    ->keyRef('orderPersonRef')
                    ->refer('personKey')
                    ->selector('.//order')
                    ->end()
                    ->field('@personId')
                    ->end();

                // Assert
                expect($result)->toBeInstanceOf(ComplexType::class);
            });

            test('creates complex type with unique constraint via fluent api', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->complexType('PersonType')
                    ->unique('emailUnique')
                    ->selector('.//person')
                    ->end()
                    ->field('email')
                    ->end();

                // Assert
                expect($result)->toBeInstanceOf(ComplexType::class);
            });

            test('creates complex type with multiple constraints', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $complexType = $wsdl->complexType('PersonType')
                    ->key('personKey')
                    ->selector('.//person')
                    ->end()
                    ->field('@id')
                    ->end()
                    ->unique('emailUnique')
                    ->selector('.//person')
                    ->end()
                    ->field('email')
                    ->end();

                // Assert
                expect($complexType)->toBeInstanceOf(ComplexType::class);
            });

            test('creates complex type with key and multiple fields via fluent api', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->complexType('PersonType')
                    ->key('personKey')
                    ->selector('.//person')
                    ->end()
                    ->field('@id')
                    ->field('@ssn')
                    ->field('name')
                    ->end();

                // Assert
                expect($result)->toBeInstanceOf(ComplexType::class);
            });

            test('creates complex type with keyref and refer via fluent api', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->complexType('OrderType')
                    ->keyRef('orderPersonRef')
                    ->selector('.//order')
                    ->end()
                    ->refer('personKey')
                    ->field('@personId')
                    ->field('@departmentId')
                    ->end();

                // Assert
                expect($result)->toBeInstanceOf(ComplexType::class);
            });

            test('creates complete person and order types with referential integrity', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $personType = $wsdl->complexType('PersonType')
                    ->key('personKey')
                    ->selector('.//person')
                    ->end()
                    ->field('@id')
                    ->end()
                    ->unique('emailUnique')
                    ->selector('.//person')
                    ->end()
                    ->field('email')
                    ->end();

                $orderType = $wsdl->complexType('OrderType')
                    ->keyRef('orderPersonRef')
                    ->refer('personKey')
                    ->selector('.//order')
                    ->end()
                    ->field('@personId')
                    ->end();

                // Assert
                expect($personType)->toBeInstanceOf(ComplexType::class)
                    ->and($orderType)->toBeInstanceOf(ComplexType::class);
            });
        });

        describe('Edge Cases', function (): void {
            test('creates constraint chain ending at different levels', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act - end at selector level
                $key = $wsdl->complexType('PersonType')
                    ->key('personKey')
                    ->selector('.//person');

                // Assert
                expect($key)->toBeInstanceOf(Selector::class);
            });

            test('creates complex type with constraint without selector', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->complexType('PersonType')
                    ->key('personKey')
                    ->field('@id')
                    ->end();

                // Assert
                expect($result)->toBeInstanceOf(ComplexType::class);
            });

            test('creates complex type with namespaced constraint names', function (): void {
                // Arrange
                $wsdl = Wsdl::create('TestService', 'http://test.example.com/');

                // Act
                $result = $wsdl->complexType('PersonType')
                    ->key('tns:personKey')
                    ->selector('.//tns:person')
                    ->end()
                    ->field('@tns:id')
                    ->end()
                    ->keyRef('tns:orderPersonRef')
                    ->refer('tns:personKey')
                    ->selector('.//tns:order')
                    ->end()
                    ->field('@tns:personId')
                    ->end();

                // Assert
                expect($result)->toBeInstanceOf(ComplexType::class);
            });
        });
    });
});
