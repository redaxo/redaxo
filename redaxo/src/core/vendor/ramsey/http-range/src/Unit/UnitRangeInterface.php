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
 * A single range for a unit
 */
interface UnitRangeInterface
{
    /**
     * Returns the raw range
     *
     * @return string
     */
    public function getRange();

    /**
     * Returns the start of the range
     *
     * @return mixed
     */
    public function getStart();

    /**
     * Returns the end of the range
     *
     * @return mixed
     */
    public function getEnd();

    /**
     * Returns the length of this range
     *
     * For example, if the total size is 1200, and the start is 700 and the end
     * is 1199, then the length is 500.
     *
     * @return mixed
     */
    public function getLength();

    /**
     * Returns the total size of the entity this unit range describes
     *
     * For example, if this unit range describes the bytes in a file, then this
     * returns the total bytes of the file.
     *
     * @return mixed
     */
    public function getTotalSize();
}
