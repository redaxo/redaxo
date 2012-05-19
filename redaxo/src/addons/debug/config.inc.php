<?php

// CHECK ADMIN SESSION
////////////////////////////////////////////////////////////////////////////////
$user = rex::getUser();
$loggedIn = rex_backend_login::hasSession();

if($loggedIn && !$user)
{
  $login = new rex_backend_login;
  if($login->checkLogin())
  {
    $user = $login->getUser();
    rex::setProperty('user', $user);
  }
}

if(isset($user) && is_a($user,'rex_user'))
{
  if($user->isAdmin())
  {
    // FIREPHP SETTINGS CUSTOM/DEFAULT
    $maxObjectDepth = $this->getConfig('firephp_settings')!='custom' ? 5  : $this->getConfig('maxObjectDepth');
    $maxArrayDepth  = $this->getConfig('firephp_settings')!='custom' ? 5  : $this->getConfig('maxArrayDepth');
    $maxDepth       = $this->getConfig('firephp_settings')!='custom' ? 10 : $this->getConfig('maxDepth');

    $options = array(
      'maxObjectDepth'      => $maxObjectDepth, // default: 5
      'maxArrayDepth'       => $maxArrayDepth,  // default: 5
      'maxDepth'            => $maxDepth,       // default: 10
      'useNativeJsonEncode' => true,            // default: true
      'includeLineNumbers'  => true,            // default: true
      );

    // INIT FIREPHP
    $firephp = FirePHP::getInstance(true);
    $firephp->setEnabled(true);
    $firephp->setOptions($options);

    // ENABLE/DISABLE LOGS
    if($this->getConfig('sql_log')==1) {
      rex_sql::setFactoryClass('rex_sql_debug');
    }

    if($this->getConfig('ep_log')==1) {
      rex_extension::setFactoryClass('rex_extension_debug');
    }

    if($this->getConfig('api_log')==1) {
      rex_api_function::setFactoryClass('rex_api_function_debug');
    }

    rex_logger::setFactoryClass('rex_logger_debug');
  }
}
else
{
  // CATCH FIREPHP CALLS, SUPPRES OUTPUT
  $firephp = FirePHP::getInstance(true);
  $firephp->setEnabled(false);
}

// EXTENSION TESTS
////////////////////////////////////////////////////////////////////////////////
#$blubb = rex_extension::registerPoint('FOO_BAR',array());
#
#// LATE EXTENSION: callable -> declared function
#rex_extension::register('FOO_BAR','bar');
#function bar($params)
#{
#  $params['subject'][] ='declared function: haha.. i actually made it!';
#  return $params['subject'];
#}
#
#// LATE EXTENSION: callable -> create_function
#rex_extension::register('FOO_BAR',create_function('$params', '$params["subject"][] = "create_function: haha.. i actually made it!";return $params["subject"];'));
#
#// LATE EXTENSION: callable -> class::method
#rex_extension::register('FOO_BAR',array('Foo','bar'));
#class Foo
#{
#  static public function bar($params)
#  {
#    $params['subject'][] = 'class::method : haha.. i actually made it!';
#    return $params['subject'];
#  }
#} // END class
#
#// LATE EXTENSION: callable -> anon function
#rex_extension::register('FOO_BAR',
#  function($params)
#  {
#    $params['subject'][] ='anon function: haha.. i actually made it!';
#    return $params['subject'];
#  }
#);
#
#// RE-REGISTER EP -> SHOULD CLEAR LATE EXT FROM BEFORE
#$blubb = rex_extension::registerPoint('FOO_BAR',array());



// SQL TESTS
////////////////////////////////////////////////////////////////////////////////

// BROKEN SQL SETQUER
#$db = rex_sql::factory();
#$db->setQuery('setQuery BOOM!');

#// BROKEN EXECUTE SQL
#$db = rex_sql::factory();
#$db->setTable('Tisch');
#$db->setValue('Feld','Wert');
#$db->setWhere(' hier und dort..');
#$db->addGlobalUpdateFields();
#$db->update();

// SLOW QUERY
#$db = rex_sql::factory();
#$db->setQuery('select benchmark(670000, \'foo\' = \'foo\');');
