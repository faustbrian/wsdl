<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Operations;

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

/**
 * Notification operation builder (output only, no input).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Notification
{
    /** @var array<int, array{name: string, type: string}> */
    private array $outputs = [];

    private ?string $soapAction = null;

    public function __construct(
        private readonly Wsdl $wsdl,
        private readonly string $name,
    ) {}

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
     * Set custom SOAP action.
     */
    public function soapAction(string $action): self
    {
        $this->soapAction = $action;

        return $this;
    }

    /**
     * Build and register this notification operation.
     */
    public function end(): Wsdl
    {
        // Create response type
        $responseType = $this->wsdl->complexType($this->name.'Response');

        foreach ($this->outputs as $output) {
            $responseType->element($output['name'], $output['type']);
        }

        // Create output message
        $this->wsdl->message($this->name.'Output')
            ->part('parameters', 'tns:'.$this->name.'Response');

        // Get or create default port type
        $portTypeName = $this->wsdl->getName().'PortType';
        $portTypes = $this->wsdl->getPortTypes();

        if (!isset($portTypes[$portTypeName])) {
            $this->wsdl->portType($portTypeName);
        }

        // Add notification operation to port type (no input)
        $portTypes = $this->wsdl->getPortTypes();
        $portTypes[$portTypeName]->operation(
            $this->name,
            '', // No input for notification
            $this->name.'Output',
        );

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

        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, array{name: string, type: string}>
     */
    public function getOutputs(): array
    {
        return $this->outputs;
    }

    public function getSoapAction(): ?string
    {
        return $this->soapAction;
    }
}
