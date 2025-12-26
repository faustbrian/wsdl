<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Mtom;

/**
 * Represents an xop:Include element for binary content references.
 *
 * The xop:Include element is used within XSD schema types to indicate
 * that binary content should be transmitted as an optimized MIME attachment
 * rather than inline base64-encoded data.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final readonly class XopInclude
{
    public const string NAMESPACE_URI = 'http://www.w3.org/2004/08/xop/include';

    /**
     * @param string $href Content-ID URL reference (e.g., "cid:example@example.org")
     */
    public function __construct(
        public string $href,
    ) {}

    /**
     * Create an XopInclude instance.
     */
    public static function create(string $href): self
    {
        return new self($href);
    }
}
