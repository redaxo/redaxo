<?php

use Redaxo\Core\Backend\Accesskey;
use Redaxo\Core\Backend\Form\Role;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Form\Form;
use Redaxo\Core\Form\Storage\DbStorage;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Http\Response;
use Redaxo\Core\Security\CsrfToken;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\DataList;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\Message;

$func = Request::request('func', 'string');
$id = Request::request('id', 'int');

// the form submits to the current url, so the message of the previous save must not appear again
$message = 'get' !== Request::requestMethod() ? '' : match (Request::get('saved', 'string')) {
    'added' => Message::info(I18n::msg('user_role_added')),
    'updated' => Message::info(I18n::msg('user_role_data_updated')),
    default => '',
};
$content = '';

if ('delete' == $func) {
    if (!CsrfToken::factory('user_role_delete')->isValid()) {
        $message = Message::error(I18n::msg('csrf_token_invalid'));
    } else {
        $sql = Sql::factory();
        $sql->setQuery('DELETE FROM rex_user_role WHERE id = ? LIMIT 1', [$id]);
        $message = Message::info(I18n::msg('user_role_deleted'));
    }

    $func = '';
}

if ('' == $func) {
    $title = I18n::msg('user_role_caption');

    $list = DataList::factory('SELECT id, name FROM rex_user_role ORDER BY name');
    $list->addTableAttribute('class', 'table-striped table-hover');

    $tdIcon = '<i class="rex-icon rex-icon-userrole"></i>';
    $thIcon = '<a class="rex-link-expanded" href="' . $list->getUrl(['func' => 'add']) . '"' . Accesskey::attributes(I18n::msg('create_user_role'), 'add') . '><i class="rex-icon rex-icon-add-userrole"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->setColumnLabel('id', I18n::msg('id'));
    $list->setColumnLayout('id', ['<th class="rex-table-id">###VALUE###</th>', '<td class="rex-table-id">###VALUE###</td>']);

    $list->setColumnLabel('name', I18n::msg('name'));
    $list->setColumnParams('name', ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn('edit', '<i class="rex-icon rex-icon-edit"></i> ' . I18n::msg('edit'));
    $list->setColumnLabel('edit', I18n::msg('user_functions'));
    $list->setColumnLayout('edit', ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('edit', ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn('duplicate', '<i class="rex-icon rex-icon-duplicate"></i> ' . I18n::msg('user_role_duplicate'));
    $list->setColumnLabel('duplicate', I18n::msg('user_functions'));
    $list->setColumnLayout('duplicate', ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('duplicate', ['func' => 'duplicate', 'id' => '###id###']);

    $list->addColumn('funcs', '<i class="rex-icon rex-icon-delete"></i> ' . I18n::msg('user_role_delete'));
    $list->setColumnLabel('funcs', I18n::msg('user_functions'));
    $list->setColumnLayout('funcs', ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('funcs', ['func' => 'delete', 'id' => '###id###'] + CsrfToken::factory('user_role_delete')->getUrlParams());
    $list->addLinkAttribute('funcs', 'data-confirm', I18n::msg('delete') . ' ?');

    $content .= $list->get();

    $fragment = new Fragment();
    $fragment->setVar('title', $title);
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
} else {
    $role = new Role();
    $source = new DbStorage('rex_user_role', $id, globalFields: true);

    if ('add' === $func) {
        $role->grantAllItems();
    } elseif (!$source->load($role)) {
        Response::sendRedirect(Url::currentBackendPage());
    }

    // a duplicate is saved as new role
    $storage = 'edit' === $func ? $source : new DbStorage('rex_user_role', globalFields: true);

    $form = new Form(
        $role,
        title: new Translatable('edit' === $func ? 'edit_user_role' : 'add_user_role'),
        applyLabel: new Translatable('form_apply'),
        cancelUrl: Url::currentBackendPage(),
    );

    if ($form->handleRequest()) {
        $storage->save($role);

        $saved = 'edit' === $func ? 'updated' : 'added';
        Response::sendRedirect(Url::currentBackendPage($form->isApplied() ? ['func' => 'edit', 'id' => $storage->id, 'saved' => $saved] : ['saved' => $saved]));
    }

    $content = $form->render();
}

echo $message;
echo $content;
