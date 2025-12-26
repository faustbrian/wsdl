<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Enums;

/**
 * SOAP protocol versions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum SoapVersion: string
{
    case Soap11 = '1.1';
    case Soap12 = '1.2';

    public function namespace(): string
    {
        return match ($this) {
            self::Soap11 => 'http://schemas.xmlsoap.org/wsdl/soap/',
            self::Soap12 => 'http://schemas.xmlsoap.org/wsdl/soap12/',
        };
    }

    public function envelopeNamespace(): string
    {
        return match ($this) {
            self::Soap11 => 'http://schemas.xmlsoap.org/soap/envelope/',
            self::Soap12 => 'http://www.w3.org/2003/05/soap-envelope',
        };
    }
}
