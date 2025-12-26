<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Compositors;

/**
 * Represents an XSD any wildcard element.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Any
{
    private string $namespace = '##any';

    private string $processContents = 'strict';

    private ?int $minOccurs = null;

    private ?int $maxOccurs = null;

    public function __construct(
        private readonly object $parent,
    ) {}

    /**
     * Set the namespace constraint.
     * Valid values: ##any, ##other, ##local, ##targetNamespace, or a URI.
     */
    public function namespace(string $ns): self
    {
        $this->namespace = $ns;

        return $this;
    }

    /**
     * Set the processContents mode.
     * Valid values: strict, lax, skip.
     */
    public function processContents(string $mode): self
    {
        if (!\in_array($mode, ['strict', 'lax', 'skip'], true)) {
            throw new \InvalidArgumentException('processContents must be one of: strict, lax, skip');
        }

        $this->processContents = $mode;

        return $this;
    }

    /**
     * Set minimum occurrences.
     */
    public function minOccurs(int $min): self
    {
        $this->minOccurs = $min;

        return $this;
    }

    /**
     * Set maximum occurrences.
     */
    public function maxOccurs(int $max): self
    {
        $this->maxOccurs = $max;

        return $this;
    }

    /**
     * Return to the parent builder.
     */
    public function end(): object
    {
        return $this->parent;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getProcessContents(): string
    {
        return $this->processContents;
    }

    public function getMinOccurs(): ?int
    {
        return $this->minOccurs;
    }

    public function getMaxOccurs(): ?int
    {
        return $this->maxOccurs;
    }
}
