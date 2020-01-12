<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

class rex_cronjob_urlrequest extends rex_cronjob
{
    public function execute()
    {
        try {
            $socket = rex_socket::factoryUrl($this->getParam('url'));
            if ('|1|' == $this->getParam('http-auth')) {
                $socket->addBasicAuthorization($this->getParam('user'), $this->getParam('password'));
            }
            if ('' != ($post = $this->getParam('post'))) {
                $response = $socket->doPost($post);
            } else {
                $response = $socket->doGet();
            }
            $statusCode = $response->getStatusCode();
            $success = $response->isSuccessful();
            $message = $statusCode . ' ' . $response->getStatusMessage();
            if (in_array($statusCode, [301, 302, 303, 307])
                && $this->getParam('redirect', true)
                && ($location = $response->getHeader('Location'))
            ) {
                // maximal eine Umleitung zulassen
                $this->setParam('redirect', false);
                $this->setParam('url', $location);
                // rekursiv erneut ausfuehren
                $success = $this->execute();
                if ($this->hasMessage()) {
                    $message .= ' -> ' . $this->getMessage();
                } else {
                    $message .= ' -> Unknown error';
                }
            }
            $this->setMessage($message);
            return $success;
        } catch (rex_exception $e) {
            $this->setMessage($e->getMessage());
            return false;
        }
    }

    public function getTypeName()
    {
        return rex_i18n::msg('cronjob_type_urlrequest');
    }

    public function getParamFields()
    {
        return [
            [
                'label' => rex_i18n::msg('cronjob_type_urlrequest_url'),
                'name' => 'url',
                'type' => 'text',
                'default' => 'http://',
            ],
            [
                'label' => rex_i18n::msg('cronjob_type_urlrequest_post'),
                'name' => 'post',
                'type' => 'text',
            ],
            [
                'name' => 'http-auth',
                'type' => 'checkbox',
                'options' => [1 => rex_i18n::msg('cronjob_type_urlrequest_httpauth')],
            ],
            [
                'label' => rex_i18n::msg('cronjob_type_urlrequest_user'),
                'name' => 'user',
                'type' => 'text',
                'visible_if' => ['http-auth' => 1],
            ],
            [
                'label' => rex_i18n::msg('cronjob_type_urlrequest_password'),
                'name' => 'password',
                'type' => 'text',
                'visible_if' => ['http-auth' => 1],
            ],
        ];
    }
}
