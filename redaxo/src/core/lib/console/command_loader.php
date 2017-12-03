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

        $this->commands['cache:clear'] = [
            'package' => null,
            'class' => 'rex_command_cache_clear',
        ];
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
        $command->setPackage($this->commands[$name]['package']);

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
