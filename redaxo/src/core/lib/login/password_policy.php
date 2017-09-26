<?php

/**
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_password_policy
{
    use rex_factory_trait;

    private $options;

    protected function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return static
     */
    public static function factory(array $options)
    {
        $class = static::getFactoryClass();

        return new $class($options);
    }

    public function check($password)
    {
        if ($this->isValid($password)) {
            return true;
        }

        return rex_i18n::msg('password_invalid', $this->getRule());
    }

    protected function getRule()
    {
        $parts = [];

        foreach ($this->options as $key => $options) {
            if (isset($options['min'], $options['max'])) {
                $constraint = rex_i18n::msg('password_rule_between', $options['min'], $options['max']);
            } elseif (isset($options['max'])) {
                $constraint = rex_i18n::msg('password_rule_max', $options['max']);
            } else {
                $constraint = rex_i18n::msg('password_rule_min', $options['min']);
            }

            $parts[] = rex_i18n::msg('password_rule_'.$key, $constraint);
        }

        return implode('; ', $parts);
    }

    protected function isValid($password)
    {
        foreach ($this->options as $key => $options) {
            switch ($key) {
                case 'length':
                    $count = mb_strlen($password);
                    break;
                case 'letter':
                    $count = preg_match_all('/[a-zA-Z]/', $password);
                    break;
                case 'uppercase':
                    $count = preg_match_all('/[A-Z]/', $password);
                    break;
                case 'lowercase':
                    $count = preg_match_all('/[a-z]/', $password);
                    break;
                case 'digit':
                    $count = preg_match_all('/\d/', $password);
                    break;
                case 'symbol':
                    $count = preg_match_all('/[^a-zA-Z0-9]/', $password);
                    break;

                default:
                    throw new rex_exception(sprintf('Unknown password_policy key "%s".', $key));
            }

            if (!$this->matchesCount($count, $options)) {
                return false;
            }
        }

        return true;
    }

    protected function matchesCount($count, array $options)
    {
        if (isset($options['min']) && $count < $options['min']) {
            return false;
        }

        if (isset($options['max']) && $count > $options['max']) {
            return false;
        }

        return true;
    }
}
