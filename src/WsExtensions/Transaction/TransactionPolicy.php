<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Transaction;

/**
 * Factory for WS-Transaction policy assertions.
 *
 * Provides static methods that return assertion objects or arrays for use
 * with WS-Policy. Supports both WS-AtomicTransaction (WS-AT) and
 * WS-BusinessActivity (WS-BA) protocols.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class TransactionPolicy
{
    public const string NAMESPACE_WSAT = 'http://docs.oasis-open.org/ws-tx/wsat/2006/06';

    public const string NAMESPACE_WSBA = 'http://docs.oasis-open.org/ws-tx/wsba/2006/06';

    public const string NAMESPACE_WSCOOR = 'http://docs.oasis-open.org/ws-tx/wscoor/2006/06';

    /**
     * Create an AtomicTransaction assertion.
     *
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public static function atomicTransaction(mixed $parent = null): AtomicTransaction
    {
        return new AtomicTransaction($parent);
    }

    /**
     * Create a BusinessActivity assertion.
     *
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public static function businessActivity(mixed $parent = null): BusinessActivity
    {
        return new BusinessActivity($parent);
    }

    /**
     * Create a TransactionFlow configuration.
     *
     * @param null|mixed $parent Optional parent object for fluent chaining
     */
    public static function transactionFlow(mixed $parent = null): TransactionFlow
    {
        return new TransactionFlow($parent);
    }

    /**
     * Create a simple AT assertion array.
     *
     * @param  string               $version AT version (1.0, 1.1, or 1.2)
     * @return array<string, mixed>
     */
    public static function at(string $version = '1.0'): array
    {
        return [
            'type' => 'wsat:ATAssertion',
            'namespace' => self::NAMESPACE_WSAT,
            'version' => $version,
        ];
    }

    /**
     * Create a simple BA assertion array.
     *
     * @param  string               $version BA version (1.0, 1.1, or 1.2)
     * @return array<string, mixed>
     */
    public static function ba(string $version = '1.0'): array
    {
        return [
            'type' => 'wsba:BAAssertion',
            'namespace' => self::NAMESPACE_WSBA,
            'version' => $version,
        ];
    }

    /**
     * Create a coordination context assertion.
     *
     * @return array<string, mixed>
     */
    public static function coordinationContext(): array
    {
        return [
            'type' => 'wscoor:CoordinationContext',
            'namespace' => self::NAMESPACE_WSCOOR,
        ];
    }

    /**
     * Create an ATAlwaysCapability assertion.
     *
     * @return array<string, mixed>
     */
    public static function atAlwaysCapability(): array
    {
        return [
            'type' => 'wsat:ATAlwaysCapability',
            'namespace' => self::NAMESPACE_WSAT,
        ];
    }

    /**
     * Create a BAAtomicOutcome assertion.
     *
     * @return array<string, mixed>
     */
    public static function baAtomicOutcome(): array
    {
        return [
            'type' => 'wsba:BAAtomicOutcome',
            'namespace' => self::NAMESPACE_WSBA,
        ];
    }

    /**
     * Create a BAMixedOutcome assertion.
     *
     * @return array<string, mixed>
     */
    public static function baMixedOutcome(): array
    {
        return [
            'type' => 'wsba:BAMixedOutcome',
            'namespace' => self::NAMESPACE_WSBA,
        ];
    }
}
