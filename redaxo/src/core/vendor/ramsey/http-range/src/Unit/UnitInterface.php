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

namespace Ramsey\Http\Range\Unit;

/**
 * An HTTP Range unit as defined in RFC 7233
 *
 * @link https://tools.ietf.org/html/rfc7233#section-2 RFC 7233 § 2
 */
interface UnitInterface
{
    /**
     * Returns the raw range set defined for this unit
     *
     *     other-range-set = 1*VCHAR
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     * @return string
     */
    public function getRangeSet();

    /**
     * Returns the unit token defined for this unit
     *
     *     other-range-unit = token
     *
     * @link https://tools.ietf.org/html/rfc7233#section-2.2 RFC 7233 § 2.2
     * @return string
     */
    public function getRangeUnit();

    /**
     * Returns the raw ranges specifier defined for this unit
     *
     *     other-ranges-specifier = other-range-unit "=" other-range-set
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     * @return string
     */
    public function getRangesSpecifier();

    /**
     * Returns an iterable collection of unit ranges
     *
     * @return UnitRangesCollection
     */
    public function getRanges();

    /**
     * Returns the total size of the entity this unit describes
     *
     * For example, if this unit describes the bytes in a file, then this
     * returns the total bytes of the file.
     *
     * @return mixed
     */
    public function getTotalSize();
}
