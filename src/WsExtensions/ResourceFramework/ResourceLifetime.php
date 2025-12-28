<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\ResourceFramework;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Represents WS-ResourceLifetime management (wsrf-rl:ResourceLifetime).
 *
 * Provides a fluent interface for building resource lifetime configurations
 * with termination time, current time, and termination modes.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ResourceLifetime
{
    private ?DateTimeInterface $terminationTime = null;

    private DateTimeInterface $currentTime;

    private bool $scheduledTermination = false;

    private bool $immediateTermination = false;

    /**
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly mixed $parent = null,
    ) {
        $this->currentTime = new DateTimeImmutable();
    }

    /**
     * Set the termination time for this resource.
     */
    public function terminationTime(?DateTimeInterface $terminationTime): self
    {
        $this->terminationTime = $terminationTime;

        return $this;
    }

    /**
     * Set the current time for this resource.
     */
    public function currentTime(DateTimeInterface $currentTime): self
    {
        $this->currentTime = $currentTime;

        return $this;
    }

    /**
     * Enable scheduled termination.
     */
    public function scheduledTermination(bool $scheduledTermination = true): self
    {
        $this->scheduledTermination = $scheduledTermination;

        return $this;
    }

    /**
     * Enable immediate termination.
     */
    public function immediateTermination(bool $immediateTermination = true): self
    {
        $this->immediateTermination = $immediateTermination;

        return $this;
    }

    public function getTerminationTime(): ?DateTimeInterface
    {
        return $this->terminationTime;
    }

    public function getCurrentTime(): DateTimeInterface
    {
        return $this->currentTime;
    }

    public function isScheduledTermination(): bool
    {
        return $this->scheduledTermination;
    }

    public function isImmediateTermination(): bool
    {
        return $this->immediateTermination;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = [
            'currentTime' => $this->currentTime->format(DateTimeInterface::ATOM),
            'scheduledTermination' => $this->scheduledTermination,
            'immediateTermination' => $this->immediateTermination,
        ];

        if ($this->terminationTime !== null) {
            $config['terminationTime'] = $this->terminationTime->format(DateTimeInterface::ATOM);
        }

        return $config;
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
