<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

class rex_cronjob_phpcallback extends rex_cronjob
{
    public function execute()
    {
        if (preg_match('/^\s*(?:(\w*?)\:\:)?(\w*?)(?:\((.*?)\))?\;?\s*$/', $this->getParam('callback'), $matches)) {
            $callback = $matches[2];
            if ('' != $matches[1]) {
                $callback = [$matches[1], $callback];
            }
            if (!is_callable($callback)) {
                if (is_array($callback)) {
                    $callback = $callback[0] . '::' . $callback[1];
                }
                $this->setMessage($callback . '() not callable');
                return false;
            }
            $params = [];
            if (isset($matches[3]) && '' != $matches[3]) {
                $params = explode(',', $matches[3]);
                foreach ($params as $i => $param) {
                    $param = preg_replace('/^(\\\'|\")?(.*?)\\1$/', '$2', trim($param));
                    $params[$i] = $param;
                }
            }
            $return = call_user_func_array($callback, $params);
            if (false !== $return) {
                if (is_string($return)) {
                    $this->setMessage($return);
                }
                return true;
            }
            $this->setMessage('Error in callback');
            return false;
        }
        $this->setMessage('Syntax error in callback');
        return false;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('cronjob_type_phpcallback');
    }

    public function getParamFields()
    {
        return [
            [
                'label' => rex_i18n::msg('cronjob_type_phpcallback'),
                'name' => 'callback',
                'type' => 'text',
                'notice' => rex_i18n::msg('cronjob_examples') . ': foo(), foo(1, \'string\'), foo::bar()',
            ],
        ];
    }
}
