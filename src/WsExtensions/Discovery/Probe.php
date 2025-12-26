<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Discovery;

/**
 * Represents a WS-Discovery Probe message (service search request).
 *
 * Used to search for services by type and scope.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Probe
{
    /**
     * @param array<string> $types Array of QNames representing service types to search for
     */
    public function __construct(
        private readonly array $types = [],
        private readonly ?Scopes $scopes = null,
    ) {}

    /**
     * Create a Probe with types and optional scopes.
     *
     * @param array<string> $types
     */
    public static function create(array $types = [], ?Scopes $scopes = null): self
    {
        return new self($types, $scopes);
    }

    /**
     * Create a Probe searching for specific types.
     *
     * @param array<string> $types
     */
    public static function forTypes(array $types): self
    {
        return new self($types);
    }

    /**
     * Create a Probe searching in specific scopes.
     */
    public static function inScopes(Scopes $scopes): self
    {
        return new self([], $scopes);
    }

    /**
     * @return array<string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getScopes(): ?Scopes
    {
        return $this->scopes;
    }
}
