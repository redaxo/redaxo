<?php

namespace Redaxo\Core\Console\Command;

/**
 * Only load(bootstrap) setup addons when the command is executed.
 *
 * see rex_addon::getSetupAddons()
 *
 * @internal Only usable in rex core commands
 */
interface OnlySetupAddonsInterface {}
