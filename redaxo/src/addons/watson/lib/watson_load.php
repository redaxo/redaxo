<?php

class watson_load
{

    static function init()
    {
        if(!defined('APPLICATION_ENV')) {
            if(false === stripos($_SERVER['SERVER_NAME'], 'localhost')) {
                define('APPLICATION_ENV', 'development');
            } else {
                define('APPLICATION_ENV', 'production');
            }
        }
    }

    static function install()
    {
        global $REX;

        $myaddon = 'watson';
        $REX['ADDON']['install'][$myaddon] = 1;
    }
}
