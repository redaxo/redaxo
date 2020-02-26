<?php

use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_console_command_loader implements CommandLoaderInterface
{
    private $commands = [];

    public function __construct()
    {
        $commands = [
            'cache:clear' => rex_command_cache_clear::class,
            'config:get' => rex_command_config_get::class,
            'config:set' => rex_command_config_set::class,
            'db:connection-options' => rex_command_db_connection_options::class,
            'db:set-connection' => rex_command_db_set_connection::class,
            'setup:check' => rex_command_setup_check::class,
            'setup:run' => rex_command_setup_run::class,
            'user:create' => rex_command_user_create::class,
        ];

        if (!rex::isSetup()) {
            $commands = array_merge($commands, [
                'assets:sync' => rex_command_assets_sync::class,
                'db:dump-schema' => rex_command_db_dump_schema::class,
                'package:activate' => rex_command_package_activate::class,
                'package:deactivate' => rex_command_package_deactivate::class,
                'package:delete' => rex_command_package_delete::class,
                'package:install' => rex_command_package_install::class,
                'package:uninstall' => rex_command_package_uninstall::class,
                'system:report' => rex_command_system_report::class,
                'user:set-password' => rex_command_user_set_password::class,
            ]);
        }

        foreach ($commands as $command => $class) {
            $this->commands[$command] = ['class' => $class];
        }

        foreach (rex_package::getAvailablePackages() as $package) {
            $commands = $package->getProperty('console_commands');

            if (!$commands) {
                continue;
            }

            if (!is_array($commands)) {
                throw new rex_exception('Expecting "console_commands" property to be an array, got "'. gettype($commands).'" from package.yml of "'. $package->getName() .'"');
            }

            foreach ($commands as $command => $class) {
                $this->commands[$command] = [
                    'package' => $package,
                    'class' => $class,
                ];
            }
        }
    }

    public function get($name)
    {
        if (!isset($this->commands[$name])) {
            throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
        }

        $class = $this->commands[$name]['class'];

        /** @var rex_console_command $command */
        $command = new $class();
        $command->setName($name);

        if (isset($this->commands[$name]['package'])) {
            $command->setPackage($this->commands[$name]['package']);
        }

        return $command;
    }

    public function has($name)
    {
        return isset($this->commands[$name]);
    }

    public function getNames()
    {
        return array_keys($this->commands);
    }
}
