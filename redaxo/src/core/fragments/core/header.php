<?php
/**
 * @var rex_fragment $this
 * @psalm-scope-this rex_fragment
 */
?>

        <div id="rex-js-nav-top" class="rex-nav-top" data-pjax-container>
            <nav class="navbar navbar-default">
                <div class="container-fluid">

                    <?php if ('login' != rex_be_controller::getCurrentPage() && !rex_be_controller::getCurrentPageObject()->isPopup()): ?>
                        <button type="button" class="navbar-toggle" id="rex-js-nav-main-toggle">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bars">
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </span>
                        </button>
                    <?php endif; ?>

                    <div class="navbar-header">
                        <?php if (rex_be_controller::getCurrentPageObject()->isPopup()): ?>
                            <span class="navbar-brand"><?= rex_file::get(rex_path::coreAssets('redaxo-logo.svg')) ?></span>
                        <?php else: ?>
                            <a class="navbar-brand" href="<?= rex_url::backendController() ?>"><?= rex_file::get(rex_path::coreAssets('redaxo-logo.svg')) ?></a>
                        <?php endif; ?>
                        <?php if (!rex_be_controller::getCurrentPageObject()->isPopup() && rex::getUser() && rex::getUser()->isAdmin() && rex::isDebugMode()): ?>
                            <a class="rex-marker-debugmode" href="<?= rex_url::backendPage('system/settings') ?>" title="<?= rex_i18n::msg('debug_mode_marker') ?>">
                                <i class="rex-icon rex-icon-heartbeat rex-pulse"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?= $this->meta_navigation ?>

                </div>
            </nav>

            <div class="rex-ajax-loader" id="rex-js-ajax-loader">
                <div class="rex-ajax-loader-element"></div>
            </div>
        </div>
