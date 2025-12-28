<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Core;

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;
use Cline\WsdlBuilder\WsExtensions\Addressing\Action;
use RuntimeException;

use function sprintf;

/**
 * Simplified operation builder for high-level API.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Operation
{
    /** @var array<int, array{name: string, type: string}> */
    private array $inputs = [];

    /** @var array<int, array{name: string, type: string}> */
    private array $outputs = [];

    /** @var array<int, array{name: string, type: string}> */
    private array $faults = [];

    private ?string $soapAction = null;

    private ?Action $addressingAction = null;

    public function __construct(
        private readonly Wsdl $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add an input parameter.
     */
    public function input(string $name, XsdType|string $type): self
    {
        $this->inputs[] = [
            'name' => $name,
            'type' => $type instanceof XsdType ? $type->value : $type,
        ];

        return $this;
    }

    /**
     * Add an output parameter.
     */
    public function output(string $name, XsdType|string $type): self
    {
        $this->outputs[] = [
            'name' => $name,
            'type' => $type instanceof XsdType ? $type->value : $type,
        ];

        return $this;
    }

    /**
     * Add a fault.
     */
    public function fault(string $name, XsdType|string $type): self
    {
        $this->faults[] = [
            'name' => $name,
            'type' => $type instanceof XsdType ? $type->value : $type,
        ];

        return $this;
    }

    /**
     * Set custom SOAP action.
     */
    public function soapAction(string $action): self
    {
        $this->soapAction = $action;

        return $this;
    }

    /**
     * Set WS-Addressing action URIs for this operation.
     */
    public function action(string $input, ?string $output = null): self
    {
        $this->addressingAction = new Action($input, $output);

        return $this;
    }

    /**
     * Set a fault action URI for this operation.
     */
    public function faultAction(string $faultName, string $action): self
    {
        if (!$this->addressingAction instanceof Action) {
            throw new RuntimeException(
                sprintf("No action defined for operation '%s'. Call action() first.", $this->name),
            );
        }

        // @codeCoverageIgnoreStart
        $faultActions = $this->addressingAction->faultActions ?? [];
        $faultActions[$faultName] = $action;

        $this->addressingAction = new Action(
            $this->addressingAction->inputAction,
            $this->addressingAction->outputAction,
            $faultActions,
        );

        return $this;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Build and register this operation.
     */
    public function end(): Wsdl
    {
        // Create request type
        $requestType = $this->wsdl->complexType($this->name.'Request');

        foreach ($this->inputs as $input) {
            $requestType->element($input['name'], $input['type']);
        }

        // Create response type
        $responseType = $this->wsdl->complexType($this->name.'Response');

        foreach ($this->outputs as $output) {
            $responseType->element($output['name'], $output['type']);
        }

        // Create messages
        $this->wsdl->message($this->name.'Input')
            ->part('parameters', 'tns:'.$this->name.'Request');

        $this->wsdl->message($this->name.'Output')
            ->part('parameters', 'tns:'.$this->name.'Response');

        // Create fault messages if needed
        $faultMessage = null;

        if ($this->faults !== []) {
            $faultTypeName = $this->name.'Fault';
            $faultType = $this->wsdl->complexType($faultTypeName);

            foreach ($this->faults as $fault) {
                $faultType->element($fault['name'], $fault['type']);
            }

            $this->wsdl->message($faultTypeName)
                ->part('fault', 'tns:'.$faultTypeName);

            $faultMessage = $faultTypeName;
        }

        // Get or create default port type
        $portTypeName = $this->wsdl->getName().'PortType';
        $portTypes = $this->wsdl->getPortTypes();

        if (!isset($portTypes[$portTypeName])) {
            $this->wsdl->portType($portTypeName);
        }

        // Add operation to port type
        $portTypes = $this->wsdl->getPortTypes();
        $portTypes[$portTypeName]->operation(
            $this->name,
            $this->name.'Input',
            $this->name.'Output',
            $faultMessage,
        );

        // Apply WS-Addressing actions to port type if defined
        if ($this->addressingAction instanceof Action) {
            $portTypes[$portTypeName]->action(
                $this->name,
                $this->addressingAction->inputAction,
                $this->addressingAction->outputAction,
            );

            // Apply fault actions if any
            // @codeCoverageIgnoreStart
            foreach ($this->addressingAction->faultActions ?? [] as $faultName => $faultActionUri) {
                $portTypes[$portTypeName]->faultAction($this->name, $faultName, $faultActionUri);
            }
            // @codeCoverageIgnoreEnd
        }

        // Get or create default binding
        $bindingName = $this->wsdl->getName().'Binding';
        $bindings = $this->wsdl->getBindings();

        if (!isset($bindings[$bindingName])) {
            $this->wsdl->binding($bindingName, $portTypeName);
        }

        // Add operation to binding
        $bindings = $this->wsdl->getBindings();
        $action = $this->soapAction ?? $this->wsdl->getTargetNamespace().'/'.$this->name;
        $bindings[$bindingName]->operation($this->name, $action);

        // Apply WS-Addressing actions to binding if defined
        if ($this->addressingAction instanceof Action) {
            $bindings[$bindingName]->action(
                $this->name,
                $this->addressingAction->inputAction,
                $this->addressingAction->outputAction,
            );

            // Apply fault actions if any
            // @codeCoverageIgnoreStart
            foreach ($this->addressingAction->faultActions ?? [] as $faultName => $faultActionUri) {
                $bindings[$bindingName]->faultAction($this->name, $faultName, $faultActionUri);
            }
            // @codeCoverageIgnoreEnd
        }

        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, array{name: string, type: string}>
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /**
     * @return array<int, array{name: string, type: string}>
     */
    public function getOutputs(): array
    {
        return $this->outputs;
    }

    /**
     * @return array<int, array{name: string, type: string}>
     */
    public function getFaults(): array
    {
        return $this->faults;
    }

    public function getSoapAction(): ?string
    {
        return $this->soapAction;
    }
}
