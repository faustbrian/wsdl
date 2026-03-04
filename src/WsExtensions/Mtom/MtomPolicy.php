<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Mtom;

/**
 * Factory for MTOM (Message Transmission Optimization Mechanism) policy assertions.
 *
 * Provides static methods that return assertion arrays for use with WS-Policy.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MtomPolicy
{
    public const string NAMESPACE_URI = 'http://schemas.xmlsoap.org/ws/2004/09/policy/optimizedmimeserialization';

    /**
     * Create an OptimizedMimeSerialization assertion.
     *
     * This assertion indicates that MTOM encoding should be used for binary attachments.
     *
     * @return array<string, mixed>
     */
    public static function optimizedMimeSerialization(): array
    {
        return [
            'type' => 'wsoma:OptimizedMimeSerialization',
            'namespace' => self::NAMESPACE_URI,
        ];
    }
}
