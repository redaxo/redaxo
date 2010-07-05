<?php

/**
 * Addon Framework Classes
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4
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
$smtpauth_default = "false";
if($testMailer->SMTPAuth) 
  $smtpauth_default = "true";
$smtpauth = rex_post('smtpauth', 'string', $smtpauth_default);
$priority = rex_post('priority', 'int', $testMailer->Priority);

if($smtpauth != "true") 
  $smtpauth = "false";

$message = '';

if (rex_post('btn_save', 'string') != '')
{
  $file = $REX['INCLUDE_PATH'] .'/addons/phpmailer/classes/class.rex_mailer.inc.php';
  $message = rex_is_writable($file);

  if($message === true)
  {
    $message  = $I18N->msg('phpmailer_config_saved_error');

    if($file_content = rex_get_file_contents($file))
    {
      $template =
      "// --- DYN
      \$this->From             = '". $from ."';
      \$this->FromName         = '". $fromname ."';
      \$this->ConfirmReadingTo = '". $confirmto ."';
      \$this->Mailer           = '". $mailer ."';
      \$this->Host             = '". $host ."';
      \$this->CharSet          = '". $charset ."';
      \$this->WordWrap         = ". $wordwrap .";
      \$this->Encoding         = '". $encoding ."';
      \$this->Priority         = ". $priority .";
      \$this->SMTPAuth         = ". $smtpauth .";
      \$this->Username         = '". $Username ."';
      \$this->Password         = '". $Password."';
      // --- /DYN";

      $file_content = ereg_replace("(\/\/.---.DYN.*\/\/.---.\/DYN)", $template, $file_content);

      if(rex_put_file_contents($file, $file_content) !== false)
      {
        $message = $I18N->msg('phpmailer_config_saved_successful');
      }
    }
  }
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
foreach(array('false', 'true') as $type)
$sel_smtpauth->addOption($type,$type); 

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
foreach(array(1 =>$I18N->msg('phpmailer_high'),3 => $I18N->msg('phpmailer_normal'),5 => $I18N->msg('phpmailer_low')) as $no => $name)
  $sel_priority->addOption($name,$no);


if($message != '')
  echo rex_info($message);

?>

<div class="rex-addon-output">
<h2 class="rex-hl2"><?php echo $I18N->msg('phpmailer_config_settings'); ?></h2>

<div id="rex-addon-editmode" class="rex-form">
  <form action="" method="post">

     <fieldset class="rex-form-col-1">

    <div class="rex-form-wrapper">
    
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="fromname"><?php echo $I18N->msg('phpmailer_sender_name'); ?></label>
      <input type="text" name="fromname" id="fromname" value="<?php echo $fromname ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="from"><?php echo $I18N->msg('phpmailer_sender_email'); ?></label>
      <input type="text" name="from" id="from" value="<?php echo $from ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="confirmto"><?php echo $I18N->msg('phpmailer_confirm'); ?></label>
      <input type="text" name="confirmto" id="confirmto" value="<?php echo $confirmto ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-select">
      <label for="mailer"><?php echo $I18N->msg('phpmailer_mailertype'); ?></label>
      <?php echo $sel_mailer->show(); ?>
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="host"><?php echo $I18N->msg('phpmailer_host'); ?></label>
      <input type="text" name="host" id="host" value="<?php echo $host ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="charset"><?php echo $I18N->msg('phpmailer_charset'); ?></label>
      <input type="text" name="charset" id="charset" value="<?php echo $charset ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="wordwrap"><?php echo $I18N->msg('phpmailer_wordwrap'); ?></label>
      <input type="text" name="wordwrap" id="wordwrap" value="<?php echo $wordwrap ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-select">
      <label for="encoding"><?php echo $I18N->msg('phpmailer_encoding'); ?></label>
      <?php echo $sel_encoding->show(); ?>
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-select">
      <label for="priority"><?php echo $I18N->msg('phpmailer_priority'); ?></label>
      <?php echo $sel_priority->show(); ?>
    </p>
    </div>
    <div class="rex-form-row">
      <p class="rex-form-col-a rex-form-select">
          <label for="smtpauth"><?php echo $I18N->msg('phpmailer_SMTPAuth'); ?></label>
          <?php echo $sel_smtpauth->show(); ?>
      </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="Username"><?php echo $I18N->msg('phpmailer_Username'); ?></label>
      <input type="text" name="Username" id="Username" value="<?php echo $Username ?>" />
    </p>
    </div>
    <div class="rex-form-row">
    <p class="rex-form-col-a rex-form-text">
      <label for="Password"><?php echo $I18N->msg('phpmailer_Password'); ?></label>
      <input type="text" name="Password" id="Password" value="<?php echo $Password ?>" />
    </p>
    </div>
    
    <div class="rex-form-row">
      <p class="rex-form-col-a rex-form-submit">
         <input class="rex-form-submit" type="submit" name="btn_save" value="<?php echo $I18N->msg('phpmailer_save'); ?>" />
         <input class="rex-form-submit rex-form-submit-2" type="reset" name="btn_reset" value="<?php echo $I18N->msg('phpmailer_reset'); ?>" onclick="return confirm('<?php echo $I18N->msg('phpmailer_reset_info'); ?>');"/>
      </p>
    </div>
  
    </div>

     </fieldset>
  </form>
</div>

</div>
