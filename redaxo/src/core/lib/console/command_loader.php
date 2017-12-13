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
            'package:activate' => rex_command_package_activate::class,
            'package:deactivate' => rex_command_package_deactivate::class,
            'package:install' => rex_command_package_install::class,
            'package:uninstall' => rex_command_package_uninstall::class,
        ];
        foreach ($commands as $command => $class) {
            $this->commands[$command] = ['class' => $class];
        }

        foreach (rex_package::getAvailablePackages() as $package) {
            $commands = $package->getProperty('console_commands');

            if (!$commands) {
                continue;
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
