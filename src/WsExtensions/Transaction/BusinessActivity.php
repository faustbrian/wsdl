<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Transaction;

/**
 * WS-BusinessActivity policy assertion.
 *
 * Represents a long-running transaction protocol that supports compensation
 * and relaxed isolation. Suitable for business processes that span multiple
 * services and require eventual consistency rather than ACID properties.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class BusinessActivity
{
    private string $version = '1.0';

    /**
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly mixed $parent = null,
    ) {}

    /**
     * Set the WS-BusinessActivity version.
     *
     * @param string $version Version number (1.0, 1.1, or 1.2)
     */
    public function version(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'version' => $this->version,
        ];
    }

    /**
     * Return to parent or return config array.
     *
     * @return array<string, mixed>|mixed
     */
    public function end(): mixed
    {
        return $this->parent ?? $this->getConfig();
    }
}
