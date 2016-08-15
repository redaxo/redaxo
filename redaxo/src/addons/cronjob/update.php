<?php

/** @var rex_addon $this */

if (rex_string::versionCompare($this->getVersion(), '2.1-dev', '<')) {
    rex_sql::factory()->setQuery('UPDATE '.REX_CRONJOB_TABLE.' SET environment = REPLACE(REPLACE(environment, "|0|", "|frontend|"), "|1|", "|backend|")');
}
