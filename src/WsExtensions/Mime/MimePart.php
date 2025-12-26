<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\WsExtensions\Mime;

/**
 * Represents a MIME mime:part element for SOAP with Attachments (SwA).
 * A part can contain either MimeContent or reference a SOAP body.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class MimePart
{
    private ?MimeContent $mimeContent = null;

    private bool $hasSoapBody = false;

    public function __construct(
        private readonly ?string $name = null,
        private readonly ?object $parent = null,
    ) {}

    /**
     * Create a new MimePart instance.
     */
    public static function create(?string $name = null, ?object $parent = null): self
    {
        return new self($name, $parent);
    }

    /**
     * Set MIME content for this part.
     */
    public function content(string $part, string $type): self
    {
        $this->mimeContent = new MimeContent($part, $type);

        return $this;
    }

    /**
     * Set MIME content using a MimeContent instance.
     */
    public function setMimeContent(MimeContent $content): self
    {
        $this->mimeContent = $content;

        return $this;
    }

    /**
     * Indicate this part references a SOAP body.
     */
    public function soapBody(bool $hasSoapBody = true): self
    {
        $this->hasSoapBody = $hasSoapBody;

        return $this;
    }

    /**
     * Get the part name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the MIME content.
     */
    public function getMimeContent(): ?MimeContent
    {
        return $this->mimeContent;
    }

    /**
     * Check if this part has a SOAP body reference.
     */
    public function hasSoapBody(): bool
    {
        return $this->hasSoapBody;
    }

    /**
     * Return to the parent object (for fluent interface).
     */
    public function end(): ?object
    {
        return $this->parent;
    }
}
