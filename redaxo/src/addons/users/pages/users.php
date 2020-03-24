<?php

/**
 * @package redaxo5
 */

$message = '';
$content = '';

$user_id = rex_request('user_id', 'int');
$info = '';
$warnings = [];

$user = null;

$sql = rex_sql::factory();
if (0 != $user_id) {
    $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'user WHERE id = ' . $user_id . ' LIMIT 2');
    if (1 != $sql->getRows()) {
        $user_id = 0;
    } else {
        $user = new rex_user($sql);
    }
}

// Allgemeine Infos
$userpsw = rex_post('userpsw', 'string');
$userlogin = rex_post('userlogin', 'string');
$username = rex_request('username', 'string');
$userdesc = rex_request('userdesc', 'string');
$useremail = rex_request('useremail', 'string');
$useradmin = rex_request('useradmin', 'int');
$userstatus = rex_request('userstatus', 'int');

// role
$sel_role = new rex_select();
$sel_role->setSize(1);
$sel_role->setName('userrole[]');
$sel_role->setId('rex-js-user-role');
$sel_role->setMultiple();
$sel_role->setAttribute('class', 'form-control selectpicker');
// $sel_role->addOption(rex_i18n::msg('user_no_role'), 0);
$roles = [];
$sql_role = rex_sql::factory();
$sql_role->setQuery('SELECT id, name FROM ' . rex::getTablePrefix() . 'user_role ORDER BY name');
foreach ($sql_role as $role) {
    $roles[$role->getValue('id')] = $role->getValue('name');
    $sel_role->addOption($role->getValue('name'), $role->getValue('id'));
}
$userrole = rex_request('userrole', 'array');

// backend sprache
$sel_be_sprache = new rex_select();
$sel_be_sprache->setSize(1);
$sel_be_sprache->setName('userperm_be_sprache');
$sel_be_sprache->setId('rex-user-perm-mylang');
$sel_be_sprache->setAttribute('class', 'form-control selectpicker');
$sel_be_sprache->addOption('default', '');
$saveLocale = rex_i18n::getLocale();
$langs = [];
foreach (rex_i18n::getLocales() as $locale) {
    rex_i18n::setLocale($locale, false); // Locale nicht neu setzen
    $sel_be_sprache->addOption(rex_i18n::msg('lang'), $locale);
}
rex_i18n::setLocale($saveLocale, false);
$userperm_be_sprache = rex_request('userperm_be_sprache', 'string');

// ----- welche startseite
$sel_startpage = new rex_select();
$sel_startpage->setSize(1);
$sel_startpage->setName('userperm_startpage');
$sel_startpage->setId('rex-user-perm-startpage');
$sel_startpage->setAttribute('class', 'form-control selectpicker');
$sel_startpage->setAttribute('data-live-search', 'true');
$sel_startpage->addOption('default', '');

$startpages = [];
foreach (rex_be_controller::getPages() as $page => $pageObj) {
    /** @var rex_be_page $pageObj */
    if ($pageObj->hasNavigation() && !$pageObj->isHidden()) {
        $startpages[$page] = $pageObj->getTitle();
    }
}
asort($startpages);
$sel_startpage->addOptions($startpages);
$userperm_startpage = rex_request('userperm_startpage', 'string');

// --------------------------------- Title

// --------------------------------- FUNCTIONS
$FUNC_UPDATE = '';
$FUNC_APPLY = '';
$FUNC_DELETE = '';
if (0 != $user_id && (rex::getUser()->isAdmin() || !$sql->getValue('admin'))) {
    $FUNC_UPDATE = rex_request('FUNC_UPDATE', 'string');
    $FUNC_APPLY = rex_request('FUNC_APPLY', 'string');
    $FUNC_DELETE = rex_request('FUNC_DELETE', 'string');
} else {
    $user_id = 0;
}
$FUNC_ADD = rex_request('FUNC_ADD', 'string');
$save = rex_request('save', 'int');
$adminchecked = '';

