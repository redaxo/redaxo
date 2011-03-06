<div class="rex-area">

  <table class="rex-table"  summary="<?php echo $this->i18n("credits_summary") ?>">
    <caption><?php echo $this->i18n("credits_caption"); ?></caption>
    <thead>
    <tr>
      <th><?php echo $this->i18n("credits_name"); ?></th>
      <th><?php echo $this->i18n("credits_version"); ?></th>
      <th><?php echo $this->i18n("credits_author"); ?></th>
      <th><?php echo $this->i18n("credits_supportpage"); ?></th>
    </tr>
    </thead>

    <tbody>
    <?php foreach($this->addons as $addon): ?>
      <tr class="rex-addon">
        <td class="rex-col-a"><span class="<?php echo $addon->class ?>"><?php echo $addon->name ?></span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname=<?php echo $addon->name ?>">?</a>]</td>
        <td class="rex-col-b <?php echo $addon->class ?>"><?php echo $addon->version ?></td>
        <td class="rex-col-c <?php echo $addon->class ?>"><?php echo $addon->author ?></td>
        <td class="rex-col-d <?php echo $addon->class ?>">
        <?php if ($addon->supportpage): ?>
          <a href="http://<?php echo $addon->supportpage ?>" onclick="window.open(this.href); return false;"><?php echo $addon->supportpage ?></a>
        <?php endif; ?>
        </td>
      </tr>
      <?php foreach($addon->plugins as $plugin): ?>
        <tr class="rex-plugin">
          <td class="rex-col-a"><span class="<?php echo $plugin->class ?>"><?php echo $plugin->name ?></span> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname=<?php echo $addon->name ?>&amp;pluginname=<?php echo $plugin->name ?>">?</a>]</td>
          <td class="rex-col-b <?php echo $plugin->class ?>"><?php echo $plugin->version ?></td>
          <td class="rex-col-c <?php echo $plugin->class ?>"><?php echo $plugin->author ?></td>
          <td class="rex-col-d <?php echo $plugin->class ?>">
          <?php if ($plugin->supportpage): ?>
            <a href="http://<?php echo $plugin->supportpage ?>" onclick="window.open(this.href); return false;"><?php echo $plugin->supportpage ?></a>
          <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>