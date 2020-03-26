<?php

/**
 * @author gharlan
 *
 * @package redaxo\core\login
 */
class rex_backend_password_policy extends rex_password_policy
{
    use rex_factory_trait;

    private $reusePrevious;

    public function __construct(array $options)
    {
        if (isset($options['reuse_previous'])) {
            $this->reusePrevious = $options['reuse_previous'];
        }

        unset($options['reuse_previous']);
        unset($options['validity']);

        parent::__construct($options);
    }

    /**
     * @return static
     */
    public static function factory(array $options)
    {
        $class = static::getFactoryClass();

        return new $class($options);
    }

    public function check($password, $id = null)
    {
        if (true !== $msg = parent::check($password, $id)) {
            return $msg;
        }

        if (null === $id || !isset($this->reusePrevious['not_last']) && !isset($this->reusePrevious['not_months'])) {
            return true;
        }

        $user = rex_user::require($id);
        $previousPasswords = $user->getValue('previous_passwords');

        if (!$previousPasswords) {
            return true;
        }

        $password = sha1($password);
        $previousPasswords = $this->cleanUpPreviousPasswords(json_decode($previousPasswords, true));

        foreach ($previousPasswords as $previousPassword) {
            if (rex_backend_login::passwordVerify($password, $previousPassword[0], true)) {
                return rex_i18n::msg('password_already_used');
            }
        }

        return true;
    }

    /**
     * @internal
     */
    public function updatePreviousPasswords(?rex_user $user, string $password): array
    {
        if (!isset($this->reusePrevious['not_last']) && !isset($this->reusePrevious['not_months'])) {
            return [];
        }

        if ($user) {
            $previousPasswords = $user->getValue('previous_passwords');
            $previousPasswords = $previousPasswords ? json_decode($previousPasswords, true) : [];
        } else {
            $previousPasswords = [];
        }
        $previousPasswords[] = [$password, time()];

        return $this->cleanUpPreviousPasswords($previousPasswords);
    }

    private function cleanUpPreviousPasswords(array $previousPasswords): array
    {
        if (!isset($this->reusePrevious['not_last']) && !isset($this->reusePrevious['not_months'])) {
            return [];
        }

        $minI = count($previousPasswords) - ($this->reusePrevious['not_last'] ?? 0);
        $minTimestamp = isset($this->reusePrevious['not_months']) ? strtotime('-'.$this->reusePrevious['not_months'].' months') : time() + 1;

        $return = [];

        $i = 0;
        foreach ($previousPasswords as $previousPassword) {
            if ($i >= $minI || $previousPassword[1] >= $minTimestamp) {
                $return[] = $previousPassword;
            }
            ++$i;
        }

        return $return;
    }
}
