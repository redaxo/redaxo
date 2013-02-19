<?php

class rex_effect_header extends rex_effect_abstract
{

    private
        $options,
        $script;

    public function __construct()
    {
        $this->options = array(
            'top',
            'topleft',
            'left',
            'bottomleft',
            'bottom',
            'bottomright',
            'right',
            'topright',
            'center'
        );

            $this->script = '
<script type="text/javascript">
<!--

(function($) {
    $(function() {
        var $fx_workspace_select_trans = $("#media_manager_rex_effect_workspace_set_transparent_select");
        var $fx_workspace_bg_r = $("#media_manager_rex_effect_workspace_bg_r_text").parent().parent();
        var $fx_workspace_bg_g = $("#media_manager_rex_effect_workspace_bg_g_text").parent().parent();
        var $fx_workspace_bg_b = $("#media_manager_rex_effect_workspace_bg_b_text").parent().parent();

        $fx_workspace_select_trans.change(function(){
            if(jQuery(this).val() != "colored")
            {
                $fx_workspace_bg_r.hide();
                $fx_workspace_bg_g.hide();
                $fx_workspace_bg_b.hide();
            }else
            {
                $fx_workspace_bg_r.show();
                $fx_workspace_bg_g.show();
                $fx_workspace_bg_b.show();
            }
        }).change();
    });
})(jQuery);

//--></script>';

    }

    public function execute()
    {

        if ($this->params['cache'] == 'no_cache') {
            $this->media->setHeader('Cache-Control', 'no-cache, must-revalidate');
            $this->media->setHeader('Cache-Control', 'private');
            $this->media->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT'); // in the past
        }

        if ($this->params['download'] == 'download') {
            $this->media->setHeader('Content-Disposition', "attachment; filename=\"" . basename($this->media->getMediaFilename()) . "\";");
        }

        /*
         header("Pragma: public"); // required
         header("Expires: 0");
         header("Content-Transfer-Encoding: binary");
         header("Content-Length: ".$fsize);
         */

    }


    public function getParams()
    {
        return array(
            array(
                'label' => rex_i18n::msg('media_manager_effect_header_download'),
                'name' => 'download',
                'type'  => 'select',
                'options'  => array('open_media', 'download'),
                'default' => 'open_media'
            ),
            array(
                'label' => rex_i18n::msg('media_manager_effect_header_cache'),
                'name' => 'cache',
                'type' => 'select',
                'options' => array('no_cache', 'cache'),
                'default' => 'no_cache',
            ),
        );
    }
}
