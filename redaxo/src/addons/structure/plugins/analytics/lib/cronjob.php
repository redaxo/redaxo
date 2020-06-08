<?php

class rex_analytics_cronjob extends rex_cronjob
{
    public function execute()
    {
        $analytics = new rex_analytics();
        $analytics->condense();
    }

    public function getTypeName()
    {
        return __CLASS__;
    }
}
