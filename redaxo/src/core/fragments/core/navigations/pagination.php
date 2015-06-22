        <?php if ($this->pager->getRowCount() > $this->pager->getRowsPerPage()): ?>
            <ul class="rex-pagination pagination">
                <li class="rex-prev"><a href="<?php echo $this->urlprovider->getUrl([$this->pager->getCursorName() => $this->pager->getCursor($this->pager->getPrevPage())]) ?>" title="<?php echo $this->i18n('list_previous') ?>"><?php echo $this->i18n('list_previous') ?></a></li>
                <li class="rex-next"><a href="<?php echo $this->urlprovider->getUrl([$this->pager->getCursorName() => $this->pager->getCursor($this->pager->getNextPage())]) ?>" title="<?php echo $this->i18n('list_next') ?>"><?php echo $this->i18n('list_next') ?></a></li>
                <?php for ($page = $this->pager->getFirstPage(); $page <= $this->pager->getLastPage(); ++$page): ?>
                    <?php $class = ($this->pager->isActivePage($page)) ? ' class="rex-active"' : ''; ?>
                    <li class="rex-page"><a href="<?php echo $this->urlprovider->getUrl([$this->pager->getCursorName() => $this->pager->getCursor($page)]) ?>"<?php echo $class ?>><?php echo($page + 1) ?></a></li>
                <?php endfor; ?>
                <li class="rex-notice"><?php echo $this->i18n('list_rows_found', $this->pager->getRowCount()) ?></li>
            </ul>
        <?php endif;
