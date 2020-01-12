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
 * An abstract unit to handle common unit functionality
 */
abstract class AbstractUnit implements UnitInterface
{
    /**
     * @var string
     */
    private $rangeSet;

    /**
     * @var mixed
     */
    private $totalSize;

    /**
     * Returns a new collection for this range unit
     *
     * @return UnitRangesCollection
     */
    abstract public function newCollection();

    /**
     * Returns a new unit range for this range unit
     *
     * @param string $range A single range (i.e. 500-999, 500-, -500)
     * @param mixed $totalSize The total size of the entity the range describes
     * @return UnitRangeInterface
     */
    abstract public function newRange($range, $totalSize);

    /**
     * Constructs a new unit
     *
     * @param string $rangeSet A set of ranges for this unit (i.e. 500-999,500-,-500)
     * @param mixed $totalSize The total size of the entity the unit describes
     */
    public function __construct($rangeSet, $totalSize)
    {
        $this->rangeSet = $rangeSet;
        $this->totalSize = $totalSize;
    }

    /**
     * Returns the raw range set defined for this unit
     *
     *     other-range-set = 1*VCHAR
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     * @return string
     */
    public function getRangeSet()
    {
        return $this->rangeSet;
    }

    /**
     * Returns the raw ranges specifier defined for this unit
     *
     *     other-ranges-specifier = other-range-unit "=" other-range-set
     *
     * @link https://tools.ietf.org/html/rfc7233#section-3.1 RFC 7233 § 3.1
     * @return string
     */
    public function getRangesSpecifier()
    {
        return $this->getRangeUnit() . '=' . $this->getRangeSet();
    }

    /**
     * Returns an iterable collection of unit ranges
     *
     * @return UnitRangesCollection
     */
    public function getRanges()
    {
        $ranges = explode(',', $this->getRangeSet());
        $totalSize = $this->getTotalSize();
        $collection = $this->newCollection();

        foreach ($ranges as $range) {
            $collection[] = $this->newRange($range, $totalSize);
        }

        return $collection;
    }

    /**
     * Returns the total size of the entity this unit describes
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
