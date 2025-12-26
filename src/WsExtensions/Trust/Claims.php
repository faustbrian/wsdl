<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Trust;

/**
 * Represents WS-Federation claims in a WS-Trust request.
 *
 * Provides a fluent interface for building claims requirements.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Claims
{
    public const string DEFAULT_DIALECT_URI = 'http://docs.oasis-open.org/wsfed/authorization/200706/authclaims';

    /**
     * @var array<string>
     */
    private array $claimTypes = [];

    public function __construct(
        private readonly string $dialectUri = self::DEFAULT_DIALECT_URI,
    ) {}

    /**
     * Add a claim type to the claims request.
     */
    public function addClaimType(string $claimType): self
    {
        $this->claimTypes[] = $claimType;

        return $this;
    }

    /**
     * Add multiple claim types to the claims request.
     *
     * @param array<string> $claimTypes
     */
    public function addClaimTypes(array $claimTypes): self
    {
        foreach ($claimTypes as $claimType) {
            $this->addClaimType($claimType);
        }

        return $this;
    }

    /**
     * Get the dialect URI.
     */
    public function getDialectUri(): string
    {
        return $this->dialectUri;
    }

    /**
     * Get all claim types.
     *
     * @return array<string>
     */
    public function getClaimTypes(): array
    {
        return $this->claimTypes;
    }

    /**
     * Convert to array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'dialectUri' => $this->dialectUri,
            'claimTypes' => $this->claimTypes,
        ];
    }
}
