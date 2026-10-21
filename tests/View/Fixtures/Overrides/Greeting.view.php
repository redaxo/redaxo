<?php

use Redaxo\Core\Tests\View\Fixtures\Greeting;

use function Redaxo\Core\View\escape;

// Override for Greeting registered via the fixtures namespace in the tests; Card has no override here.
return static function (Greeting $greeting): void { ?>
    <div class="greeting-namespace-override"><?= escape($greeting->name) ?></div>
<?php };
