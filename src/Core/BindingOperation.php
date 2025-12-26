<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Core;

use Cline\WsdlBuilder\Enums\BindingStyle;
use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Soap\Header;
use Cline\WsdlBuilder\WsExtensions\Mime\MimeMultipartRelated;
use Cline\WsdlBuilder\WsExtensions\Http\HttpOperation;
use Cline\WsdlBuilder\WsExtensions\Http\HttpUrlEncoded;
use Cline\WsdlBuilder\WsExtensions\Http\HttpUrlReplacement;

/**
 * Represents an operation within a binding.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class BindingOperation
{
    /** @var array<int, Header> */
    private array $headers = [];

    private ?MimeMultipartRelated $inputMime = null;

    private ?MimeMultipartRelated $outputMime = null;

    private ?HttpOperation $httpOperation = null;

    private ?HttpUrlEncoded $httpUrlEncoded = null;

    private ?HttpUrlReplacement $httpUrlReplacement = null;

    public function __construct(
        public readonly string $name,
        public readonly string $soapAction,
        public readonly BindingStyle $style,
        public readonly BindingUse $use,
    ) {}

    /**
     * Add a SOAP header to this operation.
     */
    public function addHeader(Header $header): void
    {
        $this->headers[] = $header;
    }

    /**
     * @return array<int, Header>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set MIME multipart for input message.
     */
    public function setInputMime(MimeMultipartRelated $mime): self
    {
        $this->inputMime = $mime;

        return $this;
    }

    /**
     * Set MIME multipart for output message.
     */
    public function setOutputMime(MimeMultipartRelated $mime): self
    {
        $this->outputMime = $mime;

        return $this;
    }

    /**
     * Get MIME multipart for input message.
     */
    public function getInputMime(): ?MimeMultipartRelated
    {
        return $this->inputMime;
    }

    /**
     * Get MIME multipart for output message.
     */
    public function getOutputMime(): ?MimeMultipartRelated
    {
        return $this->outputMime;
    }

    /**
     * Set HTTP operation with location.
     */
    public function setHttpOperation(HttpOperation $httpOperation): self
    {
        $this->httpOperation = $httpOperation;

        return $this;
    }

    /**
     * Get HTTP operation.
     */
    public function getHttpOperation(): ?HttpOperation
    {
        return $this->httpOperation;
    }

    /**
     * Set HTTP URL-encoded input format.
     */
    public function setHttpUrlEncoded(HttpUrlEncoded $httpUrlEncoded): self
    {
        $this->httpUrlEncoded = $httpUrlEncoded;

        return $this;
    }

    /**
     * Get HTTP URL-encoded input format.
     */
    public function getHttpUrlEncoded(): ?HttpUrlEncoded
    {
        return $this->httpUrlEncoded;
    }

    /**
     * Set HTTP URL-replacement input format.
     */
    public function setHttpUrlReplacement(HttpUrlReplacement $httpUrlReplacement): self
    {
        $this->httpUrlReplacement = $httpUrlReplacement;

        return $this;
    }

    /**
     * Get HTTP URL-replacement input format.
     */
    public function getHttpUrlReplacement(): ?HttpUrlReplacement
    {
        return $this->httpUrlReplacement;
    }
}
