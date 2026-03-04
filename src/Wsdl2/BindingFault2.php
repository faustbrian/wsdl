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
 * Represents a fault within a WSDL 2.0 binding.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class BindingFault2
{
    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Binding2 $binding,
        private readonly string $ref,
    ) {}

    /**
     * Add documentation to this binding fault.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Return to the parent binding.
     */
    public function end(): Binding2
    {
        return $this->binding;
    }

    public function getRef(): string
    {
        return $this->ref;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
