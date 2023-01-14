<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob
 */

class rex_cronjob_phpcode extends rex_cronjob
{
    public function execute()
    {
        /** @psalm-taint-escape eval */ // It is intended to eval code from user input (saved to db)
        $code = preg_replace('/^\<\?(?:php)?/', '', $this->getParam('code'));
        $is = ini_set('display_errors', '1');
        ob_start();
        $return = false;

        try {
            $return = eval($code);
        } catch (Throwable $exception) {
            echo $exception::class.': '.$exception->getMessage();
        }

        $output = ob_get_clean();
        ini_set('display_errors', $is);
        if ($output) {
            $output = str_replace(["\r\n\r\n", "\n\n"], "\n", trim(strip_tags($output)));
            $output = preg_replace('@in ' . preg_quote(__FILE__, '@') . "\\([0-9]*\\) : eval\\(\\)'d code @", '', $output);
            $this->setMessage($output);
        }
        return false !== $return;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('cronjob_type_phpcode');
    }

    public function getParamFields()
    {
        return [
            [
                'label' => rex_i18n::msg('cronjob_type_phpcode'),
                'name' => 'code',
                'type' => 'textarea',
                'attributes' => ['rows' => 20, 'class' => 'form-control rex-code rex-js-code', 'spellcheck' => 'false'],
            ],
        ];
    }
}
