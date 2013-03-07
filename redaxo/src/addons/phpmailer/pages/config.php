<?php

/**
 * Addon Framework Classes
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5
 *
 * @var rex_addon $this
 */

$message = '';

if (rex_post('btn_save', 'string') != '') {
    $this->setConfig(rex_post('settings', [
        ['fromname', 'string'],
        ['from', 'string'],
        ['confirmto', 'string'],
        ['bcc', 'string'],
        ['mailer', 'string'],
        ['host', 'string'],
        ['charset', 'string'],
        ['wordwrap', 'int'],
        ['encoding', 'string'],
        ['username', 'string'],
        ['password', 'string'],
        ['smtpauth', 'boolean'],
        ['priority', 'int']
    ]));

    $message = $this->i18n('config_saved_successful');
}

$sel_mailer = new rex_select();
$sel_mailer->setId('mailer');
$sel_mailer->setName('settings[mailer]');
$sel_mailer->setSize(1);
$sel_mailer->setSelected($this->getConfig('mailer'));
foreach (['mail', 'sendmail', 'smtp'] as $type)
    $sel_mailer->addOption($type, $type);

$sel_smtpauth = new rex_select();
$sel_smtpauth->setId('smtpauth');
$sel_smtpauth->setName('settings[smtpauth]');
$sel_smtpauth->setSize(1);
$sel_smtpauth->setSelected($this->getConfig('smtpauth'));
foreach ([0 => 'false', 1 => 'true'] as $i => $type)
$sel_smtpauth->addOption($type, $i);

$sel_encoding = new rex_select();
$sel_encoding->setId('encoding');
$sel_encoding->setName('settings[encoding]');
$sel_encoding->setSize(1);
$sel_encoding->setSelected($this->getConfig('encoding'));
foreach (['7bit', '8bit', 'binary', 'base64', 'quoted-printable'] as $enc)
    $sel_encoding->addOption($enc, $enc);

$sel_priority = new rex_select();
$sel_priority->setid('priority');
$sel_priority->setName('settings[priority]');
$sel_priority->setSize(1);
$sel_priority->setSelected($this->getConfig('priority'));
foreach ([1 => $this->i18n('high'), 3 => $this->i18n('normal'), 5 => $this->i18n('low')] as $no => $name)
    $sel_priority->addOption($name, $no);


if ($message != '')
    echo rex_view::info($message);

?>

<div class="rex-addon-output">
<h2 class="rex-hl2"><?php echo $this->i18n('config_settings'); ?></h2>

<div id="rex-addon-editmode" class="rex-form">
    <form action="" method="post">

         <fieldset class="rex-form-col-1">

        <div class="rex-form-wrapper">

        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="fromname"><?php echo $this->i18n('sender_name'); ?></label>
            <input type="text" name="settings[fromname]" id="fromname" value="<?php echo $this->getConfig('fromname') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="from"><?php echo $this->i18n('sender_email'); ?></label>
            <input type="text" name="settings[from]" id="from" value="<?php echo $this->getConfig('from') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="confirmto"><?php echo $this->i18n('confirm'); ?></label>
            <input type="text" name="settings[confirmto]" id="confirmto" value="<?php echo $this->getConfig('confirmto') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="bcc"><?php echo $this->i18n('bcc'); ?></label>
            <input type="text" name="settings[bcc]" id="bcc" value="<?php echo $this->getConfig('bcc') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-select">
            <label for="mailer"><?php echo $this->i18n('mailertype'); ?></label>
            <?php $sel_mailer->show(); ?>
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="host"><?php echo $this->i18n('host'); ?></label>
            <input type="text" name="settings[host]" id="host" value="<?php echo $this->getConfig('host') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="charset"><?php echo $this->i18n('charset'); ?></label>
            <input type="text" name="settings[charset]" id="charset" value="<?php echo $this->getConfig('charset') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="wordwrap"><?php echo $this->i18n('wordwrap'); ?></label>
            <input type="text" name="settings[wordwrap]" id="wordwrap" value="<?php echo $this->getConfig('wordwrap') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-select">
            <label for="encoding"><?php echo $this->i18n('encoding'); ?></label>
            <?php $sel_encoding->show(); ?>
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-select">
            <label for="priority"><?php echo $this->i18n('priority'); ?></label>
            <?php $sel_priority->show(); ?>
        </p>
        </div>
        <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-select">
                    <label for="smtpauth"><?php echo $this->i18n('SMTPAuth'); ?></label>
                    <?php $sel_smtpauth->show(); ?>
            </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="Username"><?php echo $this->i18n('Username'); ?></label>
            <input type="text" name="settings[username]" id="Username" value="<?php echo $this->getConfig('username') ?>" />
        </p>
        </div>
        <div class="rex-form-row">
        <p class="rex-form-col-a rex-form-text">
            <label for="Password"><?php echo $this->i18n('Password'); ?></label>
            <input type="text" name="settings[password]" id="Password" value="<?php echo $this->getConfig('password') ?>" />
        </p>
        </div>

        <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-submit">
                 <input class="rex-form-submit" type="submit" name="btn_save" value="<?php echo $this->i18n('save'); ?>" />
                 <input class="rex-form-submit rex-form-submit-2" type="reset" name="btn_reset" value="<?php echo $this->i18n('reset'); ?>" data-confirm="<?php echo $this->i18n('reset_info'); ?>"/>
            </p>
        </div>

        </div>

         </fieldset>
    </form>
</div>

</div>
