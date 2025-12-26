<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Mime;

/**
 * Represents a MIME mime:mimeXml element for SOAP with Attachments (SwA).
 * Specifies that a part should be in XML format.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MimeXml
{
    public function __construct(
        private readonly ?string $part = null,
    ) {}

    /**
     * Create a new MimeXml instance.
     */
    public static function create(?string $part = null): self
    {
        return new self($part);
    }

    /**
     * Get the part name.
     */
    public function getPart(): ?string
    {
        return $this->part;
    }
}
