<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Http;

/**
 * Represents http:binding element for HTTP bindings (non-SOAP REST-style).
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class HttpBinding
{
    public const string HTTP_BINDING_NS = 'http://schemas.xmlsoap.org/wsdl/http/';

    /**
     * @param string $verb HTTP verb (GET, POST, PUT, DELETE, etc.)
     */
    public function __construct(
        public string $verb,
    ) {}

    /**
     * Create an HTTP binding with a specific verb.
     */
    public static function create(string $verb): self
    {
        return new self($verb);
    }

    /**
     * Create an HTTP GET binding.
     */
    public static function get(): self
    {
        return new self('GET');
    }

    /**
     * Create an HTTP POST binding.
     */
    public static function post(): self
    {
        return new self('POST');
    }

    /**
     * Create an HTTP PUT binding.
     */
    public static function put(): self
    {
        return new self('PUT');
    }

    /**
     * Create an HTTP DELETE binding.
     */
    public static function delete(): self
    {
        return new self('DELETE');
    }
}
