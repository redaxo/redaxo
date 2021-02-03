<section class="mediapool-wrapper toggled" id="mediapool-wrapper">
    <section class="mediapool-content">
        <?= $this->subfragment('mediapool-header.php') ?>
        <?= $this->subfragment('mediapool-list.php') ?>
    </section>
    <aside class="mediapool-sidebar">
        <?= $this->subfragment('mediapool-filter.php') ?>
    </aside>
</section>

<script>
    $('[data-toggle="button"]').on('click', function (event) {
        event.preventDefault();
        let target = $(this).data('target');
        $(target).toggleClass('toggled');
    });

    $('[data-layout]').on('click', function (event) {
        event.preventDefault();
        let layout = $(this).data('layout');
        let target = $(this).data('target');

        $(target).removeClass(function (index, css) {
            return (css.match (/(^|\s)is-\S+/g) || []).join(' ');
        });

        $(this).parent().children().removeClass('active');
        $(this).addClass('active');
        $(target).addClass(layout);
    });

    $('[data-layout]').first().trigger('click');
</script>
