<?php

/**
 * Userinfo Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

// zuletzt bearbeitete artikel (version-addon)
// zuletzt bearbeitete editMe datensätze
// zuletzt bearbeitete editMe Datenmodelle
// zuletzt gelaufene cronjobs

class rex_stats_component extends rex_dashboard_component
{
  public function __construct()
  {
    // default cache lifetime in seconds
    $cache_options['lifetime'] = 1800;

    parent::__construct('userinfo-stats', $cache_options);
    $this->setTitle(rex_i18n::msg('userinfo_component_stats_title'));
    $this->setBlock(rex_i18n::msg('userinfo_block_stats'));
  }

  protected function prepare()
  {
    $stats = rex_a659_statistics();

    $content = '';

    $content .= '<tr>';
    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_clangs');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_clangs'];
    $content .= '</td>';

    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_templates');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_templates'];
    $content .= '</td>';
    $content .= '</tr>';

    $content .= '<tr>';
    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_categories');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_categories'];
    $content .= '</td>';

    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_modules');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_modules'];
    $content .= '</td>';
    $content .= '</tr>';

    $content .= '<tr>';
    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_articles');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_articles'];
    $content .= '</td>';

    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_actions');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_actions'];
    $content .= '</td>';
    $content .= '</tr>';

    $content .= '<tr>';
    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_slices');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_slices'];
    $content .= '</td>';

    $content .= '<th>';
    $content .= rex_i18n::msg('userinfo_component_stats_users');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_users'];
    $content .= '</td>';
    $content .= '</tr>';

    $this->setContent(
      '<table class="rex-table rex-dashboard-table">
        <colgroup>
          <col width="20%" />
          <col width="30%" />
          <col width="20%" />
          <col width="30%" />
        </colgroup>
        <tbody>
          '.$content.'
        </tbody>
      </table>'
    );
  }
}

class rex_articles_component extends rex_dashboard_component
{
  function __construct()
  {
    parent::__construct('userinfo-articles');
    $this->setTitle(rex_i18n::msg('userinfo_component_articles_title'));
    $this->setTitleUrl('index.php?page=structure');
    $this->setBlock(rex_i18n::msg('userinfo_block_latest_infos'));
  }

  public function checkPermission()
  {
    return rex::getUser()->isAdmin() || rex::getUser()->getComplexPerm('structure')->hasStructurePerm();
  }

  protected function prepare()
  {
    $limit = A659_DEFAULT_LIMIT;

    if(rex::getUser()->getComplexPerm('structure')->hasMountpoints())
    {
      $whereCond = '1=1';
    }
    else
    {
      $whereCond = '1=0';
      $categoryPerms = rex::getUser()->getComplexPerm('structure')->getMountpoints();
      foreach($categoryPerms as $catPerm)
      {
        $whereCond .= ' OR path LIKE "%|'. $catPerm .'|%"';
      }
    }

    $qry = 'SELECT id, re_id, clang, startpage, name, updateuser, updatedate
            FROM '. rex::getTablePrefix() .'article
            WHERE '. $whereCond .'
            GROUP BY id
            ORDER BY updatedate DESC
            LIMIT '. $limit;
    $list = rex_list::factory($qry);
    $list->setCaption(rex_i18n::msg('userinfo_component_articles_caption'));
    $list->addTableAttribute('summary', rex_i18n::msg('userinfo_component_articles_summary'));
    $list->addTableColumnGroup(array(40, '*', 120, 150));

    $list->removeColumn('id');
    $list->removeColumn('re_id');
    $list->removeColumn('clang');
    $list->removeColumn('startpage');
    $editParams = array('page' => 'content', 'mode' => 'edit', 'article_id' => '###id###', 'clang' => '###clang###');

    $thIcon = '';
    $tdIcon = '<span class="rex-i-element rex-i-article"><span class="rex-i-element-text">###name###</span></span>';
    $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
    $list->setColumnParams($thIcon, $editParams);

    $list->setColumnLabel('name', rex_i18n::msg('header_article_name'));
    $list->setColumnParams('name', $editParams);

    $list->setColumnLabel('updateuser', rex_i18n::msg('userinfo_component_stats_user'));
    $list->setColumnLabel('updatedate', rex_i18n::msg('userinfo_component_stats_date'));
    $list->setColumnFormat('updatedate', 'strftime', 'datetime');

    $this->setContent($list->get());
  }
}

class rex_media_component extends rex_dashboard_component
{
  public function __construct()
  {
    parent::__construct('userinfo-media');
    $this->setTitle(rex_i18n::msg('userinfo_component_media_title'));
    $this->setTitleUrl('javascript:openMediaPool();');
    $this->setBlock(rex_i18n::msg('userinfo_block_latest_infos'));
  }

  public function checkPermission()
  {
    return rex::getUser()->getComplexPerm('media')->hasMediaPerm();
  }

  protected function prepare()
  {
    $limit = A659_DEFAULT_LIMIT;

    $list = rex_list::factory('SELECT category_id, media_id, filename, updateuser, updatedate FROM '. rex::getTablePrefix() .'media ORDER BY updatedate DESC LIMIT '.$limit);
    $list->setCaption(rex_i18n::msg('pool_file_caption'));
    $list->addTableAttribute('summary', rex_i18n::msg('pool_file_summary'));
    $list->addTableColumnGroup(array(40, '*', 120, 150));

    $list->removeColumn('category_id');
    $list->removeColumn('media_id');
    $editParams = array('page' => 'mediapool', 'subpage' => 'detail', 'rex_file_category' => '###category_id###', 'file_id' => '###media_id###');

    $thIcon = '';
    $tdIcon = '<span class="rex-i-element rex-i-media"><span class="rex-i-element-text">###filename###</span></span>';
    $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
    $list->setColumnParams($thIcon, $editParams);
    $list->addLinkAttribute($thIcon, 'onclick', 'newPoolWindow(this.href); return false;');

    $list->setColumnLabel('filename', rex_i18n::msg('pool_file_info'));
    $list->setColumnParams('filename', $editParams);
    $list->addLinkAttribute('filename', 'onclick', 'newPoolWindow(this.href); return false;');

    $list->setColumnLabel('updateuser', rex_i18n::msg('userinfo_component_stats_user'));
    $list->setColumnLabel('updatedate', rex_i18n::msg('userinfo_component_stats_date'));
    $list->setColumnFormat('updatedate', 'strftime', 'datetime');

    $this->setContent($list->get());
  }
}
