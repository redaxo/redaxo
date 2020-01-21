<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_command_setup_run extends rex_console_command implements rex_command_only_setup_packages
{
    /** @var \Symfony\Component\Console\Style\SymfonyStyle */
    private $io;

    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this
            ->setDescription('Perform redaxo setup')
            ->addOption('--lang', null, InputOption::VALUE_REQUIRED, 'System language e.g. "de_de" or "en_gb"')
            ->addOption('--agree-license', null, InputOption::VALUE_NONE, 'Accept license terms and conditions')
            ->addOption('--server', null, InputOption::VALUE_REQUIRED, 'Website URL e.g. "https://example.org/"')
            ->addOption('--servername', null, InputOption::VALUE_REQUIRED, 'Website name')
            ->addOption('--error-email', null, InputOption::VALUE_REQUIRED, 'Error mail address e.g. "info@example.org"')
            ->addOption('--timezone', null, InputOption::VALUE_REQUIRED, 'Timezone e.g. "Europe/Berlin"')
            ->addOption('--db-host', null, InputOption::VALUE_REQUIRED, 'Database hostname e.g. "localhost" or "127.0.0.1"')
            ->addOption('--db-login', null, InputOption::VALUE_REQUIRED, 'Database username e.g. "root"')
            ->addOption('--db-password', null, InputOption::VALUE_REQUIRED, 'Database user password')
            ->addOption('--db-name', null, InputOption::VALUE_REQUIRED, 'Database name e.g. "redaxo"')
            ->addOption('--db-createdb', null, InputOption::VALUE_NONE, 'Creates the database')
            ->addOption('--db-setup', null, InputOption::VALUE_REQUIRED, 'Database setup mode e.g. "normal", "override" or "import"')
            ->addOption('--db-charset', null, InputOption::VALUE_REQUIRED, 'Database charset "utf8" or "utf8mb4"')
            ->addOption('--db-import', null, InputOption::VALUE_REQUIRED, 'Database import filename if "import" is used as --db-setup')
            ->addOption('--admin-username', null, InputOption::VALUE_REQUIRED, 'Creates a redaxo admin user with the given username')
            ->addOption('--admin-password', null, InputOption::VALUE_REQUIRED, 'Sets the password for the admin user account')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = $this->getStyle($input, $output);

        $this->io = $io;
        $this->input = $input;
        $this->output = $output;

        $configFile = rex_path::coreData('config.yml');
        $config = array_merge(
            rex_file::getConfig(rex_path::core('default.config.yml')),
            rex_file::getConfig($configFile)
        );
        $config['setup'] = true;

        $requiredValue = static function ($value) {
            if (empty($value)) {
                throw new InvalidArgumentException('Value required');
            }
            return $value;
        };

        // ---------------------------------- Step 1 . Language
        $io->title('Step 1 of 6 / Language');
        $langs = [];
        foreach (rex_i18n::getLocales() as $locale) {
            $langs[$locale] = rex_i18n::msgInLocale('lang', $locale);
        }
        ksort($langs);

        if (null === $input->getOption('lang')) {
            $config['lang'] = $io->askQuestion(new ChoiceQuestion('Please select a language', $langs));
        } else {
            $lang = $input->getOption('lang');
            if (!$lang || !array_key_exists($lang, $langs)) {
                throw new InvalidArgumentException('Unknown lang "' . $lang . '" specified');
            }
            $config['lang'] = $lang;
            $io->success('Language "'.$lang.'" selected.');
        }

        // ---------------------------------- Step 2 . license
        $io->title('Step 2 of 6 / License');

        if (false === $input->getOption('agree-license')) {
            $license_file = rex_path::base('LICENSE.md');
            $license = rex_file::get($license_file);
            $io->writeln($license);
            if (!$io->confirm('Accept license terms and conditions?')) {
                $io->error('You need to accept license terms and conditions');
                return 1;
            }
        } else {
            if (null === $input->getOption('agree-license')) {
                $io->error('You need to accept license terms and conditions');
                return 1;
            }
            $io->success('You accepted license terms and conditions');
        }

        // ---------------------------------- Step 3 . Perms, Environment
        $io->title('Step 3 of 6 / System check');

        /** Cloned from comannd setup:check*/
        $errors = rex_setup::checkEnvironment();
        if (0 == count($errors)) {
            $io->success('PHP version ok');
        } else {
            $errors = array_map([$this, 'decodeMessage'], $errors);
            $io->error("PHP version errors:\n" .implode("\n", $errors));
            return 1;
        }

        $res = rex_setup::checkFilesystem();
        if (count($res) > 0) {
            $errors = [];
            foreach ($res as $key => $messages) {
                if (count($messages) > 0) {
                    $affectedFiles = [];
                    foreach ($messages as $message) {
                        $affectedFiles[] = rex_path::relative($message);
                    }
                    $errors[] = rex_i18n::msg($key) . "\n". implode("\n", $affectedFiles);
                }
            }

            $errors = array_map([$this, 'decodeMessage'], $errors);
            $io->error("Directory permissions error:\n" .implode("\n", $errors));
            return 1;
        }
        $io->success('Directory permissions ok');

        // ---------------------------------- step 4 . Config
        $io->title('Step 4 of 6 / Creating config');

        $io->section('General');
        if ($input->getOption('server')) {
            $config['server'] = $input->getOption('server');
            $io->success('Using website URL "'.$config['server'].'"');
        } else {
            $config['server'] = $io->ask('Website URL', $config['server'], $requiredValue);
        }

        if ($input->getOption('servername')) {
            $config['servername'] = $input->getOption('servername');
            $io->success('Using website name "'.$config['servername'].'"');
        } else {
            $config['servername'] = $io->ask('Website name', $config['servername'], $requiredValue);
        }

        if ($input->getOption('error-email')) {
            $config['error_email'] = $input->getOption('error-email');
            $io->success('Using "'.$config['error_email'].'" in case of errors');
        } else {
            $config['error_email'] = $io->ask('E-mail address in case of errors', $config['error_email'], $requiredValue);
        }

        if (!$input->getOption('timezone')) {
            $q = new Question('Choose timezone', $config['timezone']);
            $q->setAutocompleterValues(DateTimeZone::listIdentifiers());
            $q->setValidator(static function ($value) {
                if (false === @date_default_timezone_set($value)) {
                    throw new RuntimeException('Time zone invalid');
                }
                return $value;
            });
            $config['timezone'] = $io->askQuestion($q);
        } else {
            $timezone = $input->getOption('timezone');
            if (!in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
                throw new InvalidArgumentException('Unknown timezone "'.$timezone.'" specified');
            }
            $config['timezone'] = $timezone;
            $io->success('Timezone "'.$timezone.'" selected');
        }

        if (!$input->getOption('db-host') && !$input->getOption('db-login') && !$input->getOption('db-password') && !$input->getOption('db-name')) {
            $io->section('Database information');
            do {
                $config['db'][1]['host'] = $io->ask('MySQL host', $config['db'][1]['host']);
                $config['db'][1]['login'] = $io->ask('Login', $config['db'][1]['login']);
                $config['db'][1]['password'] = $io->askHidden('Password');
                $config['db'][1]['name'] = $io->ask('Database name', $config['db'][1]['name']);

                $redaxo_db_create = $io->confirm('Create database', false);

                rex::setProperty('db', $config['db']);
                try {
                    $err = rex_setup::checkDb($config, $redaxo_db_create);
                } catch (PDOException $e) {
                    $err = 'The following error occured: ' . $e->getMessage();
                }

                if ('' !== $err) {
                    $io->error($err);
                }
            } while ('' !== $err);
        } else {
            $config['db'][1]['host'] = $input->getOption('db-host');
            $config['db'][1]['login'] = $input->getOption('db-login');
            $config['db'][1]['password'] = $input->getOption('db-password');
            $config['db'][1]['name'] = $input->getOption('db-name');

            $redaxo_db_create = true === $input->getOption('db-createdb');

            rex::setProperty('db', $config['db']);
            try {
                $err = rex_setup::checkDb($config, $redaxo_db_create);
            } catch (PDOException $e) {
                $err = 'The following error occured: ' . $e->getMessage();
            }

            if ('' !== $err) {
                $io->error($err);
                return 1;
            }
        }
        $io->success('Database connection successfully established');

        // ---------------------------------- step 5 . create db / demo
        $io->title('Step 5 of 6 / Database');

        // Search for exports
        $backups = [];
        foreach (rex_backup::getBackupFiles('') as $file) {
            if ('.sql' != substr($file, strlen($file) - 4)) {
                continue;
            }
            $backups[] = substr($file, 0, -4);
        }

        $createdbOptions = [
            'normal' => 'Setup database',
            'override' => 'Setup database and overwrite it if it exitsts already (Caution - All existing data will be deleted!',
            'existing' => 'Database already exists (Continue without database import)',
            'update' => 'Update database (Update from previous version)',
        ];
        if (count($backups) > 0) {
            $createdbOptions['import'] = 'Import existing database export';
        }

        if (!$input->getOption('db-setup')) {
            $createdb = $io->askQuestion(new ChoiceQuestion('Setup database', $createdbOptions, 'normal'));
        } else {
            $validOptions = array_keys($createdbOptions);
            $createdb = $input->getOption('db-setup');
            if (!in_array($createdb, $validOptions, true)) {
                throw new InvalidArgumentException('Unknown db-setup value "'.$createdb.'". Valid values are ' . implode(', ', $validOptions));
            }
        }

        $tables_complete = ('' == rex_setup_importer::verifyDbSchema()) ? true : false;

        if ('update' == $createdb) {
            $useUtf8mb4 = $this->getDbCharset();
            $config['utf8mb4'] = 'utf8mb4' === $useUtf8mb4;
            rex_sql_table::setUtf8mb4($config['utf8mb4']);
            $error = rex_setup_importer::updateFromPrevious();
            $io->success('Database successfully updated');
        } elseif ('import' == $createdb) {
            $import_name = $input->getOption('db-import') ?? $io->askQuestion(new ChoiceQuestion('Please choose a database export', $backups));
            if (!in_array($import_name, $backups, true)) {
                throw new InvalidArgumentException('Unknown import file "'.$import_name.'" specified');
            }
            $error = rex_setup_importer::loadExistingImport($import_name);
            $io->success('Database successfully imported using file "'.$import_name.'"');
        } elseif ('existing' == $createdb && $tables_complete) {
            $error = rex_setup_importer::databaseAlreadyExists();
            $io->success('Skipping database setup');
        } elseif ('override' == $createdb) {
            $useUtf8mb4 = $this->getDbCharset();
            $config['utf8mb4'] = 'utf8mb4' === $useUtf8mb4;
            rex_sql_table::setUtf8mb4($config['utf8mb4']);
            $error = rex_setup_importer::overrideExisting();
            $io->success('Database successfully overwritten');
        } elseif ('normal' == $createdb) {
            $useUtf8mb4 = $this->getDbCharset();
            $config['utf8mb4'] = 'utf8mb4' === $useUtf8mb4;
            rex_sql_table::setUtf8mb4($config['utf8mb4']);
            $error = rex_setup_importer::prepareEmptyDb();
            $io->success('Database successfully created');
        } else {
            $error = 'An undefinied error occurred';
        }

        if ('' !== $error) {
            $io->error($this->decodeMessage($error));
            return 1;
        }

        $error = rex_setup_importer::verifyDbSchema();
        if ('' != $error) {
            $io->error($this->decodeMessage($error));
            return 1;
        }

        rex_clang_service::generateCache();
        rex::setConfig('version', rex::getVersion());

        // ---------------------------------- Step 6 . Create User
        $io->title('Step 6 of 6 / User');

        $login = null;
        $password = null;

        $passwordPolicy = rex_backend_password_policy::factory(rex::getProperty('password_policy', []));
        if (null === $input->getOption('admin-username') || null === $input->getOption('admin-password')) {
            $user = rex_sql::factory();
            $user
                ->setTable(rex::getTable('user'))
                ->select();

            $skipUserCreation = false;
            if ($user->getRows()) {
                $skipUserCreation = $io->confirm('Users already exists. Skip user creation?');
            }

            if (!$skipUserCreation) {
                $io->section('Create administrator account');
                $login = $io->ask('Username', null, static function ($login) {
                    $user = rex_sql::factory();
                    $user
                        ->setTable(rex::getTable('user'))
                        ->setWhere(['login' => $login])
                        ->select();

                    if ($user->getRows()) {
                        throw new InvalidArgumentException(sprintf('User "%s" already exists.', $login));
                    }
                    return $login;
                });
                $password = $io->askHidden('Password', static function ($password) use ($passwordPolicy) {
                    if (true !== $msg = $passwordPolicy->check($password)) {
                        throw new InvalidArgumentException($msg);
                    }

                    return $password;
                });
            }
        } else {
            $login = $input->getOption('admin-username');
            $password = $input->getOption('admin-password');
            if (true !== $msg = $passwordPolicy->check($password)) {
                $io->error($msg);
                return 1;
            }
        }

        if ($login && $password) {
            $user = rex_sql::factory();
            $user
                ->setTable(rex::getTable('user'))
                ->setWhere(['login' => $login])
                ->select();

            if ($user->getRows()) {
                throw new InvalidArgumentException(sprintf('User "%s" already exists.', $login));
            }

            $user = rex_sql::factory();
            $user->setTable(rex::getTablePrefix() . 'user');
            $user->setValue('login', $login);
            $user->setValue('password', rex_backend_login::passwordHash($password));
            $user->setValue('admin', 1);
            $user->addGlobalCreateFields('console');
            $user->addGlobalUpdateFields('console');
            $user->setValue('status', '1');
            $user->insert();

            $io->success(sprintf('User "%s" successfully created.', $login));
        }

        // ---------------------------------- last step. save config

        $config['setup'] = false;
        if (!rex_file::putConfig($configFile, $config)) {
            $io->error('Writing to config.yml failed.');
            return 1;
        }
        rex_file::delete(rex_path::coreCache('config.yml.cache'));

        $io->success('Congratulations! REDAXO has successfully been installed.');
        return 0;
    }

    /**
     * @return bool|string false|utf8|utf8mb4
     */
    private function getDbCharset()
    {
        if ($charset = $this->input->getOption('db-charset')) {
            if (!in_array($charset, ['utf8', 'utf8mb8'])) {
                throw new InvalidArgumentException('unknown database charset "'.$charset.'" specified');
            }
            if ('utf8mb4' === $charset && !rex_setup_importer::supportsUtf8mb4()) {
                $sql = rex_sql::factory();
                throw new InvalidArgumentException('Your database doesn\'t support utf8mb4. It requires at least MySQL 5.7.7 or MariaDB 10.2. You are using '.$sql->getDbType(). ' '.$sql->getDbVersion());
            }
            return $charset;
        }

        if (!rex_setup_importer::supportsUtf8mb4()) {
            $sql = rex_sql::factory();
            $this->io->writeln('Your database doesn\'t support utf8mb4. It requires at least MySQL 5.7.7 or MariaDB 10.2. You are using '.$sql->getDbType(). ' '.$sql->getDbVersion());
            $this->io->writeln('utf8 is deprecated and will removed in future versions of REDAXO.');
            if ($this->io->confirm('Continue with charset utf8 ?', false)) {
                return 'utf8';
            }
            return false;
        }

        return $this->io->choice('Choose database charset', [
            'utf8mb4' => '[recommended] Requires at least MySQL 5.7.7 or MariaDB 10.2. Complete unicode support including emojis and more special characters',
            'utf8' => '[deprecated] non-standard utf8 mode. Won\'t be support in future versions of REDAXO',
        ]);
    }
}
