<?php
/**
 * @author Daniel Weitenauer
 * @copyright (c) 2019 studio ahoi
 */

abstract class rex_structure_field_base
{
    use rex_factory_trait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @param string $id
     *
     * @return static
     */
    public static function factory($id)
    {
        $class = self::getFactoryClass();

        return new $class($id);
    }

    /**
     * @param string $id
     */
    protected function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    abstract public function get();
}
