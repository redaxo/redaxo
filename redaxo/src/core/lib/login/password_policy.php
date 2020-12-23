<?php

/**
 * @author gharlan
 *
 * @package redaxo\core\login
 */
class rex_password_policy
{
    /**
     * @var array<string, array{min?: int, max?: int}>
     */
    private $options;

    /**
     * @param array<string, array{min?: int, max?: int}> $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string   $password
     * @param null|int $id
     *
     * @throws rex_exception
     *
     * @return true|string `true` on success, otherwise an error message
     */
    public function check($password, $id = null)
    {
        if ($this->isValid($password)) {
            return true;
        }

        return rex_i18n::msg('password_invalid', $this->getDescription());
    }

    public function getDescription(): ?string
    {
        $parts = [];

        foreach ($this->options as $key => $options) {
            if (isset($options['min'], $options['max']) && $options['min']) {
                $constraint = rex_i18n::msg('password_rule_between', $options['min'], $options['max']);
            } elseif (isset($options['max'])) {
                $constraint = rex_i18n::msg('password_rule_max', $options['max']);
            } elseif (isset($options['min']) && $options['min']) {
                $constraint = rex_i18n::msg('password_rule_min', $options['min']);
            } else {
                continue;
            }

            $parts[] = rex_i18n::msg('password_rule_'.$key, $constraint);
        }

        return $parts ? implode('; ', $parts) : null;
    }

    /**
     * @return string
     *
     * @deprecated since 5.12, use `getDescription` instead
     */
    #[\JetBrains\PhpStorm\Deprecated(reason: 'since 5.12, use `getDescription` instead', replacement: '%class%->getDescription()')]
    protected function getRule()
    {
        return $this->getDescription() ?? '';
    }

    /**
     * @return bool
     */
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
                    $count = preg_match_all('/[0-9]/', $password);
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

    /**
     * @param int $count
     * @param array{min?: int, max?: int} $options
     *
     * @return bool
     */
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
