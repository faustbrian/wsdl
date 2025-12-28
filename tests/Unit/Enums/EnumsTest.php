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

describe('SoapVersion', function (): void {
    test('has SOAP 1.1 version', function (): void {
        expect(SoapVersion::Soap11->value)->toBe('1.1');
    });

    test('has SOAP 1.2 version', function (): void {
        expect(SoapVersion::Soap12->value)->toBe('1.2');
    });

    test('returns correct namespace for SOAP 1.1', function (): void {
        expect(SoapVersion::Soap11->namespace())->toBe(Wsdl::SOAP_NS);
    });

    test('returns correct namespace for SOAP 1.2', function (): void {
        expect(SoapVersion::Soap12->namespace())->toBe(Wsdl::SOAP12_NS);
    });
});

describe('BindingStyle', function (): void {
    test('has document style', function (): void {
        expect(BindingStyle::Document->value)->toBe('document');
    });

    test('has rpc style', function (): void {
        expect(BindingStyle::Rpc->value)->toBe('rpc');
    });
});

describe('BindingUse', function (): void {
    test('has literal use', function (): void {
        expect(BindingUse::Literal->value)->toBe('literal');
    });

    test('has encoded use', function (): void {
        expect(BindingUse::Encoded->value)->toBe('encoded');
    });
});

describe('XsdType', function (): void {
    describe('Primitive Types', function (): void {
        test('has string type', function (): void {
            expect(XsdType::String->value)->toBe('string');
        });

        test('has boolean type', function (): void {
            expect(XsdType::Boolean->value)->toBe('boolean');
        });

        test('has decimal type', function (): void {
            expect(XsdType::Decimal->value)->toBe('decimal');
        });

        test('has float type', function (): void {
            expect(XsdType::Float->value)->toBe('float');
        });

        test('has double type', function (): void {
            expect(XsdType::Double->value)->toBe('double');
        });

        test('has duration type', function (): void {
            expect(XsdType::Duration->value)->toBe('duration');
        });

        test('has dateTime type', function (): void {
            expect(XsdType::DateTime->value)->toBe('dateTime');
        });

        test('has time type', function (): void {
            expect(XsdType::Time->value)->toBe('time');
        });

        test('has date type', function (): void {
            expect(XsdType::Date->value)->toBe('date');
        });

        test('has hexBinary type', function (): void {
            expect(XsdType::HexBinary->value)->toBe('hexBinary');
        });

        test('has base64Binary type', function (): void {
            expect(XsdType::Base64Binary->value)->toBe('base64Binary');
        });

        test('has anyURI type', function (): void {
            expect(XsdType::AnyUri->value)->toBe('anyURI');
        });

        test('has QName type', function (): void {
            expect(XsdType::QName->value)->toBe('QName');
        });
    });

    describe('Derived Types', function (): void {
        test('has int type', function (): void {
            expect(XsdType::Int->value)->toBe('int');
        });

        test('has integer type', function (): void {
            expect(XsdType::Integer->value)->toBe('integer');
        });

        test('has long type', function (): void {
            expect(XsdType::Long->value)->toBe('long');
        });

        test('has short type', function (): void {
            expect(XsdType::Short->value)->toBe('short');
        });

        test('has byte type', function (): void {
            expect(XsdType::Byte->value)->toBe('byte');
        });

        test('has unsignedInt type', function (): void {
            expect(XsdType::UnsignedInt->value)->toBe('unsignedInt');
        });

        test('has unsignedLong type', function (): void {
            expect(XsdType::UnsignedLong->value)->toBe('unsignedLong');
        });

        test('has unsignedShort type', function (): void {
            expect(XsdType::UnsignedShort->value)->toBe('unsignedShort');
        });

        test('has unsignedByte type', function (): void {
            expect(XsdType::UnsignedByte->value)->toBe('unsignedByte');
        });

        test('has positiveInteger type', function (): void {
            expect(XsdType::PositiveInteger->value)->toBe('positiveInteger');
        });

        test('has negativeInteger type', function (): void {
            expect(XsdType::NegativeInteger->value)->toBe('negativeInteger');
        });

        test('has nonPositiveInteger type', function (): void {
            expect(XsdType::NonPositiveInteger->value)->toBe('nonPositiveInteger');
        });

        test('has nonNegativeInteger type', function (): void {
            expect(XsdType::NonNegativeInteger->value)->toBe('nonNegativeInteger');
        });

        test('has normalizedString type', function (): void {
            expect(XsdType::NormalizedString->value)->toBe('normalizedString');
        });

        test('has token type', function (): void {
            expect(XsdType::Token->value)->toBe('token');
        });

        test('has language type', function (): void {
            expect(XsdType::Language->value)->toBe('language');
        });

        test('has Name type', function (): void {
            expect(XsdType::Name->value)->toBe('Name');
        });

        test('has NCName type', function (): void {
            expect(XsdType::NCName->value)->toBe('NCName');
        });

        test('has ID type', function (): void {
            expect(XsdType::ID->value)->toBe('ID');
        });

        test('has IDREF type', function (): void {
            expect(XsdType::IDREF->value)->toBe('IDREF');
        });

        test('has IDREFS type', function (): void {
            expect(XsdType::IDREFS->value)->toBe('IDREFS');
        });

        test('has anyType type', function (): void {
            expect(XsdType::AnyType->value)->toBe('anyType');
        });
    });
});
