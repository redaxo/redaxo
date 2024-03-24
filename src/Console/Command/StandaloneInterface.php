<?php

namespace Redaxo\Core\Console\Command;

/**
 * Disable loading(bootstrapping) of any addon when the command is executed. This might be useful e.g. when loading of a addon requires a db-connection, but connection credentials have not been defined yet.
 *
 * @internal Only usable in rex core commands
 */
interface StandaloneInterface {}
