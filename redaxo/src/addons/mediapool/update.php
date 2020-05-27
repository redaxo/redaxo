<?php

$addon = rex_addon::get('mediapool');

$addon->includeFile(__DIR__ . '/install.php');

if (rex_addon::get('users')->isInstalled() && rex_addon::get('mediapool')->isInstalled() && rex_string::versionCompare($addon->getVersion(), '2.4.3-beta1', '<')) {
    $sql = rex_sql::factory();
    $sql->transactional(static function () use ($sql) {
        $roles = rex_sql::factory()->setQuery('SELECT * FROM ' . rex::getTable('user_role'));
        /** @var rex_sql $role */
        foreach ($roles as $role) {
            $perms = $role->getArrayValue('perms');
            if ('all' == $perms['media']) {
                $perms['general'] = $perms['general'] ?? '|';
                $perms['general'] .= 'media[sync]|';
                $sql
                    ->setTable(rex::getTable('user_role'))
                    ->setWhere(['id' => $role->getValue('id')])
                    ->setArrayValue('perms', $perms)
                    ->update();
            }
        }
    });
}
