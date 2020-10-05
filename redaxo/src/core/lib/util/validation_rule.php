<?php

/**
 * @package redaxo\core
 */
class rex_validation_rule
{
    public const TYPE_NOT_EMPTY = 'notEmpty';
    public const TYPE_MIN_LENGTH = 'minLength';
    public const TYPE_MAX_LENGTH = 'maxLength';
    public const TYPE_MIN = 'min';
    public const TYPE_MAX = 'max';
    public const TYPE_URL = 'url';
    public const TYPE_EMAIL = 'email';
    public const TYPE_MATCH = 'match';
    public const TYPE_NOT_MATCH = 'notMatch';
    public const TYPE_VALUES = 'values';
    public const TYPE_CUSTOM = 'custom';

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
    public function __construct(string $type, ?string $message = null, $option = null)
    {
        $this->type = $type;
        $this->message = $message;
        $this->option = $option;
    }

    /**
     * Validator type, e.g. one of rex_validation_rule::* but could also be extended via rex-factory.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Type specific option.
     *
     * @return mixed
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Message which is used if this validator type does not match.
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }
}
