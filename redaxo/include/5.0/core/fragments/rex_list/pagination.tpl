    <?php if($this->pager->getRowCount() > $this->pager->getRowsPerPage()): ?>
    <div class="rex-navi-paginate rex-toolbar">
      <div class="rex-toolbar-content">
        <ul class="rex-navi-paginate">
        	<li class="rex-navi-paginate-prev"><a href="<?php echo $this->urlprovider->getUrl(array($this->pager->getCursorName() => $this->pager->getCursor($this->pager->getPrevPage()))) ?>" title="<?php echo $this->i18n('list_previous') ?>"><span><?php echo $this->i18n('list_previous') ?></span></a></li>
          <?php for($page = $this->pager->getFirstPage(); $page < $this->pager->getLastPage(); $page++): ?>
            <?php $class = ($this->pager->isActivePage($page)) ? ' class="rex-active"' : ''; ?>
            <li class="rex-navi-paginate-page"><a href="<?php echo $this->urlprovider->getUrl(array($this->pager->getCursorName() => $this->pager->getCursor($page))) ?>"<?php echo $class ?>><span><?php echo ($page+1) ?></span></a></li>
          <?php endfor; ?>
          <li class="rex-navi-paginate-next"><a href="<?php echo $this->urlprovider->getUrl(array($this->pager->getCursorName() => $this->pager->getCursor($this->pager->getNextPage()))) ?>" title="<?php echo $this->i18n('list_next') ?>"><span><?php echo $this->i18n('list_next') ?></span></a></li>
          <li class="rex-navi-paginate-message"><span><?php echo $this->i18n('list_rows_found', $this->pager->getRowCount()) ?></span></li>
        </ul>
        <div class="rex-clearer"></div>
      </div>
    </div>
    <?php endif; ?>