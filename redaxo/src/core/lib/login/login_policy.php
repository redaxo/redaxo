<?php

/**
 * @author mstaab
 *
 * @package redaxo\core\login
 */
class rex_login_policy
{
    /**
     * @var array<string, int|bool>
     */
    private $options;

    /**
     * @param array<string, int|bool> $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param 'login_tries_1'|'relogin_delay_1'|'login_tries_2'|'relogin_delay_2' $key
     */
    public function getSetting(string $key): int
    {
        if (array_key_exists($key, $this->options)) {
            return (int) $this->options[$key];
        }

        // defaults, in case config.yml does not define values
        // e.g. because of a redaxo core update from a version.
        switch ($key) {
            case 'login_tries_1':
                return 3;
            case 'relogin_delay_1':
                return 5;
            case 'login_tries_2':
                return 50;
            case 'relogin_delay_2':
                return 3600;
        }

        throw new rex_exception('Invalid login policy key: ' . $key);
    }

    public function isStayLoggedInEnabled(): bool
    {
        $key = 'enable_stay_logged_in';

        if (array_key_exists($key, $this->options)) {
            return (bool) $this->options[$key];
        }

        // enabled by default
        return true;
    }
}
