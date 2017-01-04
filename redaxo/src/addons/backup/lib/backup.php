<?php

/**
 * @package redaxo\backup
 */
class rex_backup
{
    const IMPORT_ARCHIVE = 1;
    const IMPORT_DB = 2;
    const IMPORT_EVENT_PRE = 3;
    const IMPORT_EVENT_POST = 4;

    public static function getDir()
    {
        $dir = rex_path::addonData('backup');
        rex_dir::create($dir);

        return $dir;
    }

    public static function getBackupFiles($filePrefix)
    {
        $dir = self::getDir();

        $folder = rex_finder::factory($dir)->filesOnly();

        $filtered = [];
        foreach ($folder as $file) {
            $file = $file->getFilename();
            if (substr($file, strlen($file) - strlen($filePrefix)) == $filePrefix) {
                $filtered[] = $file;
            }
        }
        $folder = $filtered;

        usort($folder, function ($file_a, $file_b) use ($dir) {
            $time_a = filemtime($dir . '/' . $file_a);
            $time_b = filemtime($dir . '/' . $file_b);

            if ($time_a == $time_b) {
                return 0;
            }

            return ($time_a > $time_b) ? -1 : 1;
        });

        return $folder;
    }

    /**
     * Importiert den SQL Dump $filename in die Datenbank.
     *
     * @param string $filename Pfad + Dateinamen zur SQL-Datei
     *
     * @return array Gibt ein Assoc. Array zurück.
     *               'state' => boolean (Status ob fehler aufgetreten sind)
     *               'message' => Evtl. Status/Fehlermeldung
     */
    public static function importDb($filename)
    {
        $return = [];
        $return['state'] = false;
        $return['message'] = '';

        $msg = '';
        $error = '';

        if ($filename == '' || substr($filename, -4, 4) != '.sql') {
            $return['message'] = rex_i18n::msg('backup_no_import_file_chosen_or_wrong_version') . '<br>';
            return $return;
        }

        $conts = rex_file::get($filename);

        // Versionsstempel prüfen
        // ## Redaxo Database Dump Version x.x
        $mainVersion = rex::getVersion('%s');
        $version = strpos($conts, '## Redaxo Database Dump Version ' . $mainVersion);
        if ($version === false) {
            $return['message'] = rex_i18n::msg('backup_no_valid_import_file') . '. [## Redaxo Database Dump Version ' . $mainVersion . '] is missing';
            return $return;
        }
        // Versionsstempel entfernen
        $conts = trim(str_replace('## Redaxo Database Dump Version ' . $mainVersion, '', $conts));

        // Prefix prüfen
        // ## Prefix xxx_
        if (preg_match('/^## Prefix ([a-zA-Z0-9\_]*)/', $conts, $matches) && isset($matches[1])) {
            // prefix entfernen
            $prefix = $matches[1];
            $conts = trim(str_replace('## Prefix ' . $prefix, '', $conts));
        } else {
            // Prefix wurde nicht gefunden
            $return['message'] = rex_i18n::msg('backup_no_valid_import_file') . '. [## Prefix ' . rex::getTablePrefix() . '] is missing';
            return $return;
        }

        // Charset prüfen
        // ## charset xxx_
        if (preg_match('/^## charset ([a-zA-Z0-9\_\-]*)/', $conts, $matches) && isset($matches[1])) {
            // charset entfernen
            $charset = $matches[1];
            $conts = trim(str_replace('## charset ' . $charset, '', $conts));

            // $rexCharset = rex_i18n::msg('htmlcharset');
            $rexCharset = 'utf-8';
            if ($rexCharset != $charset) {
                $return['message'] = rex_i18n::msg('backup_no_valid_charset') . '. ' . $rexCharset . ' != ' . $charset;
                return $return;
            }
        }

        // Prefix im export mit dem der installation angleichen
        if (rex::getTablePrefix() != $prefix) {
            // Hier case-insensitiv ersetzen, damit alle möglich Schreibweisen (TABLE TablE, tAblE,..) ersetzt werden
            // Dies ist wichtig, da auch SQLs innerhalb von Ein/Ausgabe der Module vom rex-admin verwendet werden
            $conts = preg_replace('/(TABLES? `?)' . preg_quote($prefix, '/') . '/i', '$1' . rex::getTablePrefix(), $conts);
            $conts = preg_replace('/(INTO `?)'  . preg_quote($prefix, '/') . '/i', '$1' . rex::getTablePrefix(), $conts);
            $conts = preg_replace('/(EXISTS `?)' . preg_quote($prefix, '/') . '/i', '$1' . rex::getTablePrefix(), $conts);
        }

        // ----- EXTENSION POINT
        $filesize = filesize($filename);
        $msg = rex_extension::registerPoint(new rex_extension_point('BACKUP_BEFORE_DB_IMPORT', $msg, [
            'content' => $conts,
            'filename' => $filename,
            'filesize' => $filesize,
        ]));

        // require import skript to do some userside-magic
        self::importScript(str_replace('.sql', '.php', $filename), self::IMPORT_DB, self::IMPORT_EVENT_PRE);

        // Datei aufteilen
        $lines = [];
        rex_sql_util::splitSqlFile($lines, $conts, 0);

        $sql = rex_sql::factory();
        foreach ($lines as $line) {
            try {
                $sql->setQuery($line['query']);
            } catch (rex_sql_exception $e) {
                $error .= "\n" . $e->getMessage();
            }
        }

        if ($error != '') {
            $return['message'] = trim($error);
            return $return;
        }

        $msg .= rex_i18n::msg('backup_database_imported') . '. ' . rex_i18n::msg('backup_entry_count', count($lines)) . '<br />';
        unset($lines);

        // prüfen, ob eine user tabelle angelegt wurde
        $tables = rex_sql::showTables();
        $user_table_found = in_array(rex::getTablePrefix() . 'user', $tables);

        if (!$user_table_found) {
            $create_user_table = '
             CREATE TABLE ' . rex::getTablePrefix() . 'user
             (
                 id int(11) NOT NULL auto_increment,
                 name varchar(255) NOT NULL,
                 description text NOT NULL,
                 login varchar(50) NOT NULL,
                 psw varchar(50) NOT NULL,
                 status varchar(5) NOT NULL,
                 role int(11) NOT NULL,
                 rights text NOT NULL,
                 login_tries tinyint(4) NOT NULL DEFAULT 0,
                 createuser varchar(255) NOT NULL,
                 updateuser varchar(255) NOT NULL,
                 createdate datetime NOT NULL,
                 updatedate datetime NOT NULL,
                 lasttrydate datetime NOT NULL,
                 session_id varchar(255) NOT NULL,
                 PRIMARY KEY(id)
             ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
            $db = rex_sql::factory();
            try {
                $db->setQuery($create_user_table);
            } catch (rex_sql_exception $e) {
                // evtl vorhergehende meldungen löschen, damit nur der fehler angezeigt wird
                $msg = '';
                $msg .= $e->getMessage();
            }
        }

        $user_role_table_found = in_array(rex::getTablePrefix() . 'user_role', $tables);
        if (!$user_role_table_found) {
            $create_user_role_table = '
             CREATE TABLE ' . rex::getTablePrefix() . 'user_role
             (
                 id int(11) NOT NULL auto_increment,
                 name varchar(255) NOT NULL,
                 description text NOT NULL,
                 rights text NOT NULL,
                 createuser varchar(255) NOT NULL,
                 updateuser varchar(255) NOT NULL,
                 createdate datetime NOT NULL DEFAULT 0,
                 updatedate datetime NOT NULL DEFAULT 0
                 PRIMARY KEY(id)
             ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;';
            $db = rex_sql::factory();
            try {
                $db->setQuery($create_user_role_table);
            } catch (rex_sql_exception $e) {
                // evtl vorhergehende meldungen löschen, damit nur der fehler angezeigt wird
                $msg = '';
                $msg .= $e->getMessage();
            }
        }

        // generated neu erstellen, wenn kein Fehler aufgetreten ist
        if ($error == '') {
            // ----- EXTENSION POINT
            $msg = rex_extension::registerPoint(new rex_extension_point('BACKUP_AFTER_DB_IMPORT', $msg, [
                'content' => $conts,
                'filename' => $filename,
                'filesize' => $filesize,
            ]));

            // require import skript to do some userside-magic
            self::importScript(str_replace('.sql', '.php', $filename), self::IMPORT_DB, self::IMPORT_EVENT_POST);

            $msg .= rex_delete_cache();
            $return['state'] = true;
        }

        $return['message'] = $msg;

        return $return;
    }

    /**
     * Importiert das Tar-Archiv $filename in den Ordner /files.
     *
     * @param string $filename Pfad + Dateinamen zum Tar-Archiv
     *
     * @return array Gibt ein Assoc. Array zurück.
     *               'state' => boolean (Status ob fehler aufgetreten sind)
     *               'message' => Evtl. Status/Fehlermeldung
     */
    public static function importFiles($filename)
    {
        $return = [];
        $return['state'] = false;

        if ($filename == '' || substr($filename, -7, 7) != '.tar.gz') {
            $return['message'] = rex_i18n::msg('backup_no_import_file_chosen') . '<br />';
            return $return;
        }

        // Ordner /files komplett leeren
        rex_dir::deleteFiles(rex_path::media());

        $tar = new rex_backup_tar();

        // ----- EXTENSION POINT
        $tar = rex_extension::registerPoint(new rex_extension_point('BACKUP_BEFORE_FILE_IMPORT', $tar));

        // require import skript to do some userside-magic
        self::importScript(str_replace('.tar.gz', '.php', $filename), self::IMPORT_ARCHIVE, self::IMPORT_EVENT_PRE);

        $tar->openTAR($filename);
        if (!$tar->extractTar()) {
            $msg = rex_i18n::msg('backup_problem_when_extracting') . '<br />';
            if (count($tar->getMessages()) > 0) {
                $msg .= rex_i18n::msg('backup_create_dirs_manually') . '<br />';
                foreach ($tar->getMessages() as $_message) {
                    $msg .= rex_path::absolute($_message) . '<br />';
                }
            }
        } else {
            $msg = rex_i18n::msg('backup_file_imported') . '<br />';
        }

        // ----- EXTENSION POINT
        $tar = rex_extension::registerPoint(new rex_extension_point('BACKUP_AFTER_FILE_IMPORT', $tar));

        // require import skript to do some userside-magic
        self::importScript(str_replace('.tar.gz', '.php', $filename), self::IMPORT_ARCHIVE, self::IMPORT_EVENT_POST);

        $return['state'] = true;
        $return['message'] = $msg;
        return $return;
    }

    /**
     * Erstellt einen SQL Dump, der die aktuellen Datebankstruktur darstellt.
     * Dieser wird in der Datei $filename gespeichert.
     *
     * @param string $filename
     * @param array  $tables
     *
     * @return bool TRUE wenn ein Dump erstellt wurde, sonst FALSE
     */
    public static function exportDb($filename, array $tables = null)
    {
        $fp = @fopen($filename, 'w');

        if (!$fp) {
            return false;
        }

        $sql = rex_sql::factory();

        $nl = "\n";
        $insertSize = 5000;

        // ----- EXTENSION POINT
        rex_extension::registerPoint(new rex_extension_point('BACKUP_BEFORE_DB_EXPORT'));

        // Versionsstempel hinzufügen
        fwrite($fp, '## Redaxo Database Dump Version ' . rex::getVersion('%s') . $nl);
        fwrite($fp, '## Prefix ' . rex::getTablePrefix() . $nl);
        //fwrite($fp, '## charset '.rex_i18n::msg('htmlcharset').$nl.$nl);
        fwrite($fp, '## charset utf-8' . $nl . $nl);
    //  fwrite($fp, '/*!40110 START TRANSACTION; */'.$nl);

        if (is_null($tables)) {
            $tables = [];
            foreach (rex_sql::showTables(1, rex::getTablePrefix()) as $table) {
                if ($table != rex::getTable('user') // User Tabelle nicht exportieren
                    && substr($table, 0, strlen(rex::getTablePrefix() . rex::getTempPrefix())) != rex::getTablePrefix() . rex::getTempPrefix()
                ) { // Tabellen die mit rex_tmp_ beginnne, werden nicht exportiert!
                    $tables[] = $table;
                }
            }
        }
        foreach ($tables as $table) {
            if (!preg_match('/^[a-z0-9._-]+$/i', $table)) {
                throw new Exception('tablename "'. $table .'" contains invalid characters!');
            }

            //---- export metadata
            $create = rex_sql::showCreateTable($table);

            fwrite($fp, 'DROP TABLE IF EXISTS `' . $table . '`;' . $nl);
            fwrite($fp, $create . ';' . $nl);

            $fields = $sql->getArray('SHOW FIELDS FROM `' . $table . '`');

            foreach ($fields as &$field) {
                if (preg_match('#^(bigint|int|smallint|mediumint|tinyint|timestamp)#i', $field['Type'])) {
                    $field = 'int';
                } elseif (preg_match('#^(float|double|decimal)#', $field['Type'])) {
                    $field = 'double';
                } elseif (preg_match('#^(char|varchar|text|longtext|mediumtext|tinytext)#', $field['Type'])) {
                    $field = 'string';
                }
                // else ?
            }

            //---- export tabledata
            $start = 0;
            $max = $insertSize;

            do {
                $array = $sql->getArray('SELECT * FROM `' . $table . '` LIMIT ' . $start . ',' . $max, [], PDO::FETCH_NUM);
                $count = $sql->getRows();

                if ($count > 0 && $start == 0) {
                    fwrite($fp, $nl . 'LOCK TABLES `' . $table . '` WRITE;');
                    fwrite($fp, $nl . '/*!40000 ALTER TABLE `' . $table . '` DISABLE KEYS */;');
                } elseif ($count == 0) {
                    break;
                }

                $start += $max;
                $values = [];

                foreach ($array as $row) {
                    $record = [];

                    foreach ($fields as $idx => $type) {
                        $column = $row[$idx];

                        switch ($type) {
                            case 'int':
                                $record[] = intval($column);
                                break;
                            case 'double':
                                $record[] = sprintf('%.10F', (float) $column);
                                break;
                            case 'string':
                            default:
                                $record[] = $sql->escape($column, "'");
                                break;
                        }
                    }

                    $values[] = $nl . '  (' . implode(',', $record) . ')';
                }

                if (!empty($values)) {
                    fwrite($fp, $nl . 'INSERT INTO `' . $table . '` VALUES ' . implode(',', $values) . ';');
                    unset($values);
                }
            } while ($count >= $max);

            if ($start > 0) {
                fwrite($fp, $nl . '/*!40000 ALTER TABLE `' . $table . '` ENABLE KEYS */;');
                fwrite($fp, $nl . 'UNLOCK TABLES;' . $nl . $nl);
            }
        }

        fclose($fp);

        $hasContent = true;

        // Den Dateiinhalt geben wir nur dann weiter, wenn es unbedingt notwendig ist.
        if (rex_extension::isRegistered('BACKUP_AFTER_DB_EXPORT')) {
            $content = rex_file::get($filename);
            $hashBefore = md5($content);
            // ----- EXTENSION POINT
            $content = rex_extension::registerPoint(new rex_extension_point('BACKUP_AFTER_DB_EXPORT', $content));
            $hashAfter = md5($content);

            if ($hashAfter != $hashBefore) {
                rex_file::put($filename, $content);
                $hasContent = !empty($content);
                unset($content);
            }
        }

        return $hasContent;
    }

    /**
     * Exportiert alle Ordner $folders aus dem Verzeichnis /files.
     *
     * @param array $folders Array von Ordnernamen, die exportiert werden sollen
     *
     * @return string Inhalt des Tar-Archives als String
     */
    public static function exportFiles($folders)
    {
        $tar = new rex_backup_tar();

        // ----- EXTENSION POINT
        $tar = rex_extension::registerPoint(new rex_extension_point('BACKUP_BEFORE_FILE_EXPORT', $tar));

        foreach ($folders as $item) {
            self::addFolderToTar($tar, rex_url::frontend(), $item);
        }

        // ----- EXTENSION POINT
        $tar = rex_extension::registerPoint(new rex_extension_point('BACKUP_AFTER_FILE_EXPORT', $tar));

        return $tar->toTar(null, true);
    }

    /**
     * Fügt einem Tar-Archiv ein Ordner von Dateien hinzu.
     */
    private static function addFolderToTar(rex_backup_tar $tar, $path, $dir)
    {
        $handle = opendir($path . $dir);
        $isMediafolder = realpath($path . $dir) . '/' == rex_path::media();
        while (false !== ($file = readdir($handle))) {
            // Alles exportieren, außer ...
            // - addons verzeichnis im mediafolder (wird bei addoninstallation wiedererstellt)
            // - svn infos
            // - tmp prefix Dateien

            if ($file == '.' || $file == '..' || $file == '.svn') {
                continue;
            }

            if (substr($file, 0, strlen(rex::getTempPrefix())) == rex::getTempPrefix()) {
                continue;
            }

            if ($isMediafolder && $file == 'addons') {
                continue;
            }

            if (is_dir($path . $dir . '/' . $file)) {
                self::addFolderToTar($tar, $path . $dir . '/', $file);
            } else {
                $tar->addFile($path . $dir . '/' . $file);
            }
        }
        closedir($handle);
    }

    private static function importScript($filename, $importType, $eventType)
    {
        if (file_exists($filename)) {
            require $filename;
        }
    }
}