if ($save && ($FUNC_ADD || $FUNC_UPDATE || $FUNC_APPLY)) {
    if (!rex_csrf_token::factory('user_edit')->isValid()) {
        $warnings[] = rex_i18n::msg('csrf_token_invalid');
    }

    $validator = rex_validator::factory();
    if ($useremail && !rex_validator::factory()->email($useremail)) {
        $warnings[] = rex_i18n::msg('invalid_email');
    }

    if ($userpsw && (true !== $msg = rex_backend_password_policy::factory(rex::getProperty('password_policy', []))->check($userpsw, $user_id))) {
        if (rex::getUser()->isAdmin()) {
            $msg .= ' '.rex_i18n::msg('password_admin_notice');
        }
        $warnings[] = $msg;
    }
}

if ($warnings) {
    // do not save
} elseif ('' != $FUNC_UPDATE || '' != $FUNC_APPLY) {
    $loginReset = rex_request('logintriesreset', 'int');
    $userstatus = rex_request('userstatus', 'int');

    if (rex::getUser()->isAdmin() && $user_id == rex::getUser()->getId()) {
        $useradmin = 1;
    }

    $updateuser = rex_sql::factory();
    $updateuser->setTable(rex::getTablePrefix() . 'user');
    $updateuser->setWhere(['id' => $user_id]);
    $updateuser->setValue('name', $username);
    $updateuser->setValue('role', implode(',', $userrole));
    $updateuser->setValue('admin', rex::getUser()->isAdmin() && 1 == $useradmin ? 1 : 0);
    $updateuser->setValue('language', $userperm_be_sprache);
    $updateuser->setValue('startpage', $userperm_startpage);
    $updateuser->addGlobalUpdateFields();
    $updateuser->setValue('description', $userdesc);
    $updateuser->setValue('email', $useremail);
    if (1 == $loginReset) {
        $updateuser->setValue('login_tries', '0');
    }
    if (1 == $userstatus) {
        $updateuser->setValue('status', 1);
    } else {
        $updateuser->setValue('status', 0);
    }

    if ('' != $userpsw) {
        $updateuser->setValue('password', rex_login::passwordHash($userpsw));
    }

    $updateuser->update();

    $info = rex_i18n::msg('user_data_updated');

    $sql->setQuery('SELECT * FROM ' . rex::getTable('user') . ' WHERE id = ?', [$user_id]);
    $user = new rex_user($sql);

    rex_extension::registerPoint(new rex_extension_point('USER_UPDATED', '', [
        'id' => $user_id,
        'user' => $user,
        'password' => $userpsw,
    ], true));

    if (isset($FUNC_UPDATE) && '' != $FUNC_UPDATE) {
        $user_id = 0;
        $FUNC_UPDATE = '';
    }
} elseif ('' != $FUNC_DELETE) {
    // man kann sich selbst nicht loeschen..
    if (rex::getUser()->getId() == $user_id) {
        $warnings[] = rex_i18n::msg('user_notdeleteself');
    } elseif (!rex_csrf_token::factory('user_delete')->isValid()) {
        $warnings[] = rex_i18n::msg('csrf_token_invalid');
    } else {
        $deleteuser = rex_sql::factory();
        $deleteuser->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'user WHERE id = ? LIMIT 1', [$user_id]);
        $info = rex_i18n::msg('user_deleted');

        rex_extension::registerPoint(new rex_extension_point('USER_DELETED', '', [
            'id' => $user_id,
            'user' => $user,
        ], true));
    }

    $user_id = 0;
} elseif ('' != $FUNC_ADD && 1 == $save) {
    $adduser = rex_sql::factory();
    $adduser->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'user WHERE login = ?', [$userlogin]);

    if (0 == $adduser->getRows() && '' != $userlogin && '' != $userpsw) {
        $userpswHash = rex_login::passwordHash($userpsw);

        $adduser = rex_sql::factory();
        $adduser->setTable(rex::getTablePrefix() . 'user');
        $adduser->setValue('name', $username);
        $adduser->setValue('password', $userpswHash);
        $adduser->setValue('login', $userlogin);
        $adduser->setValue('description', $userdesc);
        $adduser->setValue('email', $useremail);
        $adduser->setValue('admin', rex::getUser()->isAdmin() && 1 == $useradmin ? 1 : 0);
        $adduser->setValue('language', $userperm_be_sprache);
        $adduser->setValue('startpage', $userperm_startpage);
        $adduser->setValue('role', implode(',', $userrole));
        $adduser->addGlobalCreateFields();
        if (isset($userstatus) && 1 == $userstatus) {
            $adduser->setValue('status', 1);
        } else {
            $adduser->setValue('status', 0);
        }

        $adduser->insert();
        $user_id = 0;
        $FUNC_ADD = '';
        $info = rex_i18n::msg('user_added');

        rex_extension::registerPoint(new rex_extension_point('USER_ADDED', '', [
            'id' => $adduser->getLastId(),
            'user' => new rex_user($adduser->setQuery('SELECT * FROM '.rex::getTable('user').' WHERE id = ?', [$adduser->getLastId()])),
            'password' => $userpsw,
        ], true));
    } else {
        if (1 == $useradmin) {
            $adminchecked = 'checked="checked"';
        }

        // userrole
        $sel_role->setSelected($userrole);

        // userperm_be_sprache
        if ('' == $userperm_be_sprache) {
            $userperm_be_sprache = 'default';
        }
        $sel_be_sprache->setSelected($userperm_be_sprache);

        // userperm_startpage
        if ('' == $userperm_startpage) {
            $userperm_startpage = 'default';
        }
        $sel_startpage->setSelected($userperm_startpage);

        if ($adduser->getRows()) {
            $warnings[] = rex_i18n::msg('user_login_exists');
        }
        if (!$userlogin) {
            $warnings[] = rex_i18n::msg('user_missing_login');
        }
        if (!$userpsw) {
            $warnings[] = rex_i18n::msg('user_missing_password');
        }
    }
}

