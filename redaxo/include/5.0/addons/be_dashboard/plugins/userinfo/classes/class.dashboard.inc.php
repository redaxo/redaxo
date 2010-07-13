<?php

/**
 * Userinfo Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

// zuletzt bearbeitete artikel (version-addon)
// zuletzt bearbeitete editMe datensätze
// zuletzt bearbeitete editMe Datenmodelle
// zuletzt gelaufene cronjobs

class rex_stats_component extends rex_dashboard_component
{
  function rex_stats_component()
  {
    global $I18N;
    
    // default cache lifetime in seconds
    $cache_options['lifetime'] = 1800;
    
    parent::rex_dashboard_component('userinfo-stats', $cache_options);
    $this->setTitle($I18N->msg('userinfo_component_stats_title'));
    $this->setBlock($I18N->msg('userinfo_block_stats'));
  }
  
  /*protected*/ function prepare()
  {
    global $I18N;
    
    $stats = rex_a659_statistics();
    
    $content = '';
    
    $content .= '<tr>';
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_clangs');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_clangs'];
    $content .= '</td>';
    
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_templates');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_templates'];
    $content .= '</td>';
    $content .= '</tr>';
    
    $content .= '<tr>';
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_categories');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_categories'];
    $content .= '</td>';
    
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_modules');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_modules'];
    $content .= '</td>';
    $content .= '</tr>';
    
    $content .= '<tr>';
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_articles');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_articles'];
    $content .= '</td>';
    
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_actions');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_actions'];
    $content .= '</td>';
    $content .= '</tr>';
    
    $content .= '<tr>';
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_slices');
    $content .= '</th>';
    $content .= '<td>';
    $content .= $stats['total_slices'];
    $content .= '</td>';
    
    $content .= '<th>';
    $content .= $I18N->msg('userinfo_component_stats_users');
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
  function rex_articles_component()
  {
    global $I18N;
    
    parent::rex_dashboard_component('userinfo-articles');
    $this->setTitle($I18N->msg('userinfo_component_articles_title'));
    $this->setTitleUrl('index.php?page=structure');
    $this->setBlock($I18N->msg('userinfo_block_latest_infos'));
  }
  
  /*public*/ function checkPermission()
  {
    global $REX;
    return $REX['USER']->isAdmin() || $REX['USER']->hasStructurePerm();
  }
  
  /*protected*/ function prepare()
  {
    global $REX, $I18N;
    
    $limit = A659_DEFAULT_LIMIT;
    
    $qry = 'SELECT id, re_id, clang, startpage, name, updateuser, updatedate 
            FROM '. $REX['TABLE_PREFIX'] .'article
            WHERE '. $REX['USER']->getCategoryPermAsSql() .'
            GROUP BY id
            ORDER BY updatedate DESC
            LIMIT '. $limit;
    $list = rex_list::factory($qry);
    $list->setCaption($I18N->msg('userinfo_component_articles_caption'));
    $list->addTableAttribute('summary', $I18N->msg('userinfo_component_articles_summary'));
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
  
    $list->setColumnLabel('name', $I18N->msg('header_article_name'));
    $list->setColumnParams('name', $editParams);
  
    $list->setColumnLabel('updateuser', $I18N->msg('userinfo_component_stats_user'));
    $list->setColumnLabel('updatedate', $I18N->msg('userinfo_component_stats_date'));
    $list->setColumnFormat('updatedate', 'strftime', 'datetime');
    
    $this->setContent($list->get());
  }
}

class rex_media_component extends rex_dashboard_component
{
  function rex_media_component()
  {
    global $I18N;
    
    parent::rex_dashboard_component('userinfo-media');
    $this->setTitle($I18N->msg('userinfo_component_media_title'));
    $this->setTitleUrl('javascript:openMediaPool();');
    $this->setBlock($I18N->msg('userinfo_block_latest_infos'));
  }
  
  /*public*/ function checkPermission()
  {
    global $REX;
    
    return $REX['USER']->hasMediaPerm();
  }

  /*protected*/ function prepare()
  {
    global $REX, $I18N;
    
    $limit = A659_DEFAULT_LIMIT;
      
    $list = rex_list::factory('SELECT category_id, file_id, filename, updateuser, updatedate FROM '. $REX['TABLE_PREFIX'] .'file ORDER BY updatedate DESC LIMIT '.$limit);
    $list->setCaption($I18N->msg('pool_file_caption'));
    $list->addTableAttribute('summary', $I18N->msg('pool_file_summary'));
    $list->addTableColumnGroup(array(40, '*', 120, 150));
  
    $list->removeColumn('category_id');
    $list->removeColumn('file_id');
    $editParams = array('page' => 'mediapool', 'subpage' => 'detail', 'rex_file_category' => '###category_id###', 'file_id' => '###file_id###');
    
    $thIcon = '';
    $tdIcon = '<span class="rex-i-element rex-i-media"><span class="rex-i-element-text">###filename###</span></span>';
    $list->addColumn($thIcon, $tdIcon, 0, array('<th class="rex-icon">###VALUE###</th>','<td class="rex-icon">###VALUE###</td>'));
    $list->setColumnParams($thIcon, $editParams);
    $list->addLinkAttribute($thIcon, 'onclick', 'newPoolWindow(this.href); return false;');
    
    $list->setColumnLabel('filename', $I18N->msg('pool_file_info'));
    $list->setColumnParams('filename', $editParams);
    $list->addLinkAttribute('filename', 'onclick', 'newPoolWindow(this.href); return false;');
    
    $list->setColumnLabel('updateuser', $I18N->msg('userinfo_component_stats_user'));
    $list->setColumnLabel('updatedate', $I18N->msg('userinfo_component_stats_date'));
    $list->setColumnFormat('updatedate', 'strftime', 'datetime');
  
    $this->setContent($list->get());
  }
}
