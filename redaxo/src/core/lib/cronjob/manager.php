<?php

use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Path;

class rex_cronjob_manager
{
    /** @var list<class-string<rex_cronjob>>|null */
    private static $types;

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
     * @api
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
            foreach ($params as $key => $value) {
                $cronjob->setParam(str_replace($type . '_', '', $key), $value);
            }

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
     * @param bool $success
     * @param string $message
     * @return void
     */
    public function log($success, $message)
    {
        $name = $this->name;
        if (!$name) {
            if ($this->cronjob instanceof rex_cronjob) {
                $name = Core::isBackend() ? $this->cronjob->getTypeName() : $this->cronjob->getType();
            } else {
                $name = '[no name]';
            }
        }

        if ('backend' === Core::getEnvironment() && 'cronjob/cronjobs' == rex_get('page') && 'execute' == rex_get('func')) {
            $environment = 'backend_manual';
        } else {
            $environment = Core::getEnvironment();
        }

        $log = rex_log_file::factory(Path::log('cronjob.log'), 2_000_000);
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
        if (null === self::$types) {
            self::$types = [];

            self::$types[] = rex_cronjob_urlrequest::class;
            self::$types[] = rex_cronjob_export::class;
            self::$types[] = rex_cronjob_optimize_tables::class;
            self::$types[] = rex_cronjob_article_status::class;
            self::$types[] = rex_cronjob_structure_history::class;
            self::$types[] = rex_cronjob_mailer_purge::class;
        }

        return self::$types;
    }

    /**
     * @param class-string<rex_cronjob> $class
     * @return void
     */
    public static function registerType($class)
    {
        $types = self::getTypes();
        $types[] = $class;
        self::$types = $types;
    }

    /**
     * @return string
     */
    public static function getCurrentEnvironment()
    {
        if (defined('REX_CRONJOB_SCRIPT') && REX_CRONJOB_SCRIPT) {
            return 'script';
        }

        return Core::isBackend() ? 'backend' : 'frontend';
    }
}
