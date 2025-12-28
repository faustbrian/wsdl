<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Core;

use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Addressing\Action;
use RuntimeException;

/**
 * Represents a WSDL port type (interface definition).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PortType
{
    /** @var array<string, PortTypeOperation> */
    private array $operations = [];

    private ?Documentation $documentation = null;

    private bool $usingAddressing = false;

    /** @var array<string, Action> */
    private array $actions = [];

    public function __construct(
        private readonly Wsdl $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add an operation to this port type.
     */
    public function operation(string $name, ?string $input, ?string $output, ?string $fault = null): self
    {
        $this->operations[$name] = new PortTypeOperation($name, $input, $output, $fault);

        return $this;
    }

    /**
     * Add documentation to this port type.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Enable WS-Addressing for this port type.
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

    /**
     * @return array<string, PortTypeOperation>
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
}
