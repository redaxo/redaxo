<?php

/**
 * A readonly representation of the config.yml system config which allows type-safe working with often used system-config properties.
 *
 * @psalm-immutable
 * @psalm-pure
 */
final class rex_system_config {
    public function __construct(array $config) {
        $this->instname = $config['instname'];
        $this->server = $config['server'];
        $this->servername = $config['servername'];
        $this->error_email = $config['error_email'];

        $this->lang = $config['lang'];
        $this->db = $config['db'];
    }

    /**
     * @var string
     */
    public $instname;
    /**
     * @var string
     */
    public $server;
    /**
     * @var string
     */
    public $servername;
    /**
     * @var string
     */
    public $error_email;

    /**
     * @var string
     */
    public $lang;

    /**
     * @psalm-var array{
     *   1: array<host: string, login: string, password: string, name: string, persistent: bool>
     *   2: array<host: string, login: string, password: string, name: string, persistent: bool>
     * }
     * @var array
     */
    public $db;
}
