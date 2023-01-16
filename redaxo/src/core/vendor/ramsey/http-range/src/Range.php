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

namespace Ramsey\Http\Range;

use Psr\Http\Message\RequestInterface;
use Ramsey\Http\Range\Exception\NoRangeException;
use Ramsey\Http\Range\Unit\UnitInterface;

use function count;
use function trim;

/**
 * `Range` represents an HTTP Range request header.
 *
 * For more information about range requests, see
 * [RFC 7233: HTTP Range Requests](https://tools.ietf.org/html/rfc7233).
 */
class Range
{
    private RequestInterface $request;

    /**
     * @var mixed
     */
    private $totalSize;

    private UnitFactoryInterface $unitFactory;

    /**
     * Constructs an HTTP Range request header.
     *
     * @param RequestInterface $request A PSR-7-compatible HTTP request.
     * @param mixed $totalSize The total size of the entity for which a range is
     *     requested (this may be in bytes, items, etc.).
     * @param UnitFactoryInterface|null $unitFactory An optional factory to use for
     *     parsing range units.
     */
    public function __construct(
        RequestInterface $request,
        $totalSize,
        ?UnitFactoryInterface $unitFactory = null
    ) {
        $this->request = $request;
        $this->totalSize = $totalSize;

        if ($unitFactory === null) {
            $unitFactory = new UnitFactory();
        }

        $this->unitFactory = $unitFactory;
    }

    /**
     * Returns the PSR-7 HTTP request object.
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Returns the total size of the entity for which the range is requested.
     *
     * @return mixed
     */
    public function getTotalSize()
    {
        return $this->totalSize;
    }

    /**
     * Returns the unit factory used by this range.
     */
    public function getUnitFactory(): UnitFactoryInterface
    {
        return $this->unitFactory;
    }

    /**
     * Returns the unit parsed for this range request.
     *
     * @throws NoRangeException if a range request header could not be found.
     */
    public function getUnit(): UnitInterface
    {
        $rangeHeader = $this->getRequest()->getHeader('Range');

        if (count($rangeHeader) === 0) {
            throw new NoRangeException();
        }

        // Use only the first Range header found, for now.
        return $this->getUnitFactory()->getUnit(trim($rangeHeader[0]), $this->getTotalSize());
    }
}
