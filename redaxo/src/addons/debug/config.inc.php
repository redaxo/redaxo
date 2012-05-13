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

                                                                                #FB::log($user,__CLASS__.'::'.__FUNCTION__.' $user');
                                                                                #FB::log($loggedIn,__CLASS__.'::'.__FUNCTION__.' $loggedIn');
if(isset($user) && is_a($user,'rex_user'))
{
  if($user->isAdmin())
  {
    // FIREPHP SETTINGS
    $options = array(
      'maxObjectDepth'      => $this->getConfig('firephp_maxdepth'), // default: 5
      'maxArrayDepth'       => $this->getConfig('firephp_maxdepth'), // default: 5
      'maxDepth'            => $this->getConfig('firephp_maxdepth'), // default: 10
      'useNativeJsonEncode' => true,                                 // default: true
      'includeLineNumbers'  => true,                                 // default: true
      );                                                                        #FB::log($options,__CLASS__.'::'.__FUNCTION__.' $options');

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
