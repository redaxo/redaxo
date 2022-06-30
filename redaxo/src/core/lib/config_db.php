<?php

/**
 * @psalm-readonly
 *
 * @package redaxo\core
 */
final class rex_config_db
{
    /**
     * @var string
     */
    public $host;
    /**
     * @var string
     */
    public $login;
    /**
     * @var string
     */
    public $password;
    /**
     * @var string
     */
    public $name;
    /**
     * @var bool
     */
    public $persistent;

    /**
     * @var string|null
     */
    public $sslKey;
    /**
     * @var string|null
     */
    public $sslCert;
    /**
     * @var string|null
     */
    public $sslCa;

    public function __construct(array $dbConfig)
    {
        $this->host = $dbConfig['host'];
        $this->login = $dbConfig['login'];
        $this->password = $dbConfig['password'];
        $this->name = $dbConfig['name'];
        $this->persistent = $dbConfig['persistent'] ?? false;

        $this->sslKey = $dbConfig['ssl_key'] ?? null;
        $this->sslCert = $dbConfig['ssl_cert'] ?? null;
        $this->sslCa = $dbConfig['ssl_ca'] ?? null;
    }
}
