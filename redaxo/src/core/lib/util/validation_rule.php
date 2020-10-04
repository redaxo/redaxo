<?php

class rex_validation_rule {
    /**
     * @var string
     */
    protected $type;
    /**
     * @var null|string
     */
    protected $message;
    /**
     * @var mixed
     */
    protected $option;

    /**
     * rex_validation_rule constructor.
     *
     * @param string      $type    Validator type (any static method name of this class)
     * @param null|string $message Message which is used if this validator type does not match
     * @param mixed       $option  Type specific option
     */
    public function __construct($type, $message = null, $option = null) {
        $this->type = $type;
        $this->message = $message;
        $this->option = $option;
    }

    /**
     * Validator type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Type specific option
     *
     * @return mixed
     */
    public function getOption() {
        return $this->option;
    }

    /**
     * Message which is used if this validator type does not match
     *
     * @return null|string
     */
    public function getMessage() {
        return $this->message;
    }
}
