<?php

namespace Redaxo\Core\Fragment\Page;

use Redaxo\Core\Fragment\Page;
use rex_system_report;

/**
 * @see redaxo/src/core/fragments/core/page/SystemReport.php
 */
final class SystemReport extends Page
{
    public readonly rex_system_report $report;

    public function __construct()
    {
        $this->report = rex_system_report::factory();
    }

    protected function getPath(): string
    {
        return 'core/page/SystemReport.php';
    }
}
