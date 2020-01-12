<?php

/**
 * @package redaxo\structure\content
 */
class rex_article_action
{
    public const PREVIEW = 'preview';
    public const PRESAVE = 'presave';
    public const POSTSAVE = 'postsave';

    private $moduleId;
    private $event;
    private $mode;
    private $save = true;
    private $messages = [];
    private $sql;
    private $vars;

    public function __construct($moduleId, $function, rex_sql $sql)
    {
        $this->moduleId = $moduleId;
        $this->event = $function;
        if ('edit' == $function) {
            $this->mode = 2;
        } elseif ('delete' == $function) {
            $this->mode = 4;
        } else {
            $this->mode = 1;
        }
        $this->sql = $sql;
        $this->vars['search'] = ['REX_ARTICLE_ID', 'REX_CLANG_ID', 'REX_CTYPE_ID', 'REX_MODULE_ID', 'REX_SLICE_ID'];
        $this->vars['replace'] = [
            rex_request('article_id', 'int'),
            rex_request('clang', 'int'),
            rex_request('ctype', 'int'),
            rex_request('module_id', 'int'),
            1 == $this->mode ? 0 : rex_request('slice_id', 'int'),
        ];
    }

    public function setRequestValues()
    {
        $request = ['value' => 20, 'media' => 10, 'medialist' => 10, 'link' => 10, 'linklist' => 10];
        foreach ($request as $key => $max) {
            $values = rex_request('REX_INPUT_' . strtoupper($key), 'array');
            for ($i = 1; $i <= $max; ++$i) {
                if (isset($values[$i])) {
                    if (is_array($values[$i])) {
                        $this->sql->setArrayValue($key . $i, $values[$i]);
                    } else {
                        $this->sql->setValue($key . $i, $values[$i]);
                    }
                } else {
                    $this->sql->setValue($key . $i, null);
                }
            }
        }
    }

    public function exec($type)
    {
        if (!in_array($type, [self::PREVIEW, self::PRESAVE, self::POSTSAVE])) {
            throw new InvalidArgumentException('$type musst be rex_article_action::PREVIEW, ::PRESAVE or ::POSTSAVE');
        }

        $this->messages = [];
        $this->save = true;

        $ga = rex_sql::factory();
        $ga->setQuery('SELECT a.id, `' . $type . '` as code FROM ' . rex::getTable('module_action') . ' ma,' . rex::getTable('action') . ' a WHERE `' . $type . '` != "" AND ma.action_id=a.id AND module_id=? AND (a.' . $type . 'mode & ?)', [$this->moduleId, $this->mode]);

        foreach ($ga as $row) {
            $action = $row->getValue('code');
            $action = str_replace($this->vars['search'], $this->vars['replace'], $action);
            $action = rex_var::parse($action, rex_var::ENV_BACKEND | rex_var::ENV_INPUT, 'action', $this->sql);
            require rex_stream::factory('action/' . $row->getValue('id') . '/' . $type, $action);
        }
    }

    protected function setSave($save)
    {
        $this->save = $save;
    }

    protected function addMessage($message)
    {
        $this->messages[] = $message;
    }

    public function getSave()
    {
        return $this->save;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getEvent()
    {
        return $this->event;
    }

    protected function setValue($id, $value)
    {
        if ($id < 1 || $id > 20) {
            throw new InvalidArgumentException('ID for REX_VALUE out of range (1..20)');
        }
        $this->sql->setValue('value' . $id, $value);
    }

    protected function setMedia($id, $value)
    {
        if ($id < 1 || $id > 10) {
            throw new InvalidArgumentException('ID for REX_MEDIA out of range (1..10)');
        }
        $this->sql->setValue('media' . $id, $value);
    }

    protected function setMediaList($id, $value)
    {
        if ($id < 1 || $id > 10) {
            throw new InvalidArgumentException('ID for REX_MEDIALIST out of range (1..10)');
        }
        $this->sql->setValue('medialist' . $id, $value);
    }

    protected function setLink($id, $value)
    {
        if ($id < 1 || $id > 10) {
            throw new InvalidArgumentException('ID for REX_LINK out of range (1..10)');
        }
        $this->sql->setValue('link' . $id, $value);
    }

    protected function setLinkList($id, $value)
    {
        if ($id < 1 || $id > 10) {
            throw new InvalidArgumentException('ID for REX_LINKLIST out of range (1..10)');
        }
        $this->sql->setValue('linklist' . $id, $value);
    }

    protected function getValue($id)
    {
        return $this->sql->getValue('value' . $id);
    }

    protected function getMedia($id)
    {
        return $this->sql->getValue('media' . $id);
    }

    protected function getMediaList($id)
    {
        return $this->sql->getValue('medialist' . $id);
    }

    protected function getLink($id)
    {
        return $this->sql->getValue('link' . $id);
    }

    protected function getLinkList($id)
    {
        return $this->sql->getValue('linklist' . $id);
    }
}