// ---------------------------------- ERR MSG

if ('' != $info) {
    $message .= rex_view::info($info);
}

if (!empty($warnings)) {
    $message .= rex_view::warning(implode('<br/>', $warnings));
}

// --------------------------------- FORMS

$SHOW = true;

if ('' != $FUNC_ADD || $user_id > 0) {
    $SHOW = false;

    $statuschecked = '';
    if ('' != $FUNC_ADD) {
        $statuschecked = 'checked="checked"';
    }

    $buttons = '';
    if ($user_id > 0) {
        // User Edit

        $form_label = rex_i18n::msg('edit_user');
        $add_hidden = '<input type="hidden" name="user_id" value="' . $user_id . '" />';
        $add_user_login = '<p class="form-control-static">' . rex_escape($sql->getValue(rex::getTablePrefix() . 'user.login')) . '</p>';

        $formElements = [];

        $n = [];
        $n['field'] = '<a class="btn btn-abort" href="' . rex_url::currentBackendPage() . '">' . rex_i18n::msg('form_abort') . '</a>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="FUNC_UPDATE" value="1" ' . rex::getAccesskey(rex_i18n::msg('save_and_close_tooltip'), 'save') . '>' . rex_i18n::msg('user_save') . '</button>';
        $formElements[] = $n;

        $n = [];
        $n['field'] = '<button class="btn btn-apply" type="submit" name="FUNC_APPLY" value="1" ' . rex::getAccesskey(rex_i18n::msg('save_and_goon_tooltip'), 'apply') . '>' . rex_i18n::msg('user_apply') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');
        unset($formElements);

        if (!$FUNC_UPDATE && !$FUNC_APPLY) {
            $sql = rex_sql::factory();
            $sql->setQuery('select * from ' . rex::getTablePrefix() . 'user where id=' . $user_id);

            if (1 == $sql->getRows()) {
                $useradmin = $sql->getValue('admin');
                $userstatus = $sql->getValue(rex::getTablePrefix() . 'user.status');
                $userrole = $sql->getValue(rex::getTablePrefix() . 'user.role');
                if ('' == $userrole) {
                    $userrole = [];
                } else {
                    $userrole = explode(',', $userrole);
                }
                $userperm_be_sprache = $sql->getValue('language');
                $userperm_startpage = $sql->getValue('startpage');
                $userpsw = $sql->getValue(rex::getTablePrefix() . 'user.password');
                $username = $sql->getValue(rex::getTablePrefix() . 'user.name');
                $userdesc = $sql->getValue(rex::getTablePrefix() . 'user.description');
                $useremail = $sql->getValue(rex::getTablePrefix() . 'user.email');
            }
        }

        if ($useradmin) {
            $adminchecked = 'checked="checked"';
        } else {
            $adminchecked = '';
        }

        if (1 == $userstatus) {
            $statuschecked = 'checked="checked"';
        } else {
            $statuschecked = '';
        }

        $sel_role->setSelected($userrole);
        $sel_be_sprache->setSelected($userperm_be_sprache);
        $sel_startpage->setSelected($userperm_startpage);

        $self = rex::getUser()->getValue('login') == $sql->getValue(rex::getTablePrefix() . 'user.login');

        if (rex::getUser()->isAdmin()) {
            $disabled = $self ? ' disabled="disabled"' : '';
            $add_admin_chkbox = '<input type="checkbox" id="rex-js-user-admin" name="useradmin" value="1" ' . $adminchecked . $disabled . ' />';
        } else {
            $add_admin_chkbox = '';
        }

        // Der Benutzer kann sich selbst den Status nicht entziehen
        if ($self && '' != $statuschecked) {
            $add_status_chkbox = '<input type="hidden" name="userstatus" value="1" /><input type="checkbox" id="rex-user-status" name="userstatus" value="1" ' . $statuschecked . ' disabled="disabled" />';
        } else {
            $add_status_chkbox = '<input type="checkbox" id="rex-user-status" name="userstatus" value="1" ' . $statuschecked . ' />';
        }
    } else {
        // User Add
        $form_label = rex_i18n::msg('create_user');
        $add_hidden = '<input type="hidden" name="FUNC_ADD" value="1" />';
        if (rex::getUser()->isAdmin()) {
            $add_admin_chkbox = '<input type="checkbox" id="rex-js-user-admin" name="useradmin" value="1" ' . $adminchecked . ' />';
        } else {
            $add_admin_chkbox = '';
        }
        $add_status_chkbox = '<input type="checkbox" id="rex-user-status" name="userstatus" value="1" ' . $statuschecked . ' />';
        $add_user_login = '<input class="form-control" type="text" id="rex-user-login" name="userlogin" value="' . rex_escape($userlogin) . '" autofocus />';

        $formElements = [];

        $n = [];
        $n['field'] = '<button class="btn btn-save" type="submit" name="function" value="1" ' . rex::getAccesskey(rex_i18n::msg('add_user'), 'save') . '>' . rex_i18n::msg('add_user') . '</button>';
        $formElements[] = $n;

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');
        unset($formElements);
    }

    $content .= '
            <fieldset>
                <input type="hidden" name="subpage" value="" />
                <input type="hidden" name="save" value="1" />
                ' . $add_hidden;

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-user-login">' . rex_i18n::msg('login_name') . '</label>';
    $n['field'] = $add_user_login;
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-js-user-password">' . rex_i18n::msg('password') . '</label>';
    $n['field'] = '<input class="form-control" type="password" id="rex-js-user-password" name="userpsw" autocomplete="new-password"/>';

    if ('' != rex::getProperty('pswfunc')) {
        $n['note'] = rex_i18n::msg('psw_encrypted');
    }

    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-user-name">' . rex_i18n::msg('name') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-user-name" name="username" value="' . rex_escape($username) . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-user-description">' . rex_i18n::msg('description') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-user-description" name="userdesc" value="' . rex_escape($userdesc) . '" />';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-user-email">' . rex_i18n::msg('email') . '</label>';
    $n['field'] = '<input class="form-control" type="email" placeholder="name@domain.tld" id="rex-user-email" name="useremail" value="' . rex_escape($useremail) . '" />';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('flush', true);
    $fragment->setVar('group', true);
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');

    $formElements = [];

    if ($add_admin_chkbox) {
        $n = [];
        $n['label'] = '<label for="rex-js-user-admin">' . rex_i18n::msg('user_admin') . '</label>';
        $n['field'] = $add_admin_chkbox;
        $n['note'] = rex_i18n::msg('user_admin_note');
        $formElements[] = $n;
    }

    $n = [];
    $n['label'] = '<label for="rex-user-status">' . rex_i18n::msg('user_status') . '</label>';
    $n['field'] = $add_status_chkbox;
    $formElements[] = $n;

    if ($user_id > 0 && $user->getValue('login_tries') > 0) {
        $n = [];
        $n['label'] = '<label for="rex-user-logintriesreset">' . rex_i18n::msg('user_reset_tries', $user->getValue('login_tries')) . '</label>';
        $n['field'] = '<input type="checkbox" id="rex-user-logintriesreset" name="logintriesreset" value="1" />';
        $formElements[] = $n;
    }

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/checkbox.php');

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="rex-js-user-role">' . rex_i18n::msg('user_role') . '</label>';
    $n['field'] = $sel_role->get();
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-user-perm-startpage">' . rex_i18n::msg('startpage') . '</label>';
    $n['field'] = $sel_startpage->get();
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="rex-user-perm-mylang">' . rex_i18n::msg('backend_language') . '</label>';
    $n['field'] = $sel_be_sprache->get();
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('group', true);
    $fragment->setVar('flush', true);
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');

    $content .= '</fieldset>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', $form_label);
    $fragment->setVar('body', $content, false);
    $fragment->setVar('buttons', $buttons, false);
    $content = $fragment->parse('core/page/section.php');

    $content = '
        <form id="rex-form-user" action="' . rex_url::currentBackendPage() . '" method="post">
            ' . rex_csrf_token::factory('user_edit')->getHiddenField() . '
            ' . $content . '
        </form>

        <script type="text/javascript">
        <!--
        jQuery(function($) {
            $("#rex-js-user-admin").change(function() {
                 if ($(this).is(":checked"))
                     $("#rex-js-user-role").prop("disabled", true);
                 else
                     $("#rex-js-user-role").prop("disabled", false);
                 $("#rex-js-user-role").selectpicker("refresh");
            }).change();
        });
        //-->
        </script>';

    echo $message;
    echo $content;
}

