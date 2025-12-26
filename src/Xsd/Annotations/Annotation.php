<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Xsd\Annotations;

use Cline\WsdlBuilder\Documentation\Documentation;

/**
 * Represents an XSD annotation container for documentation and appinfo elements.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class Annotation
{
    /** @var array<int, Documentation> */
    private array $documentations = [];

    /** @var array<int, AppInfo> */
    private array $appInfos = [];

    public function __construct(
        private readonly object $parent,
    ) {}

    /**
     * Add documentation element (human-readable).
     */
    public function documentation(string $content, ?string $lang = null, ?string $source = null): self
    {
        $this->documentations[] = new Documentation($content, $lang, $source);

        return $this;
    }

    /**
     * Add appinfo element (machine-readable).
     */
    public function appInfo(string $content, ?string $source = null): self
    {
        $this->appInfos[] = new AppInfo($content, $source);

        return $this;
    }

    /**
     * Return to the parent builder.
     */
    public function end(): object
    {
        return $this->parent;
    }

    /**
     * @return array<int, Documentation>
     */
    public function getDocumentations(): array
    {
        return $this->documentations;
    }

    /**
     * @return array<int, AppInfo>
     */
    public function getAppInfos(): array
    {
        return $this->appInfos;
    }
}
