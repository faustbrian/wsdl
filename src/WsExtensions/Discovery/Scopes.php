<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Discovery;

use Cline\WsdlBuilder\WsExtensions\Discovery\Enums\ScopeMatchType;

/**
 * Represents WS-Discovery scopes for service discovery.
 *
 * Scopes provide a way to organize and filter services in discovery.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Scopes
{
    /**
     * @param array<string> $values Array of scope URIs
     */
    public function __construct(
        private readonly ScopeMatchType $matchBy = ScopeMatchType::RFC3986,
        private readonly array $values = [],
    ) {}

    /**
     * Create Scopes with RFC3986 matching.
     *
     * @param array<string> $values
     */
    public static function rfc3986(array $values): self
    {
        return new self(ScopeMatchType::RFC3986, $values);
    }

    /**
     * Create Scopes with UUID matching.
     *
     * @param array<string> $values
     */
    public static function uuid(array $values): self
    {
        return new self(ScopeMatchType::UUID, $values);
    }

    /**
     * Create Scopes with LDAP matching.
     *
     * @param array<string> $values
     */
    public static function ldap(array $values): self
    {
        return new self(ScopeMatchType::LDAP, $values);
    }

    /**
     * Create Scopes with string comparison matching.
     *
     * @param array<string> $values
     */
    public static function strcmp0(array $values): self
    {
        return new self(ScopeMatchType::Strcmp0, $values);
    }

    /**
     * Create Scopes with no matching algorithm.
     *
     * @param array<string> $values
     */
    public static function none(array $values): self
    {
        return new self(ScopeMatchType::None, $values);
    }

    public function getMatchBy(): ScopeMatchType
    {
        return $this->matchBy;
    }

    /**
     * @return array<string>
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
