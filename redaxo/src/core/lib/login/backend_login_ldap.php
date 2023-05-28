<?php

class rex_backend_login_ldap extends rex_backend_login
{
    public const CONFIG_PREFIX = 'backend_login_ldap';
    private $bindDn = null;
    private $clearTextPassword;
    private static $ignorePassword = false;
    private $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = rex::getProperty('backend_login_ldap', []);
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
            $user = rex_sql::factory();
            $user->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'user WHERE login = ? LIMIT 1', [$this->userLogin]);
            if ($user->getRows() === 0) {
                if ($config['create_users']) {
                    $this->addLdapUser();
                }
            } else {
                $this->updateRexUserFromLdap($user);
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

    /**
     * Fetch the supported attributes from LDAP if present.
     *
     * @return array
     */
    private function fetchLdapAttributes():array
    {
        $ldapValues = [
            'name' => $this->userLogin,
            'email' => null,
            'description' => null,
            'roles' => null,
        ];

        $ds = $this->openLdapConnection();
        if ($ds && ldap_bind($ds, $this->bindDn, $this->clearTextPassword)) {
            $config = rex::getProperty('backend_login_ldap', []);
            $ldapFilter = '(objectClass=*)'; // ldap command requires some filter
            $attributes = [];
            array_walk_recursive($config['attributes' ], function($value) use (&$attributes) { $attributes[] = $value; });
            $searchResult = ldap_read($ds, $this->bindDn, $ldapFilter, $attributes);
            if ($searchResult) {
                $ldapEntry = ldap_get_entries($ds, $searchResult);
                foreach (array_keys($ldapValues) as $key) {
                    $ldapAttributes = $config['attributes'][$key] ?? [];
                    if (!is_array($ldapAttributes)) {
                        $ldapAttributes = [ $ldapAttributes ];
                    }
                    foreach ($ldapAttributes as $ldapAttribute) {
                        $ldapAttribute = strtolower($ldapAttribute);
                        $attributeValues = $ldapEntry[0][$ldapAttribute] ?? [];
                        if (!empty($attributeValues)) {
                            if ($key == 'roles') {
                                unset($attributeValues['count']);
                                $ldapValues[$key] = array_merge($ldapValues[$key] ?? [], $attributeValues);
                            } else {
                                $ldapValues[$key] = $attributeValues[0];
                            }
                        }
                    }
                }
            }
            ldap_close($ds);
        }

        $rexRoles = [];
        foreach ($config['roles'] as $rexRole => $ldapRoleOptions) {
            if (!is_array($ldapRoleOptions)) {
                $ldapRoleOptions = [ $ldapRoleOptions ];
            }
            $ldapRoleOptions = array_map(fn($value) => strtolower($value), $ldapRoleOptions);
            foreach (($ldapValues['roles'] ?? []) as $ldapRole) {
                $ldapRole = strtolower($ldapRole);
                if (in_array($ldapRole, $ldapRoleOptions)) {
                    $rexRoles[] = $rexRole;
                }
            }
        }
        $rexRoles = array_values(array_unique($rexRoles));
        $isAdmin = array_search('admin', $rexRoles) !== false;

        if ($isAdmin) {
            $rexRoles = [];
        } else {
            // there is no implemented way to get the role id from the name, so search
            // for it ...
            $configuredRexRoles = array_keys($config['roles']);
            $sql = rex_sql::factory();
            $dbUserRoles = $sql->getArray('SELECT id, name FROM ' . rex::getTablePrefix() . 'user_role WHERE FIND_IN_SET(name, ?)', [implode(',', $configuredRexRoles)]);
            $dbUserRoles = array_column($dbUserRoles, 'id', 'name');
            $rexRoles = array_values(array_intersect_key($dbUserRoles, array_flip($rexRoles)));
        }

        $ldapValues['isAdmin'] = (int)$isAdmin;
        $ldapValues['roles'] = $rexRoles;
        $ldapValues['overrideRoles'] = array_values($dbUserRoles ?? []);

        return $ldapValues;
    }

    private function addLdapUser()
    {
        $ldapValues = $this->fetchLdapAttributes();

        $userStatus = 1;
        $userPswHash = rex_login::passwordHash($this->clearTextPassword);

        $addUser = rex_sql::factory();
        $addUser->setTable(rex::getTablePrefix() . 'user');
        $addUser->setValue('name', $ldapValues['name']);
        $addUser->setValue('password', $userPswHash);
        $addUser->setValue('login', $this->userLogin);
        $addUser->setValue('description', $ldapValues['description']);
        $addUser->setValue('email', $ldapValues['email']);
        $addUser->setValue('admin', $ldapValues['isAdmin']);
        $addUser->setValue('language', '');
        $addUser->setValue('startpage', '');
        $addUser->setValue('role', empty($ldapValues['roles']) ? null : implode(',', ldapValues['roles']));
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

    private function updateRexUserFromLdap(rex_sql $userSql)
    {
        $ldapValues = $this->fetchLdapAttributes();

        $userId = $userSql->getValue('id');

        $updateSql = rex_sql::factory();
        $updateSql->setTable(rex::getTablePrefix() . 'user');
        $updateSql->setWhere(['id' => $userId]);

        $needUpdate = false;
        if ((int)$ldapValues['isAdmin'] !== (int)$userSql->getValue('admin')) {
            $needUpdate = true;
            $updateSql->setValue('admin', (int)$ldapValues['isAdmin']);
            if ($ldapValues['isAdmin']) {
                $updateSql->setValue('role', null);
            }
        }
        if (!$ldapValues['isAdmin']) {
            $currentRoles = explode(',', ($userSql->getValue('role') ?? ''));
            $newRoles = array_diff($currentRoles, $ldapValues['overrideRoles']);
            $newRoles = array_merge($newRoles, $ldapValues['roles'] ?? []);
            sort($currentRoles);
            sort($newRoles);
            if ($currentRoles != $newRoles) {
                $needUpdate = true;
                $updateSql->setValue('role', implode(',', $newRoles));
            }
        }
        $userPswHash = rex_login::passwordHash($this->clearTextPassword);
        if ($userPswHash != $userSql->getValue('password')) {
            $needUpdate = true;
            $updateSql->setValue('password', $userPswHash);
            $updateSql->setDateTimeValue('password_changed', time());
            $updateSql->setValue('password_change_required', 0);
        }
        foreach (['name', 'description', 'email'] as $key) {
            if ($ldapValues[$key] != $userSql->getValue($key)) {
                $needUpdate = true;
                $updateSql->setValue($key, $ldapValues[$key]);
            }
        }
        if ($needUpdate) {
            $updateSql->update();
            rex_user::clearInstance($userId);
            $user = rex_user::require($userId);
            rex_extension::registerPoint(new rex_extension_point('USER_UPDATED', '', [
                'id' => $userSql->getValue('id'),
                'user' => $user,
                'password' => $this->clearTextPassword,
            ], true));
        }
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
