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
 * A generic HTTP Range unit
 */
class GenericUnit extends AbstractUnit implements UnitInterface
{
    /**
     * @var string
     */
    private $rangeUnit;

    /**
     * Constructs a new generic unit
     *
     * @param string $rangeSet A set of ranges for this unit (i.e. 500-999,500-,-500)
     * @param mixed $totalSize The total size of the entity the unit describes
     */
    public function __construct($rangeUnit, $rangeSet, $totalSize)
    {
        $this->rangeUnit = $rangeUnit;
        parent::__construct($rangeSet, $totalSize);
    }


    /**
     * Returns the "bytes" unit token for this unit
     *
     * @return string
     */
    public function getRangeUnit()
    {
        return $this->rangeUnit;
    }

    /**
     * Returns a new collection for this range unit
     *
     * @return UnitRangesCollection
     */
    public function newCollection()
    {
        return new UnitRangesCollection();
    }

    /**
     * Returns a new unit range for this range unit
     *
     * @param string $range A single range (i.e. 500-999, 500-, -500)
     * @param mixed $totalSize The total size of the entity the range describes
     * @return UnitRangeInterface
     */
    public function newRange($range, $totalSize)
    {
        return new GenericRange($range, $totalSize);
    }
}
