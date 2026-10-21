<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\Renderable;
use Redaxo\Core\View\RendersView;

/** Link for leaving a form without saving. */
final class CancelLink implements Renderable
{
    use RendersView;

    /** @internal */
    public function __construct(
        public string $url,
    ) {}
}
