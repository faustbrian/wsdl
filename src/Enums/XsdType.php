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
    case String = 'string';
    case Boolean = 'boolean';
    case Decimal = 'decimal';
    case Float = 'float';
    case Double = 'double';
    case Duration = 'duration';
    case DateTime = 'dateTime';
    case Time = 'time';
    case Date = 'date';
    case HexBinary = 'hexBinary';
    case Base64Binary = 'base64Binary';
    case AnyUri = 'anyURI';
    case QName = 'QName';

    // Derived types
    case Integer = 'integer';
    case Int = 'int';
    case Long = 'long';
    case Short = 'short';
    case Byte = 'byte';
    case NonNegativeInteger = 'nonNegativeInteger';
    case PositiveInteger = 'positiveInteger';
    case NonPositiveInteger = 'nonPositiveInteger';
    case NegativeInteger = 'negativeInteger';
    case UnsignedLong = 'unsignedLong';
    case UnsignedInt = 'unsignedInt';
    case UnsignedShort = 'unsignedShort';
    case UnsignedByte = 'unsignedByte';

    // String types
    case NormalizedString = 'normalizedString';
    case Token = 'token';
    case Language = 'language';
    case Name = 'Name';
    case NCName = 'NCName';
    case ID = 'ID';
    case IDREF = 'IDREF';
    case IDREFS = 'IDREFS';

    // Special types
    case AnyType = 'anyType';
    case AnySimpleType = 'anySimpleType';

    // SOAP attachment types
    case SwaRef = 'swaRef';

    public function localName(): string
    {
        return $this->value;
    }

    /**
     * Get the prefixed type name for WSDL 1.1 (xsd: prefix).
     */
    public function forWsdl1(): string
    {
        return $this->value === 'swaRef' ? $this->value : 'xsd:'.$this->value;
    }

    /**
     * Get the prefixed type name for WSDL 2.0 (xs: prefix).
     */
    public function forWsdl2(): string
    {
        return $this->value === 'swaRef' ? $this->value : 'xs:'.$this->value;
    }
}
