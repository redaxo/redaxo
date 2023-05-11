<?php

class rex_backend_login_ldap extends rex_backend_login
{
    private $bindDn = null;
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
            $config = rex::getProperty('backend_login_ldap', []);
            if ($config['create_users']) {
                $user = rex_sql::factory();
                $user->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'user WHERE login = ?', [$this->userLogin]);
                if ($user->getRows() === 0) {
                    $this->addLdapUser();
                }
            }
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
        $bound = false;
        $this->bindDn = null;
        $ds = $this->openLdapConnection();
        if ($ds) {
            $config = rex::getProperty('backend_login_ldap', []);
            foreach (($config['bind_dns'] ?? []) as $bindDn) {
                $bindDn = preg_replace('/%USER%/', $this->userLogin, $bindDn);
                $bound = ldap_bind($ds, $bindDn, $this->clearTextPassword);
                if ($bound) {
                    $this->bindDn = $bindDn;
                    break;
                }
            }
            ldap_close($ds);
        }
        return $bound;
    }

    private function openLdapConnection()
    {
        $config = rex::getProperty('backend_login_ldap', []);
        $uri = $config['ldap_uri'] ?? '';
        $ds = ldap_connect($uri);
        if ($ds) {
            if (ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3)) {
                if (ldap_set_option($ds, LDAP_OPT_REFERRALS, 0)) {
                    if (empty($config['starttls']) || ldap_start_tls($ds)) {
                        return $ds;
                    }
                }
            }
            ldap_close($ds);
        }
        return null;
    }

    private function addLdapUser()
    {
        $ldapValues = [
            'name' => $this->userLogin,
            'email' => null,
            'description' => null,
        ];

        $ds = $this->openLdapConnection();
        if ($ds && ldap_bind($ds, $this->bindDn, $this->clearTextPassword)) {
            $config = rex::getProperty('backend_login_ldap', []);
            $ldapFilter = '(objectClass=*)'; // ldap command requires some filter
            $attributes = array_values($config['attributes']);
            $searchResult = ldap_read($ds, $this->bindDn, $ldapFilter, $attributes);
            if ($searchResult) {
                $ldapEntry = ldap_get_entries($ds, $searchResult);
                foreach (array_keys($ldapValues) as $key) {
                    $ldapAttribute = strtolower($config['attributes'][$key] ?? '');
                    $ldapValue = $ldapEntry[0][$ldapAttribute][0] ?? null;
                    if (!empty($ldapValue)) {
                        $ldapValues[$key] = $ldapValue;
                    }
                }
            }
            ldap_close($ds);
        }

        $userStatus = 1;
        $userPswHash = rex_login::passwordHash($this->clearTextPassword);

        $addUser = rex_sql::factory();
        $addUser->setTable(rex::getTablePrefix() . 'user');
        $addUser->setValue('name', $ldapValues['name']);
        $addUser->setValue('password', $userPswHash);
        $addUser->setValue('login', $this->userLogin);
        $addUser->setValue('description', $ldapValues['description']);
        $addUser->setValue('email', $ldapValues['email']);
        $addUser->setValue('admin', 0);
        $addUser->setValue('language', '');
        $addUser->setValue('startpage', '');
        $addUser->setValue('role', null);
        $addUser->addGlobalCreateFields();
        $addUser->addGlobalUpdateFields();
        $addUser->setDateTimeValue('password_changed', time());
        $addUser->setArrayValue('previous_passwords', []);
        $addUser->setValue('password_change_required', 0);
        $addUser->setValue('status', 1);

        $addUser->insert();

        rex_extension::registerPoint(new rex_extension_point('USER_ADDED', '', [
            'id' => $addUser->getLastId(),
            'user' => rex_user::require((int) $addUser->getLastId()),
            'password' => $this->clearTextPassword,
        ], true));
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
