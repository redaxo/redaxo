<?php

/**
 * @author mstaab
 *
 * @package redaxo\core\login
 */
final class rex_login_policy
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
     * Returns the number of allowed login tries, until login will be delayed.
     *
     * Additional rules might apply via `rex_backend_password_policy`.
     *
     * @return positive-int
     */
    public function getMaxTries(): int
    {
        $key = 'login_tries';

        if (array_key_exists($key, $this->options)) {
            $val = (int) $this->options[$key];
            if ($val <= 0) {
                throw new InvalidArgumentException('Invalid value for option "' . $key . '": ' . $val);
            }
            return $val;
        }

        // defaults, in case config.yml does not define values
        // e.g. because of a redaxo core update from a version.
        return 50;
    }

    /**
     * Returns the relogin delay in seconds.
     *
     * @return positive-int
     */
    public function getReloginDelay(): int
    {
        $key = 'relogin_delay';

        if (array_key_exists($key, $this->options)) {
            $val = (int) $this->options[$key];
            if ($val <= 0) {
                throw new InvalidArgumentException('Invalid value for option "' . $key . '": ' . $val);
            }
            return $val;
        }

        // defaults, in case config.yml does not define values
        // e.g. because of a redaxo core update from a version.
        return 5;
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
