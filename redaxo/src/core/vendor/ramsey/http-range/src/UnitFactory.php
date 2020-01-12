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

namespace Ramsey\Http\Range;

use Ramsey\Http\Range\Exception\InvalidRangeSetException;
use Ramsey\Http\Range\Exception\InvalidRangeUnitException;
use Ramsey\Http\Range\Unit\BytesUnit;
use Ramsey\Http\Range\Unit\GenericUnit;
use Ramsey\Http\Range\Unit\UnitInterface;

/**
 * A default factory for creating bytes range units
 */
class UnitFactory implements UnitFactoryInterface
{
    /**
     * Returns a parsed unit for the HTTP Range header
     *
     * @param string $rangesSpecifier The original value of the HTTP Range header
     * @param mixed $totalSize The total size of the entity described by this unit
     *
     * @throws InvalidRangeUnitException
     * @throws InvalidRangeSetException
     *
     * @return UnitInterface
     */
    public function getUnit($rangesSpecifier, $totalSize)
    {
        $unitSet = explode('=', $rangesSpecifier);

        if (empty($unitSet[0])) {
            throw new InvalidRangeUnitException(
                'No range-unit provided in $rangesSpecifier'
            );
        }

        if (empty($unitSet[1])) {
            throw new InvalidRangeSetException(
                'No range-set provided in $rangesSpecifier'
            );
        }

        switch (strtolower($unitSet[0])) {
            case 'bytes':
                return new BytesUnit($unitSet[1], $totalSize);
            default:
                return new GenericUnit($unitSet[0], $unitSet[1], $totalSize);
        }
    }
}
