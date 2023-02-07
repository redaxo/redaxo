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

use function explode;

/**
 * `AbstractUnit` provides a basic implementation for HTTP range units.
 */
abstract class AbstractUnit implements UnitInterface
{
    private string $rangeSet;

    /**
     * @var mixed
     */
    private $totalSize;

    /**
     * Returns a new collection for this range unit.
     */
    abstract public function newCollection(): UnitRangesCollection;

    /**
     * Returns a new unit range for this range unit.
     *
     * @param string $range A single range (i.e. `500-999`, `500-`, `-500`).
     * @param mixed $totalSize The total size of the entity the range describes.
     */
    abstract public function newRange(string $range, $totalSize): UnitRangeInterface;

    /**
     * Constructs a new unit.
     *
     * @param string $rangeSet A set of ranges for this unit (i.e. `500-999,500-,-500`).
     * @param mixed $totalSize The total size of the entity the unit describes.
     */
    public function __construct(string $rangeSet, $totalSize)
    {
        $this->rangeSet = $rangeSet;
        $this->totalSize = $totalSize;
    }

    /**
     * Returns the raw range set defined for this unit.
     *
     * ```
     * other-range-set = 1*VCHAR
     * ```
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     */
    public function getRangeSet(): string
    {
        return $this->rangeSet;
    }

    /**
     * Returns the raw ranges specifier defined for this unit.
     *
     * ```
     * other-ranges-specifier = other-range-unit "=" other-range-set
     * ```
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     */
    public function getRangesSpecifier(): string
    {
        return $this->getRangeUnit() . '=' . $this->getRangeSet();
    }

    /**
     * Returns an iterable collection of unit ranges.
     */
    public function getRanges(): UnitRangesCollection
    {
        $ranges = explode(',', $this->getRangeSet());
        $collection = $this->newCollection();

        /** @var mixed $totalSize */
        $totalSize = $this->getTotalSize();

        foreach ($ranges as $range) {
            $collection[] = $this->newRange($range, $totalSize);
        }

        return $collection;
    }

    /**
     * Returns the total size of the entity this unit describes.
     *
     * For example, if this unit describes the bytes in a file, then this
     * returns the total bytes of the file.
     *
     * @return mixed
     */
    public function getTotalSize()
    {
        return $this->totalSize;
    }
}
