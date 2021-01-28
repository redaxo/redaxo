<?php

class rex_media_form
{
    public $title = '';
    public $buttonTitle = '';
    public $fileSelection = true;
    public $data = [];
    public $addedFields = [];
    public $addedSubmits = [];

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setButtonTitle($title)
    {
        $this->buttonTitle = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setfileSelection($status = true)
    {
        $this->fileSelection = $status;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function addField($field)
    {
        $this->addedFields[] = $field;
    }

    public function addSubmit($submits)
    {
        $this->addedSubmits[] = $submits;
    }

    public function get()
    {

        $s = '';

        $panel = '';
        $panel .= '<fieldset>';
        $formElements = [];

        $status = $this->data['status'] ?? false;

        $e = [];
        $e['label'] = '<input class="form-control" type="checkbox" id="rex-mediapool-status" name="rex_media_status" value="1" ' . ($status ? 'checked="checked"' : '') . '" />';
        $e['field'] = '<label for="rex-media-status">' . rex_i18n::msg('pool_file_status') . '</label>';
        $formElements[] = $e;

        $cats_sel = new rex_media_category_select();
        $cats_sel->setStyle('class="form-control"');
        $cats_sel->setSize(1);
        $cats_sel->setMultiple();
        $cats_sel->setName('rex_media_categories[]');
        $cats_sel->setId('rex-mediapool-category');
        $cats_sel->setAttribute('class', 'selectpicker form-control');
        $cats_sel->setAttribute('data-live-search', 'true');


        $categories = $this->data['categories'] ?? [];
        foreach ($categories as $category) {
            $cats_sel->setSelected($category);
        }

        $e = [];
        $e['label'] = '<label for="rex-media-categories">' . rex_i18n::msg('pool_media_categories') . '</label>';
        $e['field'] = $cats_sel->get();
        $formElements[] = $e;

        $tags = $this->data['tags'] ?? '';

        $e = [];
        $e['label'] = '<label for="rex-media-tags">' . rex_i18n::msg('pool_media_tags') . '</label>';
        $e['field'] = '<input type="text" name="rex_media_tags" value="'.rex_escape($tags).'" />';
        $formElements[] = $e;

        $title = $this->data['title'] ?? '';

        $e = [];
        $e['label'] = '<label for="rex-media-title">' . rex_i18n::msg('pool_file_title') . '</label>';
        $e['field'] = '<input class="form-control" type="text" id="rex-mediapool-title" name="rex_media_title" value="' . rex_escape($title) . '" />';
        $formElements[] = $e;

        foreach($this->addedFields as $add_field) {
            $e = [];
            $e['label'] = $add_field['label'];
            $e['field'] = $add_field['field'];
            $formElements[] = $e;
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $panel .= $fragment->parse('core/form/form.php');

        $panel .= rex_extension::registerPoint(new rex_extension_point('MEDIA_FORM_ADD', ''));

        if ($this->fileSelection) {
            $e = [];
            $e['label'] = '<label for="rex-media-choose-file">' . rex_i18n::msg('pool_file_file') . '</label>';
            $e['field'] = '<input id="rex-media-choose-file" type="file" name="rex_media_file" />';
            $e['after'] = '<h3>' . rex_i18n::msg('phpini_settings') . '</h3>
                        <dl class="dl-horizontal text-left">
                        ' . ((0 == rex_ini_get('file_uploads')) ? '<dt><span class="text-warning">' . rex_i18n::msg('pool_upload') . '</span></dt><dd><span class="text-warning">' . rex_i18n::msg('pool_upload_disabled') . '</span></dd>' : '') . '
                            <dt>' . rex_i18n::msg('pool_max_uploadsize') . ':</dt><dd>' . rex_formatter::bytes(rex_ini_get('upload_max_filesize')) . '</dd>
                            <dt>' . rex_i18n::msg('pool_max_uploadtime') . ':</dt><dd>' . rex_ini_get('max_input_time') . 's</dd>
                        </dl>';

            $fragment = new rex_fragment();
            $fragment->setVar('elements', [$e], false);
            $panel .= $fragment->parse('core/form/form.php');
        }
        $panel .= '</fieldset>';

        $formElements = [];

        $e = [];
        $e['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="' . $this->buttonTitle . '"' . rex::getAccesskey($this->buttonTitle, 'save') . '>' . $this->buttonTitle . '</button>';
        $formElements[] = $e;

        foreach($this->addedSubmits as $add_submit) {
            $e = [];
            $e['field'] = $add_submit;
            $formElements[] = $e;
        }

        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $buttons = $fragment->parse('core/form/submit.php');

        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit', false);
        $fragment->setVar('title', $this->title, false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('buttons', $buttons, false);
        $content = $fragment->parse('core/page/section.php');

        $s .= ' <form action="' . rex_url::currentBackendPage() . '" method="post" enctype="multipart/form-data" data-pjax="false">
                <fieldset>
                    ' . $content . '
                </fieldset>
            ';

        $s .= '</form>' . "\n";

        return $s;
    }
}
