<?php

/**
 * Disable loading(bootstrapping) of any packages when the command is executed. This might be useful e.g. when loading of a package requires a db-connection, but connection credentials have not been defined yet.
 *
 * @package redaxo\core
 *
 * @internal Only usable in rex core commands
 */
interface rex_command_standalone
{
}
