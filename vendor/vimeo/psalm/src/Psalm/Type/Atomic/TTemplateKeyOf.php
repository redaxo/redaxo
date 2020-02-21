<?php
namespace Psalm\Type\Atomic;

use Psalm\Type\Union;

class TTemplateKeyOf extends TArrayKey
{
    /**
     * @var string
     */
    public $param_name;

    /**
     * @var string
     */
    public $defining_class;

    /**
     * @var Union
     */
    public $as;

    public function __construct(
        string $param_name,
        string $defining_class,
        Union $as
    ) {
        $this->param_name = $param_name;
        $this->defining_class = $defining_class;
        $this->as = $as;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'key-of<' . $this->param_name . '>';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'key-of<' . $this->param_name . '>';
    }

    /**
     * @return string
     */
    public function getId(bool $nested = false)
    {
        return 'key-of<' . $this->param_name . ':' . $this->defining_class . ' as ' . $this->as->getId() . '>';
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string> $aliased_classes
     * @param  string|null   $this_class
     * @param  int           $php_major_version
     * @param  int           $php_minor_version
     *
     * @return string|null
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

    /**
     * @return false
     */
    public function canBeFullyExpressedInPhp()
    {
        return false;
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string> $aliased_classes
     * @param  string|null   $this_class
     * @param  bool          $use_phpdoc_format
     *
     * @return string
     */
    public function toNamespacedString(
        ?string $namespace,
        array $aliased_classes,
        ?string $this_class,
        bool $use_phpdoc_format
    ) {
        return 'key-of<' . $this->param_name . '>';
    }
}
