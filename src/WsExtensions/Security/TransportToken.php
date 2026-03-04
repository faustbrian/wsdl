<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Security;

/**
 * WS-SecurityPolicy TransportToken assertion.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TransportToken
{
    private bool $httpsToken = false;

    private bool $requireClientCertificate = false;

    public function __construct(
        private readonly TransportBinding $parent,
    ) {}

    /**
     * Specify HTTPS token.
     */
    public function httpsToken(): self
    {
        $this->httpsToken = true;

        return $this;
    }

    /**
     * Require client certificate.
     */
    public function requireClientCertificate(bool $require = true): self
    {
        $this->requireClientCertificate = $require;

        return $this;
    }

    /**
     * Get whether HTTPS token is specified.
     */
    public function isHttpsToken(): bool
    {
        return $this->httpsToken;
    }

    /**
     * Get whether client certificate is required.
     */
    public function isClientCertificateRequired(): bool
    {
        return $this->requireClientCertificate;
    }

    /**
     * Return to parent TransportBinding.
     */
    public function end(): TransportBinding
    {
        return $this->parent;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'httpsToken' => $this->httpsToken,
            'requireClientCertificate' => $this->requireClientCertificate,
        ];
    }
}
