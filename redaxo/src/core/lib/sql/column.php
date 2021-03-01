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
     * @var null|string
     */
    private $comment;

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
     * @param null|string $comment
     */
    public function __construct($name, $type, $nullable = false, $default = null, $extra = null, $comment = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->default = $default;
        $this->extra = $extra;
        $this->comment = $comment;
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
     * @param null|string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this->setModified(true);
    }

    /**
     * @return null|string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return bool
     */
    public function equals(self $column)
    {
        if ($this->name !== $column->name) {
            return false;
        }
        if ($this->type !== $column->type) {
            return false;
        }
        if ($this->nullable !== $column->nullable) {
            return false;
        }
        if ($this->default !== $column->default) {
            return false;
        }
        if ($this->extra !== $column->extra) {
            return false;
        }
        return $this->comment === $column->comment;
    }
}
