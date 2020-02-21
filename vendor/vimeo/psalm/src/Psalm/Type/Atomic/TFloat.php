<?php
namespace Psalm\Type\Atomic;

class TFloat extends Scalar
{
    public function __toString()
    {
        return 'float';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'float';
    }
}
