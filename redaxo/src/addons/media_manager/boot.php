<?php

/**
 * media_manager Addon.
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 */

rex_extension::register('PACKAGES_INCLUDED', [rex_media_manager::class, 'init'], rex_extension::EARLY);

if (rex::isBackend()) {
    // delete thumbnails on mediapool changes
    rex_extension::register('MEDIA_UPDATED', [rex_media_manager::class, 'mediaUpdated']);
    rex_extension::register('MEDIA_DELETED', [rex_media_manager::class, 'mediaUpdated']);
   	rex_extension::register('MEDIA_IS_IN_USE', 'rex_media_manager_media_is_in_use');
}

/**
 * Checks if media is used by this addon
 * @param rex_extension_point $ep Redaxo extension point
 * @return string[] Warning message as array
 */
function rex_media_manager_media_is_in_use(rex_extension_point $ep) {
	$warning = $ep->getSubject();
	$params = $ep->getParams();
	$filename = addslashes($params['filename']);

	if($filename) {
		$sql = \rex_sql::factory();
		$query = 'SELECT DISTINCT effect.id AS effect_id, effect.type_id, type.id, type.name FROM `' . rex::getTablePrefix() . 'media_manager_type_effect` AS effect '
			.'LEFT JOIN `' . rex::getTablePrefix() . 'media_manager_type`AS type ON effect.type_id = type.id '
			.'WHERE parameters REGEXP ' . $sql->escape('(^|[^[:alnum:]+_-])'. $filename);
		$sql->setQuery($query);

		// Prepare warnings
		for($i = 0; $i < $sql->getRows(); $i++) {
			$message = '<a href="javascript:openPage(\''. rex_url::backendPage('media_manager/types', ['effects' => 1, 'type_id' => $sql->getValue('type_id'), 'effect_id' => $sql->getValue('effect_id'), 'func' => 'edit']) .'\')">'. rex_i18n::msg('media_manager') .' '. rex_i18n::msg('media_manager_effect_name') .': '. $sql->getValue('name') .'</a>';
			if(!in_array($message, $warning)) {
				$warning[] = $message;
			}
		}
	}

	return $warning;
}
