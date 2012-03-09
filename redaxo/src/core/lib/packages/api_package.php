<?php

class rex_api_package extends rex_api_function
{
  public function execute()
  {
    $packageId = rex_request('package', 'string');
    $package = rex_package::get($packageId);
    if($package instanceof rex_null_package)
    {
      throw new rex_api_exception('Package "'.$packageId.'" doesn\'t exists!');
    }
    $function = rex_request('function', 'string');
    if(!in_array($function, array('install', 'uninstall', 'activate', 'deactivate', 'delete')))
    {
      throw new rex_api_exception('Unknown package function "'.$function.'"!');
    }
    $manager = rex_package_manager::factory($package);
    $success = $manager->$function();
    $message = $manager->getMessage();
    $result = new rex_api_result($success, $message);
    return $result;
  }

  // TODO remove from this class since no longer available for the api itself
  static public function getTableRow(rex_package $package, $hide = false)
  {
    $packageId = $package->getPackageId();
    $type = $package instanceof rex_plugin ? 'plugin' : 'addon';

    $delete = $package->isSystemPackage() ? rex_i18n::msg($type.'_system'.$type) : self::getLink($package, 'delete', true);

    if ($package->isInstalled())
    {
      $install = rex_i18n::msg("addon_yes").' - '. self::getLink($package, 'install', false, 'reinstall');
      if($type == 'addon' && count($package->getInstalledPlugins()) > 0)
      {
        $uninstall = rex_i18n::msg("plugin_plugins_installed");
        $delete = rex_i18n::msg("plugin_plugins_installed");
      }
      else
      {
        $uninstall = self::getLink($package, 'uninstall', true);
      }
    }
    else
    {
      $install = rex_i18n::msg("addon_no").' - '.self::getLink($package, 'install');
      $uninstall = rex_i18n::msg("addon_notinstalled");
    }

    if($package->isActivated())
    {
      $status = rex_i18n::msg("addon_yes").' - '.self::getLink($package, 'deactivate');
    }
    elseif($package->isInstalled())
    {
      $status = rex_i18n::msg("addon_no").' - '.self::getLink($package, 'activate');
    }
    else
    {
      $status = rex_i18n::msg("addon_notinstalled");
    }
    $hide = $hide ? ' style="display:none"' : '';
    $name = htmlspecialchars($package->getName());
    $class = str_replace(array('.', '/'), '_', $packageId);
    return '
        <tr class="rex-'.$type.' rex-'.$type.'-'.$class.'"'.$hide.'>
          <td class="rex-icon"><span class="rex-ic-'.$type.'">'. $name .'</span></td>
          <td class="rex-name">'.$name.' '. $package->getVersion() .' [<a href="index.php?page=addon&amp;subpage=help&amp;package='.$packageId.'">?</a>]</td>
          <td class="rex-install">'.$install.'</td>
          <td class="rex-active">'.$status.'</td>
          <td class="rex-uninstall">'.$uninstall.'</td>
          <td class="rex-delete">'.$delete.'</td>
        </tr>'."\n   ";
  }

  // TODO remove from this class since no longer available for the api itself
  static private function getLink(rex_package $package, $function, $confirm = false, $key = null)
  {
    $onclick = '';
    if($confirm)
    {
      $type = $package instanceof rex_plugin ? 'plugin' : 'addon';
      $onclick = ' onclick="return confirm(\''.htmlspecialchars(rex_i18n::msg($type.'_'.$function.'_question', $package->getName())).'\')"';
    }
    $text = rex_i18n::msg('addon_'.($key ?: $function));
    return '<a href="index.php?page=addon&amp;package='.$package->getPackageId().'&amp;rex-api-call=package&amp;function='.$function.'"'.$onclick.'>'.$text.'</a>';
  }
}
