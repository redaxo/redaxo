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

use Psr\Http\Message\RequestInterface;
use Ramsey\Http\Range\Exception\NoRangeException;
use Ramsey\Http\Range\Unit\UnitInterface;

/**
 * Represents an HTTP Range request header
 *
 * @link https://tools.ietf.org/html/rfc7233 RFC 7233: HTTP Range Requests
 */
class Range
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var mixed
     */
    private $totalSize;

    /**
     * @var UnitFactoryInterface
     */
    private $unitFactory;

    /**
     * @param RequestInterface $request
     * @param mixed $totalSize The total size of the entity for which a range is requested
     * @param UnitFactoryInterface $unitFactory
     */
    public function __construct(
        RequestInterface $request,
        $totalSize,
        UnitFactoryInterface $unitFactory = null
    ) {
        $this->request = $request;
        $this->totalSize = $totalSize;

        if ($unitFactory === null) {
            $unitFactory = new UnitFactory();
        }

        $this->unitFactory = $unitFactory;
    }

    /**
     * Returns the HTTP request object
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the total size of the entity for which the range is requested
     *
     * @return mixed
     */
    public function getTotalSize()
    {
        return $this->totalSize;
    }

    /**
     * Returns the unit factory used by this range
     *
     * @return UnitFactoryInterface
     */
    public function getUnitFactory()
    {
        return $this->unitFactory;
    }

    /**
     * Returns the unit parsed for this range request
     *
     * @throws NoRangeException if a range request is not present in the current request
     *
     * @return UnitInterface
     */
    public function getUnit()
    {
        $rangeHeader = $this->getRequest()->getHeader('Range');

        if (empty($rangeHeader)) {
            throw new NoRangeException();
        }

        // Use only the first Range header found, for now.
        return $this->getUnitFactory()->getUnit(trim($rangeHeader[0]), $this->getTotalSize());
    }
}
