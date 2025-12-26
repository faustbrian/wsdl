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
 * Represents a WSDL 2.0 interface (replaces portType in WSDL 1.1).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Interface_
{
    /** @var array<int, string> */
    private array $extends = [];

    /** @var array<string, InterfaceFault> */
    private array $faults = [];

    /** @var array<string, InterfaceOperation> */
    private array $operations = [];

    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Wsdl2 $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add interface inheritance.
     */
    public function extends(string $interfaceName): self
    {
        $this->extends[] = $interfaceName;

        return $this;
    }

    /**
     * Add an interface-level fault.
     */
    public function fault(string $name, string $element): self
    {
        $this->faults[$name] = new InterfaceFault($name, $element);

        return $this;
    }

    /**
     * Add an operation to this interface.
     */
    public function operation(string $name): InterfaceOperation
    {
        $operation = new InterfaceOperation($this, $name);
        $this->operations[$name] = $operation;

        return $operation;
    }

    /**
     * Add documentation to this interface.
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

    /**
     * @return array<int, string>
     */
    public function getExtends(): array
    {
        return $this->extends;
    }

    /**
     * @return array<string, InterfaceFault>
     */
    public function getFaults(): array
    {
        return $this->faults;
    }

    /**
     * @return array<string, InterfaceOperation>
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
