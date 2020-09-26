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
 * `UnitInterface` defines an interface for HTTP Range units as defined in RFC 7233.
 *
 * See [RFC 7233 § 2](https://tools.ietf.org/html/rfc7233#section-2) for the
 * range-unit specification.
 */
interface UnitInterface
{
    /**
     * Returns the raw range set defined for this unit.
     *
     * ```
     * other-range-set = 1*VCHAR
     * ```
     *
     * @return string
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     */
    public function getRangeSet(): string;

    /**
     * Returns the unit token defined for this unit.
     *
     * ```
     * other-range-unit = token
     * ```
     *
     * @return string
     *
     * @link https://tools.ietf.org/html/rfc7233#section-2.2 RFC 7233 § 2.2
     */
    public function getRangeUnit(): string;

    /**
     * Returns the raw ranges specifier defined for this unit.
     *
     * ```
     * other-ranges-specifier = other-range-unit "=" other-range-set
     * ```
     *
     * @return string
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     */
    public function getRangesSpecifier(): string;

    /**
     * Returns an iterable collection of unit ranges.
     *
     * @return UnitRangesCollection
     */
    public function getRanges(): UnitRangesCollection;

    /**
     * Returns the total size of the entity this unit describes.
     *
     * For example, if this unit describes the bytes in a file, then this
     * returns the total bytes of the file.
     *
     * @return mixed
     */
    public function getTotalSize();
}
