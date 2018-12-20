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

use Ramsey\Http\Range\Exception\NotSatisfiableException;
use Ramsey\Http\Range\Exception\ParseException;

/**
 * An abstract unit range to handle common unit range functionality
 */
abstract class AbstractUnitRange implements UnitRangeInterface
{
    /**
     * @var string
     */
    private $range;

    /**
     * @var mixed
     */
    private $totalSize;

    /**
     * @var mixed
     */
    private $start;

    /**
     * @var mixed
     */
    private $end;

    /**
     * Constructs a new unit range
     *
     * @param string $range A single range (i.e. 500-999, 500-, -500)
     * @param mixed $totalSize The total size of the entity the range describes
     * @throws ParseException If unable to parse the range
     * @throws NotSatisfiableException If the range cannot be satisfied
     */
    public function __construct($range, $totalSize)
    {
        $this->range = $range;
        $this->totalSize = $totalSize;

        list($this->start, $this->end) = $this->parseRange($range, $totalSize);
    }

    /**
     * Returns the raw range
     *
     * @return string
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Returns the start of the range
     *
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end of the range
     *
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns the length of this range
     *
     * For example, if the total size is 1200, and the start is 700 and the end
     * is 1199, then the length is 500.
     *
     * @return mixed
     */
    public function getLength()
    {
        return $this->getEnd() - $this->getStart() + 1;
    }

    /**
     * Returns the total size of the entity this unit range describes
     *
     * For example, if this unit range describes the bytes in a file, then this
     * returns the total bytes of the file.
     *
     * @return mixed
     */
    public function getTotalSize()
    {
        return $this->totalSize;
    }

    /**
     * Parses the given range, returning a 2-tuple where the first value is the
     * start and the second is the end
     *
     * @param string $range The range string to parse
     * @param mixed $totalSize The total size of the entity
     * @return array
     */
    private function parseRange($range, $totalSize)
    {
        $points = explode('-', $range, 2);

        if (!isset($points[1])) {
            // Assume the request is for a single item.
            $points[1] = $points[0];
        }

        $isValidRangeValue = function ($value) {
            return (ctype_digit($value) || $value === '');
        };

        if (empty(array_filter($points, 'ctype_digit'))
            || array_filter($points, $isValidRangeValue) !== $points
        ) {
            throw new ParseException(
                "Unable to parse range: {$range}"
            );
        }

        $start = $points[0];
        $end = ($points[1] !== '') ? $points[1] : ($totalSize - 1);

        if ($end >= $totalSize) {
            $end = $totalSize - 1;
        }

        if ($start === '') {
            // Use the "suffix-byte-range-spec".
            $start = $totalSize - $end;
            $end = $totalSize - 1;
        }

        if ($start == $totalSize) {
            throw new NotSatisfiableException(
                "Unable to satisfy range: {$range}; length is zero",
                $range,
                $totalSize
            );
        }

        if ($start > $totalSize) {
            throw new NotSatisfiableException(
                "Unable to satisfy range: {$range}; start ({$start}) is greater than size ({$totalSize})",
                $range,
                $totalSize
            );
        }

        if ($end < $start) {
            throw new ParseException(
                "The end value cannot be less than the start value: {$range}"
            );
        }

        return [$start, $end];
    }
}
