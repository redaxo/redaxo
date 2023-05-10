<?php

class rex_backend_login_ldap extends rex_backend_login
{
    private $clearTextPassword;
    private static $ignorePassword = false;

    public function __construct()
    {
        parent::__construct();
    }

    /** {@inheritdoc} */
    public function setLogin(#[SensitiveParameter] $login, #[SensitiveParameter] $password, $isPreHashed = false)
    {
        if (!$isPreHashed) {
            $this->clearTextPassword = $password;
        }
        parent::setLogin($login, $password, $isPreHashed);
    }

    /** {@inheritdoc} */
    public function checkLogin()
    {
        if (($this->cache && $this->loginStatus != 0) || $this->logout || $this->userLogin == '') {
            // directly to parent class
            return parent::checkLogin();
        }

        if ($this->doAuthLdap()) {
            // we have overridden the passwordVerify method
            self::$ignorePassword = true;
            $result = parent::checkLogin();
            self::$ignorePassword = false;
            return $result;
        } elseif (rex::getProperty('backend_login_ldap', [])['allow_fallback'] ?? false) {
            return parent::checkLogin();
        } else {
            return false;
        }
    }

    /**
     * Validate user via ownCloud. We use the provisioning API
     * available since OC 8. The user counts as authenticated if the
     * API call succeeds and contains all relevant fields.
     */
    private function doAuthLdap()
    {
        $config = rex::getProperty('backend_login_ldap', []);
        $uri = $config['ldap_uri'] ?? '';
        $bound = false;
        $ds = ldap_connect($uri);
        if ($ds) {
            if (ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3)) {
                if (ldap_set_option($ds, LDAP_OPT_REFERRALS, 0)) {
                    if (empty($config['starttls']) || ldap_start_tls($ds)) {
                        foreach (($config['bind_dns'] ?? []) as $bind_dn) {
                            $bind_dn = preg_replace('/%USER%/', $this->userLogin, $bind_dn);
                            $bound = ldap_bind($ds, $bind_dn, $this->clearTextPassword);
                            if ($bound) {
                                break;
                            }
                        }
                    }
                }
            }
            ldap_close($ds);
        }
        return $bound;
    }

    /** {@inheritdoc} */
    public static function passwordVerify(#[SensitiveParameter] $password, #[SensitiveParameter] $hash, $isPreHashed = false)
    {
        if (self::$ignorePassword) {
            return true;
        }
        return parent::passwordVerify($password, $hash, $isPreHashed);
    }
}
