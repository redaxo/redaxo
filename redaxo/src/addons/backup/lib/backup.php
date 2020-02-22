<?php

/**
 * @package redaxo\backup
 */
class rex_backup
{
    public const IMPORT_ARCHIVE = 1;
    public const IMPORT_DB = 2;
    public const IMPORT_EVENT_PRE = 3;
    public const IMPORT_EVENT_POST = 4;

    /**
     * @return string
     */
    public static function getDir()
    {
        $dir = rex_path::addonData('backup');
        rex_dir::create($dir);

        return $dir;
    }

    /**
     * @return string[]
     */
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

        usort($folder, static function ($file_a, $file_b) {
            return $file_a <=> $file_b;
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

        if ('' == $filename || '.sql' != substr($filename, -4, 4)) {
            $return['message'] = rex_i18n::msg('backup_no_import_file_chosen_or_wrong_version') . '<br>';
            return $return;
        }

        $conts = rex_file::get($filename);

        // Versionsstempel prüfen
        // ## Redaxo Database Dump Version x.x
        $mainVersion = rex::getVersion('%s');
        $version = strpos($conts, '## Redaxo Database Dump Version ' . $mainVersion);
        if (false === $version) {
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

            if ('utf8mb4' === $charset && !rex::getConfig('utf8mb4') && !rex_setup_importer::supportsUtf8mb4()) {
                $sql = rex_sql::factory();
                $return['message'] = rex_i18n::msg('backup_utf8mb4_not_supported', $sql->getDbType().' '.$sql->getDbVersion());
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

        if ('' != $error) {
            $return['message'] = trim($error);
            return $return;
        }

        $msg .= rex_i18n::msg('backup_database_imported') . '. ' . rex_i18n::msg('backup_entry_count', (string) count($lines)) . '<br />';
        unset($lines);

        // prüfen, ob eine user tabelle angelegt wurde
        $tables = rex_sql::factory()->getTables(rex::getTablePrefix());
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
        if ('' == $error) {
            // delete cache before EP to avoid obsolete caches while running extensions
            rex_delete_cache();

            // refresh rex_config with new values from database
            rex_config::refresh();

            // ----- EXTENSION POINT
            $msg = rex_extension::registerPoint(new rex_extension_point('BACKUP_AFTER_DB_IMPORT', $msg, [
                'content' => $conts,
                'filename' => $filename,
                'filesize' => $filesize,
            ]));

            // require import skript to do some userside-magic
            self::importScript(str_replace('.sql', '.php', $filename), self::IMPORT_DB, self::IMPORT_EVENT_POST);

            // delete cache again because the extensions and the php script could have changed data again
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

        if ('' == $filename || '.tar.gz' != substr($filename, -7, 7)) {
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
        $fp = @tmpfile();
        $tempCacheFile = null;

        // in case of permission issues/misconfigured tmp-folders
        if (!$fp) {
            $tempCacheFile = rex_path::cache(basename($filename));
            $fp = fopen($tempCacheFile, 'w');
            if (!$fp) {
                return false;
            }
        }

        $sql = rex_sql::factory();

        $nl = "\n";
        $insertSize = 4000;

        // ----- EXTENSION POINT
        rex_extension::registerPoint(new rex_extension_point('BACKUP_BEFORE_DB_EXPORT'));

        // Versionsstempel hinzufügen
        fwrite($fp, '## Redaxo Database Dump Version ' . rex::getVersion('%s') . $nl);
        fwrite($fp, '## Prefix ' . rex::getTablePrefix() . $nl);
        fwrite($fp, '## charset '.(rex::getConfig('utf8mb4') ? 'utf8mb4' : 'utf8') . $nl . $nl);
        //  fwrite($fp, '/*!40110 START TRANSACTION; */'.$nl);

        fwrite($fp, 'SET FOREIGN_KEY_CHECKS = 0;' . $nl . $nl);

        if (null === $tables) {
            $tables = self::getTables();
        }
        foreach ($tables as $table) {
            //---- export metadata
            $create = rex_sql::showCreateTable($table);

            fwrite($fp, 'DROP TABLE IF EXISTS ' . $sql->escapeIdentifier($table) . ';' . $nl);
            fwrite($fp, $create . ';' . $nl);

            $fields = $sql->getArray('SHOW FIELDS FROM ' . $sql->escapeIdentifier($table));

            foreach ($fields as &$field) {
                if (preg_match('#^(bigint|int|smallint|mediumint|tinyint|timestamp)#i', $field['Type'])) {
                    $field = 'int';
                } elseif (preg_match('#^(float|double|decimal)#', $field['Type'])) {
                    $field = 'double';
                } elseif (preg_match('#^(char|varchar|text|longtext|mediumtext|tinytext)#', $field['Type'])) {
                    $field = 'string';
                } elseif (preg_match('#^(date|datetime|time|timestamp|year)#', $field['Type'])) {
                    // types which can be passed tru 1:1 as escaping isn't necessary, because we know the mysql internal format.
                    $field = 'raw';
                }
                // else ?
            }

            //---- export tabledata
            $start = 0;
            $max = $insertSize;

            self::exportTable($table, $start, $max, $fp, $nl, $fields);

            if ($start > 0) {
                fwrite($fp, $nl . '/*!40000 ALTER TABLE ' . $sql->escapeIdentifier($table) . ' ENABLE KEYS */;');
                fwrite($fp, $nl . 'UNLOCK TABLES;' . $nl . $nl);
            }
        }

        fwrite($fp, 'SET FOREIGN_KEY_CHECKS = 1;' . $nl);

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

        // Wenn das backup vollständig und erfolgreich erzeugt werden konnte, den Export 1:1 ans Ziel kopieren.
        if ($tempCacheFile) {
            fclose($fp);
            rename($tempCacheFile, $filename);
        } else {
            $destination = fopen($filename, 'w');
            rewind($fp);
            if (!$destination) {
                return false;
            }
            stream_copy_to_stream($fp, $destination);
            fclose($fp);
            fclose($destination);
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
     *
     * @param string $path
     * @param string $dir
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

            if ('.' == $file || '..' == $file || '.svn' == $file) {
                continue;
            }

            if (substr($file, 0, strlen(rex::getTempPrefix())) == rex::getTempPrefix()) {
                continue;
            }

            if ($isMediafolder && 'addons' == $file) {
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

    /**
     * @return string[]
     *
     * @psalm-return list<string>
     */
    public static function getTables()
    {
        $tables = [];
        foreach (rex_sql::factory()->getTables(rex::getTablePrefix()) as $table) {
            if (substr($table, 0, strlen(rex::getTablePrefix() . rex::getTempPrefix())) != rex::getTablePrefix() . rex::getTempPrefix()) { // Tabellen die mit rex_tmp_ beginnne, werden nicht exportiert!
                $tables[] = $table;
            }
        }
        return $tables;
    }

    private static function importScript($filename, $importType, $eventType)
    {
        if (file_exists($filename)) {
            require $filename;
        }
    }

    private static function exportTable($table, &$start, $max, $fp, $nl, array $fields)
    {
        do {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT * FROM ' . $sql->escapeIdentifier($table) . ' LIMIT ' . $start . ',' . $max);
            $count = $sql->getRows();

            if ($count > 0 && 0 == $start) {
                fwrite($fp, $nl . 'LOCK TABLES ' . $sql->escapeIdentifier($table) . ' WRITE;');
                fwrite($fp, $nl . '/*!40000 ALTER TABLE ' . $sql->escapeIdentifier($table) . ' DISABLE KEYS */;');
            } elseif (0 == $count) {
                break;
            }

            $start += $max;
            $values = [];

            foreach ($sql as $row) {
                $record = [];
                $array = $row->getRow(PDO::FETCH_NUM);

                foreach ($fields as $idx => $type) {
                    $column = $array[$idx];

                    if (null === $column) {
                        $record[] = 'NULL';

                        continue;
                    }

                    switch ($type) {
                        // prevent calling sql->escape() on values with a known format
                        case 'raw':
                            $record[] = "'" . $column . "'";
                            break;
                        case 'int':
                            $record[] = (int) $column;
                            break;
                        case 'double':
                            $record[] = sprintf('%.10F', (float) $column);
                            break;
                        case 'string':
                            // fast-exit for very frequent used harmless values
                            if ('0' === $column || '' === $column || ' ' === $column || '|' === $column || '||' === $column) {
                                $record[] = "'" . $column . "'";
                                break;
                            }

                            // fast-exit for very frequent used harmless values
                            if (strlen($column) <= 3 && ctype_alnum($column)) {
                                $record[] = "'" . $column . "'";
                                break;
                            }
                        // no break
                        default:
                            $record[] = $sql->escape($column);
                            break;
                    }
                }

                $values[] = $nl . '  (' . implode(',', $record) . ')';
            }

            if (!empty($values)) {
                fwrite($fp, $nl . 'INSERT INTO ' . $sql->escapeIdentifier($table) . ' VALUES ');

                // iterate the values instead of implode() to save a few MB memory
                $numValues = count($values);
                $lastIdx = $numValues - 1;
                for ($i = 0; $i < $numValues; ++$i) {
                    if ($i == $lastIdx) {
                        fwrite($fp, $values[$i]);
                    } else {
                        fwrite($fp, $values[$i]. ',');
                    }
                }
                unset($values);

                fwrite($fp, ';');
            }
        } while ($count >= $max);
    }
}
