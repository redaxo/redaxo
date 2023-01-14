<?php

$addon = rex_addon::get('cronjob');

if (rex_string::versionCompare($addon->getVersion(), '2.1-dev', '<')) {
    $table = rex::getTable('cronjob');

    rex_sql_table::get($table)
        ->ensureColumn(new rex_sql_column('interval', 'text'))
        ->ensureColumn(new rex_sql_column('nexttime', 'datetime', true))
        ->alter();

    $sql = rex_sql::factory();
    $sql->setQuery('UPDATE '.$table.' SET environment = REPLACE(REPLACE(environment, "|0|", "|frontend|"), "|1|", "|backend|")');

    $jobs = $sql->getArray('SELECT id, `interval` FROM '.$table);
    foreach ($jobs as $job) {
        $old = explode('|', trim((string) $job['interval'], '|'));
        $count = (int) $old[0];

        $interval = [
            'minutes' => [0],
            'hours' => [0],
            'days' => 'all',
            'weekdays' => 'all',
            'months' => 'all',
        ];

        switch ($old[1]) {
            case 'i':
                if ($count < 8) {
                    $interval['minutes'] = 'all';
                } elseif ($count < 33) {
                    $interval['minutes'] = range(0, 55, round($count / 5) * 5);
                }
                $interval['hours'] = 'all';
                break;
            case 'h':
                if (1 == $count) {
                    $interval['hours'] = 'all';
                } elseif ($count < 13) {
                    $interval['hours'] = range(0, 23, $count);
                }
                break;
            case 'd':
                if ($count > 15) {
                    $interval['days'] = [1];
                } elseif ($count > 1) {
                    $interval['days'] = range(1, 31, $count);
                }
                break;
            case 'w':
                if (1 == $count) {
                    $interval['weekdays'] = [1];
                    break;
                }
                if (2 == $count) {
                    $interval['days'] = [1, 15];
                    break;
                }
                $count = round($count / 4);
                // no break;
            case 'm':
                $interval['days'] = [1];
                if ($count > 6) {
                    $interval['months'] = [1];
                } elseif ($count > 1) {
                    $interval['months'] = range(1, 12, $count);
                }
                break;
            case 'y':
                $interval['days'] = [1];
                $interval['months'] = [1];
                break;
        }

        $sql
            ->setTable($table)
            ->setWhere(['id' => $job['id']])
            ->setArrayValue('interval', $interval)
            ->update();
    }
}

$addon->includeFile(__DIR__.'/install.php');

if (rex_string::versionCompare(rex::getVersion(), '2.6.0-beta1', '<')) {
    // do not use `rex_path::log()` because it does not exist while updating from rex < 5.9
    @rename($addon->getDataPath('cronjob.log'), rex_path::data('log/cronjob.log'));
    @rename($addon->getDataPath('cronjob.log.2'), rex_path::data('log/cronjob.log.2'));
    @rmdir($addon->getDataPath());
}
