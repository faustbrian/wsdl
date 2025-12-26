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
 * One-way operation builder (input only, no response).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class OneWay
{
    /** @var array<int, array{name: string, type: string}> */
    private array $inputs = [];

    private ?string $soapAction = null;

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
     * Set custom SOAP action.
     */
    public function soapAction(string $action): self
    {
        $this->soapAction = $action;

        return $this;
    }

    /**
     * Build and register this one-way operation.
     */
    public function end(): Wsdl
    {
        // Create request type
        $requestType = $this->wsdl->complexType($this->name.'Request');

        foreach ($this->inputs as $input) {
            $requestType->element($input['name'], $input['type']);
        }

        // Create input message
        $this->wsdl->message($this->name.'Input')
            ->part('parameters', 'tns:'.$this->name.'Request');

        // Get or create default port type
        $portTypeName = $this->wsdl->getName().'PortType';
        $portTypes = $this->wsdl->getPortTypes();

        if (!isset($portTypes[$portTypeName])) {
            $this->wsdl->portType($portTypeName);
        }

        // Add one-way operation to port type (no output)
        $portTypes = $this->wsdl->getPortTypes();
        $portTypes[$portTypeName]->operation(
            $this->name,
            $this->name.'Input',
            '', // No output for one-way
            null,
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
    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function getSoapAction(): ?string
    {
        return $this->soapAction;
    }
}
