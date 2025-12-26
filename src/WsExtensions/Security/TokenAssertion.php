<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Security;

use Cline\WsdlBuilder\WsExtensions\Security\Enums\SecurityTokenInclusion;

/**
 * Base class for WS-SecurityPolicy token assertions.
 *
 * @author Brian Faust <brian@cline.sh>
 */
class TokenAssertion
{
    private ?SecurityTokenInclusion $includeToken = null;

    public function __construct(
        private readonly string $tokenType,
    ) {}

    /**
     * Set the token inclusion policy.
     */
    public function includeToken(SecurityTokenInclusion $inclusion): self
    {
        $this->includeToken = $inclusion;

        return $this;
    }

    /**
     * Get the token type.
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * Get the token inclusion policy.
     */
    public function getIncludeToken(): ?SecurityTokenInclusion
    {
        return $this->includeToken;
    }

    /**
     * Convert to array representation for policy generation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $config = [
            'tokenType' => $this->tokenType,
        ];

        if ($this->includeToken !== null) {
            $config['includeToken'] = $this->includeToken->value;
        }

        return $config;
    }
}
