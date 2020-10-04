<?php

class rex_validation_rule {
    const TYPE_NOT_EMPTY = 'notEmpty';
    const TYPE_MIN_LENGTH = 'minLength';
    const TYPE_MAX_LENGTH = 'maxLength';
    const TYPE_MIN = 'min';
    const TYPE_MAX = 'max';
    const TYPE_URL = 'url';
    const TYPE_EMAIL = 'email';
    const TYPE_MATCH = 'match';
    const TYPE_NOT_MATCH = 'notMatch';
    const TYPE_VALUES = 'values';
    const TYPE_CUSTOM = 'custom';

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
     * @param string      $type    Validator type, e.g. one of rex_validation_rule::* but could also be extended via rex-factory
     * @param null|string $message Message which is used if this validator type does not match
     * @param mixed       $option  Type specific option
     */
    public function __construct($type, $message = null, $option = null) {
        $this->type = $type;
        $this->message = $message;
        $this->option = $option;
    }

    /**
     * Validator type, e.g. one of rex_validation_rule::* but could also be extended via rex-factory
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
