<?php

/**
 * @package redaxo\media-manager
 */
class rex_effect_header extends rex_effect_abstract
{
    public function execute()
    {
        if ($this->params['cache'] == 'no_cache') {
            $this->media->setHeader('Cache-Control', 'must-revalidate, proxy-revalidate, private, no-cache, max-age=0');
            $this->media->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT'); // in the past
        } elseif (isset($this->params['expires']) && 'no' !== $this->params['expires']) {
            $this->media->setHeader('Expires', gmdate('D, d M Y H:i:s T', strtotime('+'.$this->params['expires'])));
        }

        if ($this->params['download'] == 'download') {
            $this->media->setHeader('Content-Disposition', 'attachment; filename="' . basename($this->media->getMediaFilename()) . '";');
        }

        /*
         header("Pragma: public"); // required
         header("Expires: 0");
         header("Content-Transfer-Encoding: binary");
         header("Content-Length: ".$fsize);
         */
    }

    public function getName()
    {
        return rex_i18n::msg('media_manager_effect_header');
    }

    public function getParams()
    {
        return [
            [
                'label' => rex_i18n::msg('media_manager_effect_header_download'),
                'name' => 'download',
                'type' => 'select',
                'options' => ['open_media', 'download'],
                'default' => 'open_media',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_header_cache'),
                'name' => 'cache',
                'type' => 'select',
                'options' => ['no_cache', 'cache'],
                'default' => 'no_cache',
            ],
            [
                'label' => rex_i18n::msg('media_manager_effect_header_expires'),
                'name' => 'expires',
                'type' => 'select',
                'options' => ['no', '1 min', '1 hour', '1 day', '1 week', '1 month', '1 year'],
                'default' => 'no',
                'suffix' => '
<script type="text/javascript">
<!--
$(function() {
    var $cache = $("#media-manager-rex-effect-header-cache-select");
    var $expires = $("#media-manager-rex-effect-header-expires-select");

    $cache.change(function() {
        if ("no_cache" === $cache.val()) {
            $expires.prop("disabled", true).val("no");
        } else {
            $expires.prop("disabled", false);
        }
    }).change();
});
//--></script>',
            ],
        ];
    }
}
