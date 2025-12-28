<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Trust;

use Cline\WsdlBuilder\WsExtensions\Trust\Enums\KeyType;
use Cline\WsdlBuilder\WsExtensions\Trust\Enums\TokenType;

/**
 * Represents a WS-Trust RequestSecurityToken template.
 *
 * Provides a fluent interface for building RST templates with token type,
 * key type, key size, and claims requirements.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class RequestSecurityToken
{
    private ?TokenType $tokenType = null;

    private ?KeyType $keyType = null;

    private ?int $keySize = null;

    private ?Claims $claims = null;

    /**
     * Set the token type for the request.
     */
    public function tokenType(TokenType $tokenType): self
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    /**
     * Set the key type for the request.
     */
    public function keyType(KeyType $keyType): self
    {
        $this->keyType = $keyType;

        return $this;
    }

    /**
     * Set the key size for the request (in bits).
     */
    public function keySize(int $keySize): self
    {
        $this->keySize = $keySize;

        return $this;
    }

    /**
     * Set the claims for the request.
     */
    public function claims(Claims $claims): self
    {
        $this->claims = $claims;

        return $this;
    }

    /**
     * Get the token type.
     */
    public function getTokenType(): ?TokenType
    {
        return $this->tokenType;
    }

    /**
     * Get the key type.
     */
    public function getKeyType(): ?KeyType
    {
        return $this->keyType;
    }

    /**
     * Get the key size.
     */
    public function getKeySize(): ?int
    {
        return $this->keySize;
    }

    /**
     * Get the claims.
     */
    public function getClaims(): ?Claims
    {
        return $this->claims;
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $config = [];

        if ($this->tokenType instanceof TokenType) {
            $config['tokenType'] = $this->tokenType->value;
        }

        if ($this->keyType instanceof KeyType) {
            $config['keyType'] = $this->keyType->value;
        }

        if ($this->keySize !== null) {
            $config['keySize'] = $this->keySize;
        }

        if ($this->claims instanceof Claims) {
            $config['claims'] = $this->claims->toArray();
        }

        return $config;
    }
}
