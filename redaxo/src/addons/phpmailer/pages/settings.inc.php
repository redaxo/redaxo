<?php

/**
 * Addon Framework Classes
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @package redaxo5
 * @version $Id class.rex_form.inc.php,v 1.3 2006/09/07 104351 kills Exp $
 */

$testMailer = new rex_mailer();

$fromname = rex_post('fromname', 'string', $testMailer->FromName);
$from = rex_post('from', 'string', $testMailer->From);
$confirmto = rex_post('confirmto', 'string', $testMailer->ConfirmReadingTo);
$mailer = rex_post('mailer', 'string', $testMailer->Mailer);
$host = rex_post('host', 'string', $testMailer->Host);
$charset = rex_post('charset', 'string', $testMailer->CharSet);
$wordwrap = rex_post('wordwrap', 'int', $testMailer->WordWrap);
$encoding = rex_post('encoding', 'string', $testMailer->Encoding);
$Password = rex_post('Password', 'string', $testMailer->Password);
$Username = rex_post('Username', 'string', $testMailer->Username);
$smtpauth = rex_post('smtpauth', 'boolean', $testMailer->SMTPAuth);
$priority = rex_post('priority', 'int', $testMailer->Priority);

$message = '';

if (rex_post('btn_save', 'string') != '')
{
  $this->setConfig('from',     $from);
  $this->setConfig('fromname', $fromname);
  $this->setConfig('confirmto', $confirmto);
  $this->setConfig('mailer',   $mailer);
  $this->setConfig('host',     $host);
  $this->setConfig('charset',  $charset);
  $this->setConfig('wordwrap', $wordwrap);
  $this->setConfig('encoding', $encoding);
  $this->setConfig('priority', $priority);
  $this->setConfig('smtpauth', $smtpauth);
  $this->setConfig('username', $Username);
  $this->setConfig('password', $Password);

  $message = $this->i18n('config_saved_successful');
}

$sel_mailer = new rex_select();
$sel_mailer->setId('mailer');
$sel_mailer->setName('mailer');
$sel_mailer->setSize(1);
$sel_mailer->setSelected($mailer);
foreach(array('mail', 'sendmail', 'smtp') as $type)
  $sel_mailer->addOption($type,$type);

$sel_smtpauth = new rex_select();
$sel_smtpauth->setId('smtpauth');
$sel_smtpauth->setName('smtpauth');
$sel_smtpauth->setSize(1);
$sel_smtpauth->setSelected($smtpauth);
foreach(array(0 => 'false', 1 => 'true') as $i => $type)
$sel_smtpauth->addOption($type,$i);

$sel_encoding = new rex_select();
$sel_encoding->setId('encoding');
$sel_encoding->setName('encoding');
$sel_encoding->setSize(1);
$sel_encoding->setSelected($encoding);
foreach(array('7bit', '8bit', 'binary', 'base64', 'quoted-printable') as $enc)
  $sel_encoding->addOption($enc,$enc);

$sel_priority = new rex_select();
$sel_priority->setid('priority');
$sel_priority->setName('priority');
$sel_priority->setSize(1);
$sel_priority->setSelected($priority);
foreach(array(1 =>$this->i18n('high'),3 => $this->i18n('normal'),5 => $this->i18n('low')) as $no => $name)
  $sel_priority->addOption($name,$no);


if($message != '')
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
      <input type="text" name="fromname" id="fromname" value="<?php echo $fromname ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="from"><?php echo $this->i18n('sender_email'); ?></label>
      <input type="text" name="from" id="from" value="<?php echo $from ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="confirmto"><?php echo $this->i18n('confirm'); ?></label>
      <input type="text" name="confirmto" id="confirmto" value="<?php echo $confirmto ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-select">
      <label for="mailer"><?php echo $this->i18n('mailertype'); ?></label>
      <?php echo $sel_mailer->show(); ?>
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="host"><?php echo $this->i18n('host'); ?></label>
      <input type="text" name="host" id="host" value="<?php echo $host ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="charset"><?php echo $this->i18n('charset'); ?></label>
      <input type="text" name="charset" id="charset" value="<?php echo $charset ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="wordwrap"><?php echo $this->i18n('wordwrap'); ?></label>
      <input type="text" name="wordwrap" id="wordwrap" value="<?php echo $wordwrap ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-select">
      <label for="encoding"><?php echo $this->i18n('encoding'); ?></label>
      <?php echo $sel_encoding->show(); ?>
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-select">
      <label for="priority"><?php echo $this->i18n('priority'); ?></label>
      <?php echo $sel_priority->show(); ?>
    </p>
    </div>
    <div class="rex-form-row">
      <p class="rex-form-col-a rex-form-select">
          <label for="smtpauth"><?php echo $this->i18n('SMTPAuth'); ?></label>
          <?php echo $sel_smtpauth->show(); ?>
      </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="Username"><?php echo $this->i18n('Username'); ?></label>
      <input type="text" name="Username" id="Username" value="<?php echo $Username ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="Password"><?php echo $this->i18n('Password'); ?></label>
      <input type="text" name="Password" id="Password" value="<?php echo $Password ?>" />
    </p>
    </div>

    <div class="rex-form-row">
      <p class="rex-form-col-a rex-form-submit">
         <input class="rex-form-submit" type="submit" name="btn_save" value="<?php echo $this->i18n('save'); ?>" />
         <input class="rex-form-submit rex-form-submit-2" type="reset" name="btn_reset" value="<?php echo $this->i18n('reset'); ?>" onclick="return confirm('<?php echo $this->i18n('reset_info'); ?>');"/>
      </p>
    </div>

    </div>

     </fieldset>
  </form>
</div>

</div>
