<?php

/**
 * Class to represent sql columns.
 *
 * @author gharlan
 *
 * @package redaxo\core\sql
 */
class rex_sql_column
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $type;
    /**
     * @var bool
     */
    private $nullable;
    /**
     * @var null|string
     */
    private $default;
    /**
     * @var null|string
     */
    private $extra;

    /**
     * @var bool
     */
    private $modified = false;

    /**
     * @param string      $name
     * @param string      $type
     * @param bool        $nullable
     * @param null|string $default
     * @param null|string $extra
     */
    public function __construct($name, $type, $nullable = false, $default = null, $extra = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->default = $default;
        $this->extra = $extra;
    }

    /**
     * @param bool $modified
     *
     * @return $this
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return bool
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this->setModified(true);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this->setModified(true);
    }

    /**
     * @return string The column type, including its size, e.g. int(10) or varchar(255)
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param bool $nullable
     *
     * @return $this
     */
    public function setNullable($nullable)
    {
        $this->nullable = $nullable;

        return $this->setModified(true);
    }

    /**
     * @return bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @param null|string $default
     *
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this->setModified(true);
    }

    /**
     * @return null|string
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param null|string $extra
     *
     * @return $this
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this->setModified(true);
    }

    /**
     * @return null|string
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @return bool
     */
    public function equals(self $column)
    {
        return
            $this->name === $column->name &&
            $this->type === $column->type &&
            $this->nullable === $column->nullable &&
            $this->default === $column->default &&
            $this->extra === $column->extra;
    }
}
