<?php

$addon = rex_addon::get('structure');

if (rex_addon::get('users')->isInstalled() && rex_string::versionCompare($addon->getVersion(), '2.9.0-beta1', '<')) {
    $sql = rex_sql::factory();
    $sql->transactional(static function () use ($sql) {
        $roles = rex_sql::factory()->setQuery('SELECT * FROM '.rex::getTable('user_role'));
        /** @var rex_sql $role */
        foreach ($roles as $role) {
            $perms = $role->getArrayValue('perms');
            $perms['options'] = $perms['options'] ?? '|';
            $perms['options'] .= 'addArticle[]|addCategory[]|editArticle[]|editCategory[]|deleteArticle[]|deleteCategory[]|';

            $sql
                ->setTable(rex::getTable('user_role'))
                ->setWhere(['id' => $role->getValue('id')])
                ->setArrayValue('perms', $perms)
                ->update();
        }
    });
}

if (rex_addon::get('users')->isInstalled() && $addon->getPlugin('content')->isInstalled() && rex_string::versionCompare($addon->getVersion(), '2.11.0-beta1', '<')) {
    $sql = rex_sql::factory();
    $sql->transactional(static function () use ($sql) {
        $roles = rex_sql::factory()->setQuery('SELECT * FROM '.rex::getTable('user_role'));
        /** @var rex_sql $role */
        foreach ($roles as $role) {
            $perms = $role->getArrayValue('perms');
            $perms['options'] = $perms['options'] ?? '|';
            $perms['options'] .= 'publishSlice[]|';

            $sql
                ->setTable(rex::getTable('user_role'))
                ->setWhere(['id' => $role->getValue('id')])
                ->setArrayValue('perms', $perms)
                ->update();
        }
    });
}

// use path relative to __DIR__ to get correct path in update temp dir
$addon->includeFile(__DIR__.'/install.php');

foreach ($addon->getInstalledPlugins() as $plugin) {
    $file = __DIR__.'/plugins/'.$plugin->getName().'/install.php';

    if (is_file($file)) {
        $plugin->includeFile($file);
    }
}
