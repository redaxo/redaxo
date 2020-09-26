<?php

final class rex_analytics_cronjob extends rex_cronjob
{
    public function execute()
    {
        $analytics = new rex_analytics_webvitals_storage();
        $analytics->condense();

        return true;
    }

    public function getTypeName()
    {
        return __CLASS__;
    }
}
