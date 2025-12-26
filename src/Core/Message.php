<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Core;

use Cline\WsdlBuilder\Documentation\Documentation;
use Cline\WsdlBuilder\Enums\XsdType;
use Cline\WsdlBuilder\Wsdl;

/**
 * Represents a WSDL message.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Message
{
    /** @var array<int, MessagePart> */
    private array $parts = [];

    private ?Documentation $documentation = null;

    public function __construct(
        private readonly Wsdl $wsdl,
        private readonly string $name,
    ) {}

    /**
     * Add a part to this message.
     */
    public function part(string $name, XsdType|string $type): self
    {
        $this->parts[] = new MessagePart(
            $name,
            $type instanceof XsdType ? $type->value : $type,
        );

        return $this;
    }

    /**
     * Add documentation to this message.
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentation = new Documentation($content, $lang, $source);

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
     * @return array<int, MessagePart>
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    public function getDocumentation(): ?Documentation
    {
        return $this->documentation;
    }
}