// ---------------------------------- Userliste

if ($SHOW) {
    // use string starting with "_" to have users without role at bottom when sorting by role ASC
    $noRole = '_no_role';

    $list = rex_list::factory('
        SELECT
            id,
            IF(name <> "", name, login) as name,
            login,
            `admin`,
            IF(`admin`, "Admin", IFNULL((SELECT GROUP_CONCAT(name ORDER BY name SEPARATOR ",") FROM '.rex::getTable('user_role').' r WHERE FIND_IN_SET(r.id, u.role)), "'.$noRole.'")) as role,
            status,
            lastlogin
        FROM ' . rex::getTable('user') . ' u
        ORDER BY name
    ');
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-user"></i>';
    $thIcon = '<a href="' . $list->getUrl(['FUNC_ADD' => '1']) . '"' . rex::getAccesskey(rex_i18n::msg('create_user'), 'add') . ' title="' . rex_i18n::msg('create_user') . '"><i class="rex-icon rex-icon-add-user"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['user_id' => '###id###']);
    $list->setColumnFormat($thIcon, 'custom', static function ($params) use ($thIcon, $tdIcon) {
        $list = $params['list'];
        $tdIcon = !$list->getValue('status') ? str_replace('rex-icon-user', 'rex-icon-user text-muted', $tdIcon) : $tdIcon;
        return !$list->getValue('admin') || rex::getUser()->isAdmin() ? $list->getColumnLink($thIcon, $tdIcon) : $tdIcon;
    });

    $list->removeColumn('admin');
    $list->removeColumn('status');

    $list->setColumnLabel('id', 'Id');
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);
    $list->setColumnSortable('id');

    $list->setColumnLabel('name', rex_i18n::msg('name'));
    $list->setColumnParams('name', ['user_id' => '###id###']);
    $list->setColumnFormat('name', 'custom', static function ($params) {
        $list = $params['list'];
        $name = rex_escape($list->getValue('name'));
        return !$list->getValue('admin') || rex::getUser()->isAdmin() ? $list->getColumnLink('name', $name) : $name;
    });
    $list->setColumnSortable('name');

    $list->setColumnLabel('login', rex_i18n::msg('login'));
    $list->setColumnFormat('login', 'custom', static function ($params) {
        $list = $params['list'];

        $login = rex_escape($list->getValue('login'));
        if (!$list->getValue('status')) {
            $login = '<span class="text-muted">' . $login . '</span>';
        }
        return $login;
    });
    $list->setColumnSortable('login');

    $list->setColumnLabel('role', rex_i18n::msg('user_role'));
    $list->setColumnFormat('role', 'custom', static function ($params) use ($noRole) {
        $list = $params['list'];
        $roles = $list->getValue('role');
        if ($noRole === $roles) {
            return rex_i18n::msg('user_no_role');
        }

        return implode('<br />', explode(',', rex_escape($roles)));
    }, ['roles' => $roles]);
    $list->setColumnSortable('role');

    $list->setColumnLabel('lastlogin', rex_i18n::msg('last_login'));
    $list->setColumnFormat('lastlogin', 'custom', static function () use ($list) {
        return rex_formatter::strftime(strtotime($list->getValue('lastlogin')), 'datetime');
    });
    $list->setColumnSortable('lastlogin', 'desc');

    $colspan = rex::getUser()->isAdmin() ? 3 : 2;
    $list->addColumn(rex_i18n::msg('user_functions'), '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit'));
    $list->setColumnLayout(rex_i18n::msg('user_functions'), ['<th class="rex-table-action" colspan="'.$colspan.'">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams(rex_i18n::msg('user_functions'), ['user_id' => '###id###']);
    $list->setColumnFormat(rex_i18n::msg('user_functions'), 'custom', static function ($params) {
        $list = $params['list'];
        $edit = '<i class="rex-icon rex-icon-edit"></i> ' . rex_i18n::msg('edit');
        return !$list->getValue('admin') || rex::getUser()->isAdmin() ? $list->getColumnLink(rex_i18n::msg('user_functions'), $edit) : $edit;
    });

    $list->addColumn('funcs', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
    $list->setColumnLayout('funcs', ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('funcs', ['FUNC_DELETE' => '1', 'user_id' => '###id###'] + rex_csrf_token::factory('user_delete')->getUrlParams());
    $list->setColumnFormat('funcs', 'custom', static function ($params) {
        $list = $params['list'];
        if ($list->getValue('id') == rex::getUser()->getId() || $list->getValue('admin') && !rex::getUser()->isAdmin()) {
            return '<span class="rex-text-disabled"><i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('user_delete') . '</span>';
        }
        return $list->getColumnLink('funcs', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('user_delete'));
    });
    $list->addLinkAttribute('funcs', 'data-confirm', rex_i18n::msg('delete') . ' ?');

    if (rex::getUser()->isAdmin()) {
        $list->addColumn('impersonate', '<i class="rex-icon rex-icon-delete"></i> ' . rex_i18n::msg('delete'));
        $list->setColumnLayout('impersonate', ['', '<td class="rex-table-action">###VALUE###</td>']);
        $list->setColumnFormat('impersonate', 'custom', static function ($params) use ($list) {
            if (rex::getImpersonator() || $list->getValue('id') == rex::getUser()->getId()) {
                return '<span class="rex-text-disabled"><i class="rex-icon rex-icon-sign-in"></i> ' . rex_i18n::msg('login_impersonate') . '</span>';
            }

            $url = rex_url::currentBackendPage(['_impersonate' => $list->getValue('id')] + rex_api_user_impersonate::getUrlParams());
            return sprintf('<a href="%s" data-pjax="false"><i class="rex-icon rex-icon-sign-in"></i> %s</a>', $url, rex_i18n::msg('login_impersonate'));
        });
    }

    $content .= $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('user_caption'));
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $message;
    echo $content;
}
