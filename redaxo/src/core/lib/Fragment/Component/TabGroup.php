<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Enum\Placement;
use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/TabGroup.php
 */
final class TabGroup extends Fragment
{
    public function __construct(
        /**
         * The tab groups main content.
         *
         * @var list<Fragment>
         */
        public array $elements,

        /**
         * The placement of the tabs.
         */
        public Placement $placement = Placement::Top,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/TabGroup.php';
    }
}
