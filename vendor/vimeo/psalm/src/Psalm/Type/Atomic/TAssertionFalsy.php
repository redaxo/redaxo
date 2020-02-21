<?php
namespace Psalm\Type\Atomic;

class TAssertionFalsy extends \Psalm\Type\Atomic
{
    public function __toString()
    {
        return 'falsy';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'falsy';
    }

    /**
     * @return string
     */
    public function getAssertionString()
    {
        return 'falsy';
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string> $aliased_classes
     * @param  string|null   $this_class
     * @param  int           $php_major_version
     * @param  int           $php_minor_version
     *
     * @return null|string
     */
    public function toPhpString(
        $namespace,
        array $aliased_classes,
        $this_class,
        $php_major_version,
        $php_minor_version
    ) {
        return null;
    }

    public function canBeFullyExpressedInPhp()
    {
        return false;
    }
}
