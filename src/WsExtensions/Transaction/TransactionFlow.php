<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Transaction;

use Cline\WsdlBuilder\WsExtensions\Transaction\Enums\TransactionFlowType;

/**
 * Transaction flow options for WS-Transaction.
 *
 * Specifies how transactions flow between services and the capabilities
 * of each service endpoint.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TransactionFlow
{
    private TransactionFlowType $flowType = TransactionFlowType::Supported;

    private bool $atAssertion = false;

    private bool $atAlwaysCapability = false;

    /**
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public function __construct(
        private readonly mixed $parent = null,
    ) {}

    /**
     * Set the transaction flow type.
     *
     * @param string|TransactionFlowType $flowType Flow type (mandatory, supported, allowed, notAllowed)
     */
    public function flowType(TransactionFlowType|string $flowType): self
    {
        $this->flowType = $flowType instanceof TransactionFlowType ? $flowType : TransactionFlowType::from($flowType);

        return $this;
    }

    /**
     * Set transaction flow as mandatory.
     * The service requires a transaction context.
     */
    public function mandatory(): self
    {
        $this->flowType = TransactionFlowType::Mandatory;

        return $this;
    }

    /**
     * Set transaction flow as supported.
     * The service supports transactions but doesn't require them.
     */
    public function supported(): self
    {
        $this->flowType = TransactionFlowType::Supported;

        return $this;
    }

    /**
     * Set transaction flow as allowed.
     * The service allows transactions to flow but doesn't actively support them.
     */
    public function allowed(): self
    {
        $this->flowType = TransactionFlowType::Allowed;

        return $this;
    }

    /**
     * Set transaction flow as not allowed.
     * The service does not allow transactions.
     */
    public function notAllowed(): self
    {
        $this->flowType = TransactionFlowType::NotAllowed;

        return $this;
    }

    /**
     * Enable ATAssertion.
     * Indicates the endpoint supports WS-AtomicTransaction assertions.
     */
    public function atAssertion(bool $enabled = true): self
    {
        $this->atAssertion = $enabled;

        return $this;
    }

    /**
     * Enable ATAlwaysCapability.
     * Indicates the endpoint always provides WS-AtomicTransaction capability.
     */
    public function atAlwaysCapability(bool $enabled = true): self
    {
        $this->atAlwaysCapability = $enabled;

        return $this;
    }

    /**
     * Get the flow type.
     */
    public function getFlowType(): TransactionFlowType
    {
        return $this->flowType;
    }

    /**
     * Get whether ATAssertion is enabled.
     */
    public function isAtAssertion(): bool
    {
        return $this->atAssertion;
    }

    /**
     * Get whether ATAlwaysCapability is enabled.
     */
    public function isAtAlwaysCapability(): bool
    {
        return $this->atAlwaysCapability;
    }

    /**
     * Get configuration as array.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = [
            'flowType' => $this->flowType->value,
        ];

        if ($this->atAssertion) {
            $config['atAssertion'] = true;
        }

        if ($this->atAlwaysCapability) {
            $config['atAlwaysCapability'] = true;
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
