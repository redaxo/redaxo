<?php

/**
 * Cronjob Addon
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
        'rex_cronjob_urlrequest'
    ];

    private $message = '';
    private $cronjob;
    private $name;
    private $id;

    public static function factory()
    {
        return new self;
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

    public function tryExecute($cronjob, $name = '', $params = [], $log = true, $id = null)
    {
        $message = '';
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

    private function log($success, $message)
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
            strip_tags($message)
        ];
        $log->add($data);
    }

    public function timeout()
    {
        if ($this->cronjob instanceof rex_cronjob) {
            $this->log(false, 'timeout');
            return true;
        }
        return false;
    }

    public static function getTypes()
    {
        return self::$types;
    }

    public static function registerType($class)
    {
        self::$types[] = $class;
    }
}
