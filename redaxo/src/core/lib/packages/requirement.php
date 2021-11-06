<?php

/**
 * @package redaxo\core\packages
 */
class rex_package_requirement
{
    /**
     * @var array
     */
    private $requirements;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $i18nPrefix;

    public function __construct(array $requirements, string $i18nPrefix)
    {
        $this->requirements = $requirements;
        $this->i18nPrefix = $i18nPrefix;
    }

    /**
     * Translates the given key.
     *
     * @param string $key Key
     *
     * @return string Tranlates text
     */
    protected function i18n($key)
    {
        $args = func_get_args();
        $key = $this->i18nPrefix . $args[0];
        if (!rex_i18n::hasMsg($key)) {
            $key = 'package_' . $args[0];
        }
        $args[0] = $key;

        return call_user_func_array([rex_i18n::class, 'msg'], $args);
    }

    /**
     * Returns the message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Checks whether the redaxo requirement is met.
     *
     * @param string $redaxoVersion REDAXO version
     *
     * @return bool
     */
    public function checkRedaxoRequirement($redaxoVersion)
    {
        if (isset($this->requirements['redaxo']) && !rex_version::matchVersionConstraints($redaxoVersion,$this->requirements['redaxo'])) {
            $this->message = $this->i18n('requirement_error_redaxo_version', $redaxoVersion, $this->requirements['redaxo']);
            return false;
        }
        return true;
    }
}
