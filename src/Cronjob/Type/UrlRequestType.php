<?php

namespace Redaxo\Core\Cronjob\Type;

use Redaxo\Core\HttpClient\Request;
use Redaxo\Core\Translation\I18n;
use rex_exception;

use function in_array;

class UrlRequestType extends AbstractType
{
    public function execute()
    {
        try {
            $socket = Request::factoryUrl($this->getParam('url'));
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
        return I18n::msg('cronjob_type_urlrequest');
    }

    public function getParamFields()
    {
        return [
            [
                'label' => I18n::msg('cronjob_type_urlrequest_url'),
                'name' => 'url',
                'type' => 'text',
                'default' => 'https://',
            ],
            [
                'label' => I18n::msg('cronjob_type_urlrequest_post'),
                'name' => 'post',
                'type' => 'text',
            ],
            [
                'name' => 'http-auth',
                'type' => 'checkbox',
                'options' => [1 => I18n::msg('cronjob_type_urlrequest_httpauth')],
            ],
            [
                'label' => I18n::msg('cronjob_type_urlrequest_user'),
                'name' => 'user',
                'type' => 'text',
                'visible_if' => ['http-auth' => 1],
            ],
            [
                'label' => I18n::msg('cronjob_type_urlrequest_password'),
                'name' => 'password',
                'type' => 'text',
                'visible_if' => ['http-auth' => 1],
            ],
        ];
    }
}
