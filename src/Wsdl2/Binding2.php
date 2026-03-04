<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Wsdl2;

use Cline\WsdlBuilder\Documentation\Documentation;

/**
 * Represents a WSDL 2.0 binding.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Binding2
{
    private ?string $type = null;

    /** @var array<string, BindingOperation2> */
    private array $operations = [];

    /** @var array<string, BindingFault2> */
    private array $faults = [];

    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Wsdl2 $wsdl,
        private readonly string $name,
        private readonly string $interfaceRef,
    ) {}

    /**
     * Set the binding type URI (e.g., http://www.w3.org/ns/wsdl/soap).
     */
    public function type(string $bindingType): self
    {
        $this->type = $bindingType;

        return $this;
    }

    /**
     * Add an operation to this binding.
     */
    public function operation(string $ref): BindingOperation2
    {
        $operation = new BindingOperation2($this, $ref);
        $this->operations[$ref] = $operation;

        return $operation;
    }

    /**
     * Add a fault to this binding.
     */
    public function fault(string $ref): BindingFault2
    {
        $fault = new BindingFault2($this, $ref);
        $this->faults[$ref] = $fault;

        return $fault;
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
     * Return to the parent WSDL builder.
     */
    public function end(): Wsdl2
    {
        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInterfaceRef(): string
    {
        return $this->interfaceRef;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return array<string, BindingOperation2>
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @return array<string, BindingFault2>
     */
    public function getFaults(): array
    {
        return $this->faults;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
