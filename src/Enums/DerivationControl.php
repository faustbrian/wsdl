<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\WsdlBuilder\Enums;

/**
 * XSD derivation control values.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum DerivationControl: string
{
    case All = '#all';
    case Extension = 'extension';
    case Restriction = 'restriction';
    case Substitution = 'substitution';
    case List = 'list';
    case Union = 'union';
}
