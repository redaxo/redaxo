<!-- *** OUTPUT OF CLANG-TOOLBAR - START *** -->
   <div id="rex-clang" class="rex-toolbar">
     <div class="rex-toolbar-content">
       <ul>
         <li><?php $this->i18n("languages"); ?></li>
         <?php foreach($this->languages as $lang): ?>
           <li class="<?php echo $lang['class'] ?> rex-navi-clang-<?php echo $lang['id'] ?>">
             <?php if($lang['url']): ?>
               <a href="<?php echo $lang['url'] ?>" class="<?php echo $lang['link_class'] ?>"><?php echo $lang['name'] ?></a>
             <?php else: ?>
               <span class="rex-strike"><?php echo $lang['name'] ?></span>
             <?php endif; ?>
           </li>
         <?php endforeach; ?>
       </ul>
     </div>
   </div>
<!-- *** OUTPUT OF CLANG-TOOLBAR - END *** -->