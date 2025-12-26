<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Groups;

use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Contracts\WsdlBuilderInterface;
use Cline\WsdlBuilder\Xsd\Compositors\Choice;
use Cline\WsdlBuilder\Xsd\Compositors\All;

/**
 * Represents an XSD element group that can be reused across multiple complex types.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class ElementGroup
{
    /** @var array<int, array{name: string, type: string, nullable: bool, minOccurs: ?int, maxOccurs: ?int}> */
    private array $elements = [];

    private ?Choice $choice = null;

    private ?All $all = null;

    public function __construct(
        private readonly WsdlBuilderInterface $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add an element to this group.
     */
    public function element(
        string $name,
        XsdType|string $type,
        bool $nullable = false,
        ?int $minOccurs = null,
        ?int $maxOccurs = null,
    ): self {
        $this->elements[] = [
            'name' => $name,
            'type' => $type instanceof XsdType ? $type->value : $type,
            'nullable' => $nullable,
            'minOccurs' => $minOccurs,
            'maxOccurs' => $maxOccurs,
        ];

        return $this;
    }

    /**
     * Start a choice compositor.
     */
    public function choice(): Choice
    {
        $this->choice = new Choice($this);

        return $this->choice;
    }

    /**
     * Start an all compositor.
     */
    public function all(): All
    {
        $this->all = new All($this);

        return $this->all;
    }

    /**
     * Return to the parent WSDL builder.
     */
    public function end(): WsdlBuilderInterface
    {
        return $this->wsdl;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, array{name: string, type: string, nullable: bool, minOccurs: ?int, maxOccurs: ?int}>
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function getChoice(): ?Choice
    {
        return $this->choice;
    }

    public function getAll(): ?All
    {
        return $this->all;
    }
}
