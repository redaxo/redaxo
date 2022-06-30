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
 * A collection of `BytesRange` objects.
 */
class BytesRangesCollection extends UnitRangesCollection
{
    /**
     * Returns the data type of the items allowed in this collection.
     *
     * @return string
     */
    public function getType(): string
    {
        return BytesRange::class;
    }
}
