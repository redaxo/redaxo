<?php

use Redaxo\Core\Form\Form;
use Redaxo\Core\Form\Storage\ConfigStorage;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Message;
use Redaxo\Test\FormFields;

$data = new FormFields();
$storage = new ConfigStorage('test_form');
$storage->load($data);

$form = new Form($data, title: 'Form fields');

if ($form->handleRequest()) {
    $storage->save($data);
    echo Message::success(I18n::msg('form_saved'));
}

echo $form->render();
