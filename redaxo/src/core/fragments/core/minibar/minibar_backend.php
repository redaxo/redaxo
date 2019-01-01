<div class="rex-minibar rex-minibar-backend">
    <div class="rex-minibar-elements">
        <?php
        foreach ($this->elements as $element) {
            $this->subfragment('core/minibar/minibar_element.php', [
                'element' => $element
            ]);
        }
        ?>
    </div>
</div>
