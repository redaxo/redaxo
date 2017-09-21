<?php

/**
 * Packages loading.
 *
 * @package redaxo5
 */

rex_addon::initialize(!rex::isSetup());

if (rex::isSetup() || rex::isSafeMode()) {
    $packageOrder = array_keys(rex_package::getSetupPackages());
} else {
    $packageOrder = rex::getConfig('package-order', []);
}

// in the first run, we register all folders for class- and fragment-loading,
// so it is transparent in which order the addons are included afterwards.
foreach ($packageOrder as $packageId) {
    rex_package::get($packageId)->enlist();
}

// now we actually include the addons logic
foreach ($packageOrder as $packageId) {
    rex_package::get($packageId)->boot();
}

// ----- all addons configs included
rex_extension::registerPoint(new rex_extension_point('PACKAGES_INCLUDED'));
