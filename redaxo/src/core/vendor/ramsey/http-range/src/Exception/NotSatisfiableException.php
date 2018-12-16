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

namespace Ramsey\Http\Range\Exception;

use Exception;

/**
 * Indicates the range given cannot be satisfied
 */
class NotSatisfiableException extends HttpRangeException
{
    /**
     * The range string that couldn't be satisfied
     *
     * @var string
     */
    private $range;

    /**
     * The total size of the entity being requested
     *
     * @var mixed
     */
    private $totalSize;

    /**
     * Constructs a NotSatisfiableException
     *
     * @param string $message
     * @param string $range
     * @param mixed $totalSize
     * @param int $code
     * @param Exception $previous
     */
    public function __construct($message, $range, $totalSize, $code = 0, Exception $previous = null)
    {
        $this->range = $range;
        $this->totalSize = $totalSize;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the range that couldn't be satisfied
     *
     * @return string
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Returns the total size of the entity being requested
     *
     * @return mixed
     */
    public function getTotalSize()
    {
        return $this->totalSize;
    }
}
