<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Mime;

/**
 * Represents a MIME mime:content element for SOAP with Attachments (SwA).
 *
 * @author Brian Faust <brian@cline.sh>
 * @psalm-immutable
 */
final readonly class MimeContent
{
    public const string MIME_NS = 'http://schemas.xmlsoap.org/wsdl/mime/';

    public function __construct(
        private ?string $part = null,
        private ?string $type = null,
    ) {}

    /**
     * Create a new MimeContent instance.
     */
    public static function create(?string $part = null, ?string $type = null): self
    {
        return new self($part, $type);
    }

    /**
     * Get the part name.
     */
    public function getPart(): ?string
    {
        return $this->part;
    }

    /**
     * Get the MIME type.
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}
