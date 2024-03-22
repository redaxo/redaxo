<?php

namespace Redaxo\Core\Console\Command;

/**
 * Only load(bootstrap) setup addons when the command is executed.
 *
 * see Addon::getSetupAddons()
 *
 * @internal Only usable in rex core commands
 */
interface OnlySetupAddonsInterface {}
