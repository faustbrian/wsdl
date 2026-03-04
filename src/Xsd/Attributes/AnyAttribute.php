<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Attributes;

/**
 * Represents an XSD wildcard attribute (anyAttribute).
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class AnyAttribute
{
    private string $namespace = '##any';

    private string $processContents = 'strict';

    /**
     * Set the namespace constraint.
     * Accepted values: ##any, ##other, ##local, ##targetNamespace, or a URI list.
     */
    public function namespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Set the process contents mode (strict, lax, skip).
     */
    public function processContents(string $mode): self
    {
        $this->processContents = $mode;

        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getProcessContents(): string
    {
        return $this->processContents;
    }
}
