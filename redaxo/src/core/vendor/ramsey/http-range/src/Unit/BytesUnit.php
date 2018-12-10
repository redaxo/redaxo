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
 * An HTTP Range bytes unit as defined in RFC 7233
 *
 * @link https://tools.ietf.org/html/rfc7233#section-2.1 RFC 7233 § 2.1
 */
class BytesUnit extends AbstractUnit implements UnitInterface
{
    /**
     * Returns the "bytes" unit token for this unit
     *
     * @return string
     */
    public function getRangeUnit()
    {
        return 'bytes';
    }

    /**
     * Returns a new collection for this range unit
     *
     * @return UnitRangesCollection
     */
    public function newCollection()
    {
        return new BytesRangesCollection();
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
        return new BytesRange($range, $totalSize);
    }
}
