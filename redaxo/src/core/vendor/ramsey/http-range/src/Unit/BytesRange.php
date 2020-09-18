<?php
/**
 * This file is part of the ramsey/http-range library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Http\Range\Unit;

/**
 * An HTTP Range bytes range spec as defined in RFC 7233.
 *
 * See [RFC 7233 § 2.1](https://tools.ietf.org/html/rfc7233#section-2.1) for the
 * byte-ranges-specifier specification.
 */
class BytesRange extends AbstractUnitRange implements UnitRangeInterface
{
}
