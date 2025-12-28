<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Core;

use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Enums\BindingStyle;
use Cline\WsdlBuilder\Enums\BindingUse;
use Cline\WsdlBuilder\Soap\Header;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Addressing\Action;
use Cline\WsdlBuilder\WsExtensions\Http\HttpBinding;
use Cline\WsdlBuilder\WsExtensions\Http\HttpOperation;
use Cline\WsdlBuilder\WsExtensions\Http\HttpUrlEncoded;
use Cline\WsdlBuilder\WsExtensions\Http\HttpUrlReplacement;
use Cline\WsdlBuilder\WsExtensions\Mime\MimeMultipartRelated;
use Cline\WsdlBuilder\WsExtensions\Policy\PolicyAttachment;
use RuntimeException;

use function array_values;
use function end;

/**
 * Represents a WSDL binding.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Binding
{
    use PolicyAttachment;

    private BindingStyle $style;

    private BindingUse $use;

    private string $transport;

    /** @var array<string, BindingOperation> */
    private array $operations = [];

    private ?Documentation $documentation = null;

    private bool $usingAddressing = false;

    /** @var array<string, Action> */
    private array $actions = [];

    private ?HttpBinding $httpBinding = null;

    public function __construct(
        private readonly Wsdl $wsdl,
        private readonly string $name,
        private readonly string $portType,
    ) {
        $this->style = $wsdl->getDefaultStyle();
        $this->use = $wsdl->getDefaultUse();
        $this->transport = Wsdl::HTTP_TRANSPORT;
    }

    /**
     * Set binding style.
     */
    public function style(BindingStyle $style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Set binding use.
     */
    public function use(BindingUse $use): self
    {
        $this->use = $use;

        return $this;
    }

    /**
     * Set transport URI.
     */
    public function transport(string $uri): self
    {
        $this->transport = $uri;

        return $this;
    }

    /**
     * Add an operation to this binding.
     */
    public function operation(
        string $name,
        string $soapAction,
        ?BindingStyle $style = null,
        ?BindingUse $use = null,
    ): self {
        $this->operations[$name] = new BindingOperation(
            $name,
            $soapAction,
            $style ?? $this->style,
            $use ?? $this->use,
        );

        return $this;
    }

    /**
     * Add a SOAP header to the last added operation.
     */
    public function header(string $message, string $part, ?BindingUse $use = null, ?string $namespace = null, ?string $encodingStyle = null): self
    {
        $operations = array_values($this->operations);
        $lastOperation = end($operations);

        if ($lastOperation === false) {
            throw new RuntimeException('No operation exists to add header to');
        }

        $header = new Header($message, $part);

        if ($use !== null) {
            $header->use($use);
        }

        if ($namespace !== null) {
            $header->namespace($namespace);
        }

        if ($encodingStyle !== null) {
            $header->encodingStyle($encodingStyle);
        }

        $lastOperation->addHeader($header);

        return $this;
    }

    /**
     * Add a header fault to the last added header in the last operation.
     */
    public function headerFault(string $message, string $part, BindingUse $use): self
    {
        $operations = array_values($this->operations);
        $lastOperation = end($operations);

        if ($lastOperation === false) {
            throw new RuntimeException('No operation exists to add header fault to');
        }

        $headers = $lastOperation->getHeaders();
        if (empty($headers)) {
            throw new RuntimeException('No header exists to add fault to');
        }

        $lastHeader = end($headers);
        $lastHeader->headerFault($message, $part)->use($use);

        return $this;
    }

    /**
     * Add MIME multipart to the input of the last added operation.
     */
    public function inputMime(): MimeMultipartRelated
    {
        $operations = array_values($this->operations);
        $lastOperation = end($operations);

        if ($lastOperation === false) {
            throw new RuntimeException('No operation exists to add MIME to');
        }

        $mime = new MimeMultipartRelated($this);
        $lastOperation->setInputMime($mime);

        return $mime;
    }

    /**
     * Add MIME multipart to the output of the last added operation.
     */
    public function outputMime(): MimeMultipartRelated
    {
        $operations = array_values($this->operations);
        $lastOperation = end($operations);

        if ($lastOperation === false) {
            throw new RuntimeException('No operation exists to add MIME to');
        }

        $mime = new MimeMultipartRelated($this);
        $lastOperation->setOutputMime($mime);

        return $mime;
    }

    /**
     * Add MIME multipart to input or output of the last added operation.
     */
    public function mimeMultipart(string $direction): MimeMultipartRelated
    {
        return match ($direction) {
            'input' => $this->inputMime(),
            'output' => $this->outputMime(),
            default => throw new RuntimeException("Invalid direction '{$direction}', expected 'input' or 'output'"),
        };
    }

    /**
     * Set HTTP operation location for the last added operation.
     */
    public function httpOperation(string $location): self
    {
        $operations = array_values($this->operations);
        $lastOperation = end($operations);

        if ($lastOperation === false) {
            throw new RuntimeException('No operation exists to add HTTP operation to');
        }

        $httpOp = new HttpOperation($location);
        $lastOperation->setHttpOperation($httpOp);

        return $this;
    }

    /**
     * Set HTTP URL-encoded input format for the last added operation.
     */
    public function httpUrlEncoded(): self
    {
        $operations = array_values($this->operations);
        $lastOperation = end($operations);

        if ($lastOperation === false) {
            throw new RuntimeException('No operation exists to add HTTP URL-encoded to');
        }

        $lastOperation->setHttpUrlEncoded(HttpUrlEncoded::create());

        return $this;
    }

    /**
     * Set HTTP URL-replacement input format for the last added operation.
     */
    public function httpUrlReplacement(): self
    {
        $operations = array_values($this->operations);
        $lastOperation = end($operations);

        if ($lastOperation === false) {
            throw new RuntimeException('No operation exists to add HTTP URL-replacement to');
        }

        $lastOperation->setHttpUrlReplacement(HttpUrlReplacement::create());

        return $this;
    }

    /**
     * Add documentation to this binding.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Enable WS-Addressing for this binding.
     */
    public function usingAddressing(bool $enabled = true): self
    {
        $this->usingAddressing = $enabled;

        return $this;
    }

    /**
     * Set WS-Addressing action URIs for an operation.
     */
    public function action(string $operationName, string $input, ?string $output = null): self
    {
        $this->actions[$operationName] = new Action($input, $output);

        return $this;
    }

    /**
     * Set a fault action URI for an operation.
     */
    public function faultAction(string $operationName, string $faultName, string $action): self
    {
        if (!isset($this->actions[$operationName])) {
            throw new RuntimeException(
                "No action defined for operation '{$operationName}'. Call action() first.",
            );
        }

        $existingAction = $this->actions[$operationName];
        $faultActions = $existingAction->faultActions ?? [];
        $faultActions[$faultName] = $action;

        $this->actions[$operationName] = new Action(
            $existingAction->inputAction,
            $existingAction->outputAction,
            $faultActions,
        );

        return $this;
    }

    /**
     * Return to the parent WSDL builder.
     */
    public function end(): Wsdl
    {
        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPortType(): string
    {
        return $this->portType;
    }

    public function getStyle(): BindingStyle
    {
        return $this->style;
    }

    public function getUse(): BindingUse
    {
        return $this->use;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    /**
     * @return array<string, BindingOperation>
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }

    public function isUsingAddressing(): bool
    {
        return $this->usingAddressing;
    }

    /**
     * @return array<string, Action>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Set HTTP binding instead of SOAP binding.
     */
    public function httpBinding(string $verb): self
    {
        $this->httpBinding = new HttpBinding($verb);

        return $this;
    }

    /**
     * Get HTTP binding configuration.
     */
    public function getHttpBinding(): ?HttpBinding
    {
        return $this->httpBinding;
    }
}
