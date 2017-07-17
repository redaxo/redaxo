<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

class rex_cronjob_manager
{
    private static $types = [
        'rex_cronjob_phpcode',
        'rex_cronjob_phpcallback',
        'rex_cronjob_urlrequest',
    ];

    private $message = '';
    private $cronjob;
    private $name;
    private $id;

    public static function factory()
    {
        return new self();
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function hasMessage()
    {
        return !empty($this->message);
    }

    public function setCronjob($cronjob)
    {
        $this->cronjob = $cronjob;
    }

    public function tryExecute($cronjob, $name = '', $params = [], $log = true, $id = null)
    {
        $success = $cronjob instanceof rex_cronjob;
        if (!$success) {
            if (is_object($cronjob)) {
                $message = 'Invalid cronjob class "' . get_class($cronjob) . '"';
            } else {
                $message = 'Class "' . $cronjob . '" not found';
            }
        } else {
            $this->name = $name;
            $this->id = $id;
            $this->cronjob = $cronjob;
            $type = $cronjob->getType();
            if (is_array($params)) {
                foreach ($params as $key => $value) {
                    $cronjob->setParam(str_replace($type . '_', '', $key), $value);
                }
            }
            $success = $cronjob->execute();
            $message = $cronjob->getMessage();
            if ($message == '' && !$success) {
                $message = 'Unknown error';
            }
        }

        if ($log) {
            $this->log($success, $message);
        }

        $this->setMessage(htmlspecialchars($message));
        $this->cronjob = null;
        $this->id = null;

        return $success;
    }

    public function log($success, $message)
    {
        $name = $this->name;
        if (!$name) {
            if ($this->cronjob instanceof rex_cronjob) {
                $name = rex::isBackend() ? $this->cronjob->getTypeName() : $this->cronjob->getType();
            } else {
                $name = '[no name]';
            }
        }
        $log = new rex_log_file(rex_path::addonData('cronjob', 'cronjob.log'), 2000000);
        $data = [
            ($success ? 'SUCCESS' : 'ERROR'),
            ($this->id ?: '--'),
            $name,
            strip_tags(nl2br($message),'<br><b>'),
        ];
        $log->add($data);
    }

    public static function getTypes()
    {
        return self::$types;
    }

    public static function registerType($class)
    {
        self::$types[] = $class;
    }

    public static function getCurrentEnvironment()
    {
        if (defined('REX_CRONJOB_SCRIPT') && REX_CRONJOB_SCRIPT) {
            return 'script';
        }

        return rex::isBackend() ? 'backend' : 'frontend';
    }
}
