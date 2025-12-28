<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Mime;

/**
 * Represents a MIME mime:multipartRelated element for SOAP with Attachments (SwA).
 * Contains multiple MIME parts that are related to each other.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MimeMultipartRelated
{
    /** @var array<int, MimePart> */
    private array $parts = [];

    public function __construct(
        private readonly ?object $parent = null,
    ) {}

    /**
     * Create a new MimeMultipartRelated instance.
     */
    public static function create(?object $parent = null): self
    {
        return new self($parent);
    }

    /**
     * Add a new MIME part.
     */
    public function part(?string $name = null): MimePart
    {
        $part = new MimePart($name, $this);
        $this->parts[] = $part;

        return $part;
    }

    /**
     * Add a new MIME part with content in one call.
     */
    public function mimePart(string $part, string $contentType): self
    {
        $mimePart = new MimePart(null, $this);
        $mimePart->content($part, $contentType);
        $this->parts[] = $mimePart;

        return $this;
    }

    /**
     * Add a named MIME part with content.
     */
    public function mimePartNamed(string $name, string $part, string $contentType): self
    {
        $mimePart = new MimePart($name, $this);
        $mimePart->content($part, $contentType);
        $this->parts[] = $mimePart;

        return $this;
    }

    /**
     * Add a MIME part that references the SOAP body.
     */
    public function soapBodyPart(): self
    {
        $mimePart = new MimePart(null, $this);
        $mimePart->soapBody();
        $this->parts[] = $mimePart;

        return $this;
    }

    /**
     * Add an existing MimePart instance.
     */
    public function addPart(MimePart $part): self
    {
        $this->parts[] = $part;

        return $this;
    }

    /**
     * Get all MIME parts.
     *
     * @return array<int, MimePart>
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * Return to the parent object (for fluent interface).
     */
    public function end(): ?object
    {
        return $this->parent;
    }
}
