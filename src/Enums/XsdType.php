<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Enums;

use function mb_substr;

/**
 * XML Schema data types.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum XsdType: string
{
    // Primitive types
    case String = 'xsd:string';
    case Boolean = 'xsd:boolean';
    case Decimal = 'xsd:decimal';
    case Float = 'xsd:float';
    case Double = 'xsd:double';
    case Duration = 'xsd:duration';
    case DateTime = 'xsd:dateTime';
    case Time = 'xsd:time';
    case Date = 'xsd:date';
    case HexBinary = 'xsd:hexBinary';
    case Base64Binary = 'xsd:base64Binary';
    case AnyUri = 'xsd:anyURI';
    case QName = 'xsd:QName';

    // Derived types
    case Integer = 'xsd:integer';
    case Int = 'xsd:int';
    case Long = 'xsd:long';
    case Short = 'xsd:short';
    case Byte = 'xsd:byte';
    case NonNegativeInteger = 'xsd:nonNegativeInteger';
    case PositiveInteger = 'xsd:positiveInteger';
    case NonPositiveInteger = 'xsd:nonPositiveInteger';
    case NegativeInteger = 'xsd:negativeInteger';
    case UnsignedLong = 'xsd:unsignedLong';
    case UnsignedInt = 'xsd:unsignedInt';
    case UnsignedShort = 'xsd:unsignedShort';
    case UnsignedByte = 'xsd:unsignedByte';

    // String types
    case NormalizedString = 'xsd:normalizedString';
    case Token = 'xsd:token';
    case Language = 'xsd:language';
    case Name = 'xsd:Name';
    case NCName = 'xsd:NCName';
    case ID = 'xsd:ID';
    case IDREF = 'xsd:IDREF';
    case IDREFS = 'xsd:IDREFS';

    // Special types
    case AnyType = 'xsd:anyType';
    case AnySimpleType = 'xsd:anySimpleType';

    // SOAP attachment types
    case SwaRef = 'swaRef';

    public function localName(): string
    {
        return mb_substr($this->value, 4);
    }
}
