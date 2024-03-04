<?php

use Redaxo\Core\Core;

/**
 * Packages loading.
 */

rex_addon::initialize(!Core::isSetup());

if (Core::isSetup() || Core::isSafeMode()) {
    $packageOrder = array_keys(rex_addon::getSetupAddons());
} else {
    $packageOrder = Core::getPackageOrder();
}

// in the first run, we register all folders for class- and fragment-loading,
// so it is transparent in which order the addons are included afterwards.
foreach ($packageOrder as $packageId) {
    rex_addon::require($packageId)->enlist();
}

// now we actually include the addons logic
rex_timer::measure('packages_boot', static function () use ($packageOrder) {
    foreach ($packageOrder as $packageId) {
        rex_timer::measure('package_boot: ' . $packageId, static function () use ($packageId) {
            rex_addon::require($packageId)->boot();
        });
    }
});

// ----- all addons configs included
rex_extension::registerPoint(new rex_extension_point('PACKAGES_INCLUDED'));
