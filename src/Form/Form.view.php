<?php

use Redaxo\Core\Form\Form;
use Redaxo\Core\View\Message;

use function Redaxo\Core\View\escape;

return static function (Form $form): void { ?>
    <?= $form->open() ?>
        <section class="rex-page-section">
            <div class="panel panel-edit">
                <?php if (null !== $form->title): ?>
                    <header class="panel-heading"><div class="panel-title"><?= escape((string) $form->title) ?></div></header>
                <?php endif ?>
                <div class="panel-body">
                    <?php foreach ($form->getFormErrors() as $error): ?>
                        <?= Message::error(escape((string) $error)) ?>
                    <?php endforeach ?>
                    <?= $form->rest() ?>
                </div>
                <footer class="panel-footer">
                    <div class="rex-form-panel-footer">
                        <div class="btn-toolbar"><?= $form->submitButton() ?></div>
                    </div>
                </footer>
            </div>
        </section>
    <?= $form->close() ?>
<?php };
