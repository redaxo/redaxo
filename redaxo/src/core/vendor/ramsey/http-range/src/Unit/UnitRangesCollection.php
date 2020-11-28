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

use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\CollectionInterface;

/**
 * A collection of `UnitRangeInterface` objects.
 */
class UnitRangesCollection extends AbstractCollection implements CollectionInterface
{
    /**
     * Returns the data type of the items allowed in this collection
     *
     * @return string
     */
    public function getType(): string
    {
        return UnitRangeInterface::class;
    }
}
