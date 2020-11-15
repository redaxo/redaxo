<?php

/**
 * @psalm-readonly
 */
class rex_config_db
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
    public $ssl_key;
    /**
     * @var string|null
     */
    public $ssl_cert;
    /**
     * @var string|null
     */
    public $ssl_ca;

    public function __construct(array $dbConfig)
    {
        $this->host = $dbConfig['host'];
        $this->login = $dbConfig['login'];
        $this->password = $dbConfig['password'];
        $this->name = $dbConfig['name'];
        $this->persistent = $dbConfig['persistent'];

        $this->ssl_key = $dbConfig['ssl_key'];
        $this->ssl_cert = $dbConfig['ssl_cert'];
        $this->ssl_ca = $dbConfig['ssl_ca'];
    }
}
