<?php
/**
 * media_manager Addon
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

if (!$this->hasConfig('jpg_quality')) {
  $this->setConfig('jpg_quality', 85);
}
