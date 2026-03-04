<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Contracts;

/**
 * Interface for WSDL builders (both 1.1 and 2.0).
 *
 * This interface serves as a common contract that XSD type classes
 * can depend on, allowing them to be used with either WSDL version
 * without tight coupling to a specific implementation.
 *
 * @author Brian Faust <brian@cline.sh>
 */
interface WsdlBuilderInterface
{
    /**
     * Get the service/description name.
     */
    public function getName(): string;

    /**
     * Get the target namespace.
     */
    public function getTargetNamespace(): string;

    /**
     * Build the WSDL XML string.
     */
    public function build(): string;
}
