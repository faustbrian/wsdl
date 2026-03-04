<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\ResourceFramework;

use Cline\WsdlBuilder\WsExtensions\Addressing\EndpointReference;

/**
 * Factory for WS-ResourceFramework policy assertions.
 *
 * Provides static methods that return assertion objects for use
 * with WS-Policy. Supports WS-Resource, WS-ResourceProperties,
 * and WS-ResourceLifetime protocols.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ResourceFrameworkPolicy
{
    public const string NAMESPACE_WSRF_R = 'http://docs.oasis-open.org/wsrf/r-2';

    public const string NAMESPACE_WSRF_RP = 'http://docs.oasis-open.org/wsrf/rp-2';

    public const string NAMESPACE_WSRF_RL = 'http://docs.oasis-open.org/wsrf/rl-2';

    /**
     * Create a Resource assertion.
     *
     * @param string     $address Endpoint address
     * @param null|mixed $parent  Optional parent object for fluent chaining
     */
    public static function resource(string $address, mixed $parent = null): Resource
    {
        $endpointReference = new EndpointReference($address);

        return new Resource($endpointReference, $parent);
    }

    /**
     * Create a ResourceProperties assertion.
     *
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public static function resourceProperties(mixed $parent = null): ResourceProperties
    {
        return new ResourceProperties($parent);
    }

    /**
     * Create a ResourceLifetime assertion.
     *
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public static function lifetime(mixed $parent = null): ResourceLifetime
    {
        return new ResourceLifetime($parent);
    }

    /**
     * Create a GetResourceProperty request.
     *
     * @param string     $resourceProperty Property name (QName)
     * @param null|mixed $parent           Optional parent object for fluent chaining
     */
    public static function getResourceProperty(string $resourceProperty, mixed $parent = null): GetResourceProperty
    {
        return new GetResourceProperty($resourceProperty, $parent);
    }

    /**
     * Create a SetResourceProperties request.
     *
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public static function setResourceProperties(mixed $parent = null): SetResourceProperties
    {
        return new SetResourceProperties($parent);
    }
}
