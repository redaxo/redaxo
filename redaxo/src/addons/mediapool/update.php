<?php

$addon = rex_addon::get('mediapool');

if (rex_addon::get('users')->isInstalled() && rex_string::versionCompare($addon->getVersion(), '2.9.0-beta1', '<')) {
    $sql = rex_sql::factory();
    $sql->transactional(static function () use ($sql) {
        $roles = rex_sql::factory()->setQuery('SELECT * FROM ' . rex::getTable('user_role'));
        foreach ($roles as $role) {
            $perms = $role->getArrayValue('perms');

            if (!in_array('media_read', $perms)) {
                $perms['media_read'] = rex_complex_perm::ALL;
            }

            if (rex_complex_perm::ALL === $perms['media']) {
                $perms['general'] = $perms['general'] ?? '|';
                $perms['general'] .= 'media[categories]|';
                $perms['general'] .= 'media[sync]|';
            }

            if ($perms !== $role->getArrayValue('perms')) {
                $sql
                    ->setTable(rex::getTable('user_role'))
                    ->setWhere(['id' => $role->getValue('id')])
                    ->setArrayValue('perms', $perms)
                    ->update();
            }
        }
    });
}

$addon->includeFile(__DIR__ . '/install.php');
