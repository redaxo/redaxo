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
    /** @var list<class-string<rex_cronjob>> */
    private static $types = [
        rex_cronjob_phpcode::class,
        rex_cronjob_phpcallback::class,
        rex_cronjob_urlrequest::class,
    ];

    /** @var string */
    private $message = '';
    /** @var rex_cronjob|class-string<rex_cronjob>|null */
    private $cronjob;
    /** @var string|null */
    private $name;
    /** @var int|null */
    private $id;

    /**
     * @return self
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function hasMessage()
    {
        return !empty($this->message);
    }

    /**
     * @param rex_cronjob|class-string<rex_cronjob> $cronjob
     * @return void
     */
    public function setCronjob($cronjob)
    {
        $this->cronjob = $cronjob;
    }

    /**
     * @param rex_cronjob|class-string<rex_cronjob> $cronjob
     * @param string $name
     * @param array $params
     * @param bool $log
     * @param int|null $id
     * @return bool
     */
    public function tryExecute($cronjob, $name = '', $params = [], $log = true, $id = null)
    {
        if (!$cronjob instanceof rex_cronjob) {
            $success = false;
            if (is_object($cronjob)) {
                $message = 'Invalid cronjob class "' . $cronjob::class . '"';
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

            $message = '';
            try {
                $success = $cronjob->execute();
                $message = $cronjob->getMessage();
            } catch (Throwable $t) {
                $success = false;
                $message = $t->getMessage();
            }

            if ('' == $message && !$success) {
                $message = 'Unknown error';
            }
        }

        if ($log) {
            $this->log($success, $message);
        }

        $this->setMessage(rex_escape($message));
        $this->cronjob = null;
        $this->id = null;

        return $success;
    }

    /**
     * @param bool   $success
     * @param string $message
     * @return void
     */
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

        if ('backend' === rex::getEnvironment() && 'cronjob/cronjobs' == rex_get('page') && 'execute' == rex_get('func')) {
            $environment = 'backend_manual';
        } else {
            $environment = rex::getEnvironment();
        }

        $log = new rex_log_file(rex_path::log('cronjob.log'), 2_000_000);
        $data = [
            $success ? 'SUCCESS' : 'ERROR',
            $this->id ?: '--',
            $name,
            strip_tags($message),
            $environment,
        ];
        $log->add($data);
    }

    /**
     * @return list<class-string<rex_cronjob>>
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * @param class-string<rex_cronjob> $class
     * @return void
     */
    public static function registerType($class)
    {
        self::$types[] = $class;
    }

    /**
     * @return string
     */
    public static function getCurrentEnvironment()
    {
        if (defined('REX_CRONJOB_SCRIPT') && REX_CRONJOB_SCRIPT) {
            return 'script';
        }

        return rex::isBackend() ? 'backend' : 'frontend';
    }
}
