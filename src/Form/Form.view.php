<?php

use Redaxo\Core\Form\Form;
use Redaxo\Core\View\Html;
use Redaxo\Core\View\Message;

return static function (Form $form): void { ?>
    <?= $form->open() ?>
        <section class="rex-page-section">
            <div class="panel panel-edit">
                <?php if (null !== $form->title): ?>
                    <header class="panel-heading"><div class="panel-title"><?= Html::from($form->title) ?></div></header>
                <?php endif ?>
                <div class="panel-body">
                    <?php foreach ($form->getFormErrors() as $error): ?>
                        <?= Message::error((string) Html::from($error)) ?>
                    <?php endforeach ?>
                    <?= $form->rest() ?>
                </div>
                <footer class="panel-footer">
                    <div class="rex-form-panel-footer">
                        <div class="btn-toolbar"><?= $form->submitButton() ?><?= $form->applyButton() ?><?= $form->cancelLink() ?></div>
                    </div>
                </footer>
            </div>
        </section>
    <?= $form->close() ?>
<?php };
