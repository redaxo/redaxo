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

use Ramsey\Http\Range\Exception\NotSatisfiableException;
use Ramsey\Http\Range\Exception\ParseException;

use function array_filter;
use function ctype_digit;
use function explode;

/**
 * `AbstractUnitRange` provides a basic implementation for unit ranges.
 */
abstract class AbstractUnitRange implements UnitRangeInterface
{
    private string $range;

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
     * Constructs a new unit range.
     *
     * @param string $range A single range (i.e. `500-999`, `500-`, `-500`).
     * @param mixed $totalSize The total size of the entity the range describes.
     *
     * @throws ParseException if unable to parse the range.
     * @throws NotSatisfiableException if the range cannot be satisfied.
     */
    public function __construct(string $range, $totalSize)
    {
        $this->range = $range;
        $this->totalSize = $totalSize;

        [$this->start, $this->end] = $this->parseRange($range, $totalSize);
    }

    /**
     * Returns the raw range.
     */
    public function getRange(): string
    {
        return $this->range;
    }

    /**
     * Returns the start of the range.
     *
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end of the range.
     *
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns the length of this range.
     *
     * For example, if the total size is 1200, and the start is 700 and the end
     * is 1199, then the length is 500.
     *
     * @return mixed
     */
    public function getLength()
    {
        return (int) $this->getEnd() - (int) $this->getStart() + 1;
    }

    /**
     * Returns the total size of the entity this unit range describes.
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
     * start and the second is the end.
     *
     * @param string $range The range string to parse.
     * @param mixed $totalSize The total size of the entity.
     *
     * @return int[]
     *
     * @throws ParseException if unable to parse the range.
     * @throws NotSatisfiableException if the range cannot be satisfied.
     */
    private function parseRange(string $range, $totalSize): array
    {
        $points = explode('-', $range, 2);

        if (!isset($points[1])) {
            // Assume the request is for a single item.
            $points[1] = $points[0];
        }

        $isValidRangeValue = fn (string $value): bool => ctype_digit($value) || $value === '';

        if (
            !array_filter($points, 'ctype_digit')
            || array_filter($points, $isValidRangeValue) !== $points
        ) {
            throw new ParseException(
                "Unable to parse range: {$range}",
            );
        }

        $totalSize = (int) $totalSize;
        $start = $points[0];
        $end = $points[1] !== '' ? (int) $points[1] : $totalSize - 1;

        if ($end >= $totalSize) {
            $end = $totalSize - 1;
        }

        if ($start === '') {
            // Use the "suffix-byte-range-spec".
            $start = $totalSize - $end;
            $end = $totalSize - 1;
        }

        $start = (int) $start;

        if ($start === $totalSize) {
            throw new NotSatisfiableException(
                "Unable to satisfy range: {$range}; length is zero",
                $range,
                $totalSize,
            );
        }

        if ($start > $totalSize) {
            throw new NotSatisfiableException(
                "Unable to satisfy range: {$range}; start ({$start}) is greater than size ({$totalSize})",
                $range,
                $totalSize,
            );
        }

        if ($end < $start) {
            throw new ParseException(
                "The end value cannot be less than the start value: {$range}",
            );
        }

        return [$start, $end];
    }
}
