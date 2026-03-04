<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Enums;

/**
 * SOAP binding styles.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum BindingStyle: string
{
    case Document = 'document';
    case Rpc = 'rpc';
}
