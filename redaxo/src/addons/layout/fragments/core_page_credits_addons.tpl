<div class="rex-article" id="credits-addons">

  <div class="rex-content">
    <table class="rex-table" id="credits-addons-summary" summary="<?php echo $this->i18n("credits_summary") ?>">
      <caption><?php echo $this->i18n("credits_caption"); ?></caption>
      <thead>
      <tr>
        <th class="rex-column-a"><?php echo $this->i18n("credits_name"); ?></th>
        <th class="rex-column-b"><?php echo $this->i18n("credits_version"); ?></th>
        <th class="rex-column-c"><?php echo $this->i18n("credits_author"); ?></th>
        <th class="rex-column-d"><?php echo $this->i18n("credits_supportpage"); ?></th>
      </tr>
      </thead>

      <tbody>
      <?php foreach($this->addons as $addon): ?>
        <tr class="rex-addon">
          <th<?php echo ($addon->class) ? ' class="'.$addon->class.'"' : '' ?>><?php echo $addon->name ?> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname=<?php echo $addon->name ?>">?</a>]</th>
          <td<?php echo ($addon->class) ? ' class="'.$addon->class.'"' : '' ?>><?php echo $addon->version ?></td>
          <td<?php echo ($addon->class) ? ' class="'.$addon->class.'"' : '' ?>><?php echo $addon->author ?></td>
          <td<?php echo ($addon->class) ? ' class="'.$addon->class.'"' : '' ?>>
          <?php if ($addon->supportpage): ?>
            <a href="http://<?php echo $addon->supportpage ?>" onclick="window.open(this.href); return false;"><?php echo $addon->supportpage ?></a>
          <?php endif; ?>
          </td>
        </tr>
        <?php foreach($addon->plugins as $plugin): ?>
          <tr class="rex-plugin">
            <th<?php echo ($addon->class) ? ' class="'.$plugin->class.'"' : '' ?>><?php echo $plugin->name ?> [<a href="index.php?page=addon&amp;subpage=help&amp;addonname=<?php echo $addon->name ?>&amp;pluginname=<?php echo $plugin->name ?>">?</a>]</th>
            <td<?php echo ($addon->class) ? ' class="'.$plugin->class.'"' : '' ?>><?php echo $plugin->version ?></td>
            <td<?php echo ($addon->class) ? ' class="'.$plugin->class.'"' : '' ?>><?php echo $plugin->author ?></td>
            <td<?php echo ($addon->class) ? ' class="'.$plugin->class.'"' : '' ?>>
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

</div>