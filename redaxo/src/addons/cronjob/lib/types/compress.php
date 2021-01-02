<?php

/**
 * Cronjob Addon.
 *
 * @author  Markus Staab
 *
 * @package redaxo\cronjob
 */
class rex_cronjob_compress extends rex_cronjob
{
    public function execute()
    {
        $glob = $this->getParam('glob');

        // try to resolve absolute path
        $files = glob($glob);
        if (!$files) {
            // try to resolve the path relative to the rex-base path
            $files = glob(rex_path::base($glob));
        }

        if ($files) {
            foreach ($files as $file) {
                if (is_file($file) && !str_ends_with($file, '.zip')) {
                    if (!is_readable($file)) {
                        $this->setMessage('file is not readable "'. $file .'"');
                        return false;
                    }
                    $zipArchive = new ZipArchive();
                    if ($zipArchive->open($file.'.zip', ZipArchive::CREATE) && $zipArchive->addFile($file) && $zipArchive->close()) {
                        if ('|1|' == $this->getParam('cleanup')) {
                            rex_file::delete($file);
                        }
                    }
                }
            }
        } else {
            $this->setMessage('Unable to glob() with path '. $glob);
            return false;
        }

        return true;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('cronjob_type_compress');
    }

    public function getParamFields()
    {
        return [
            [
                'label' => rex_i18n::msg('cronjob_type_compress_glob'),
                'name' => 'glob',
                'type' => 'text',
                'notice' => rex_i18n::msg('cronjob_type_compress_glob_notice'),
            ],
            [
                'name' => 'cleanup',
                'type' => 'checkbox',
                'options' => [1 => rex_i18n::msg('cronjob_type_compress_cleanup')],
            ],
        ];
    }
}
