<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Discovery;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;

/**
 * Represents a WS-Discovery Bye message (service departure).
 *
 * Sent by a service when it is going offline.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Bye
{
    public function __construct(
        private readonly EndpointReference $endpointReference,
    ) {}

    /**
     * Create a Bye message with an endpoint address.
     */
    public static function create(string $address): self
    {
        return new self(
            new EndpointReference($address),
        );
    }

    public function getEndpointReference(): EndpointReference
    {
        return $this->endpointReference;
    }
}
