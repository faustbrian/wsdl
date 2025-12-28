<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Security;

use Cline\WsdlBuilder\WsExtensions\Security\Enums\AlgorithmSuite;

/**
 * WS-SecurityPolicy TransportBinding assertion.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TransportBinding
{
    private ?TransportToken $transportToken = null;

    private ?AlgorithmSuite $algorithmSuite = null;

    private bool $includeTimestamp = false;

    private ?string $layout = null;

    /**
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly mixed $parent = null,
    ) {}

    /**
     * Configure the transport token.
     */
    public function transportToken(): TransportToken
    {
        $this->transportToken = new TransportToken($this);

        return $this->transportToken;
    }

    /**
     * Set the algorithm suite.
     */
    public function algorithmSuite(AlgorithmSuite|string $suite): self
    {
        $this->algorithmSuite = $suite instanceof AlgorithmSuite ? $suite : AlgorithmSuite::from($suite);

        return $this;
    }

    /**
     * Include timestamp in the security header.
     */
    public function includeTimestamp(bool $include = true): self
    {
        $this->includeTimestamp = $include;

        return $this;
    }

    /**
     * Set the layout policy.
     *
     * Valid values: Strict, Lax, LaxTsFirst, LaxTsLast
     */
    public function layout(string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get the transport token.
     */
    public function getTransportToken(): ?TransportToken
    {
        return $this->transportToken;
    }

    /**
     * Get the algorithm suite.
     */
    public function getAlgorithmSuite(): ?AlgorithmSuite
    {
        return $this->algorithmSuite;
    }

    /**
     * Get whether timestamp is included.
     */
    public function isTimestampIncluded(): bool
    {
        return $this->includeTimestamp;
    }

    /**
     * Get the layout policy.
     */
    public function getLayout(): ?string
    {
        return $this->layout;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = [
            'includeTimestamp' => $this->includeTimestamp,
        ];

        if ($this->transportToken !== null) {
            $config['transportToken'] = $this->transportToken->getConfig();
        }

        if ($this->algorithmSuite !== null) {
            $config['algorithmSuite'] = $this->algorithmSuite->value;
        }

        if ($this->layout !== null) {
            $config['layout'] = $this->layout;
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
