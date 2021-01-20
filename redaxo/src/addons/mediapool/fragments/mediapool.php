<section class="mediapool-wrapper" id="mediapool-wrapper">
    <section class="mediapool-content">
        <?= $this->subfragment('mediapool-header.php') ?>
        <?= $this->subfragment('mediapool-list.php') ?>
    </section>
    <aside class="mediapool-sidebar">
        <div class="container-fluid">
            <b>Filter</b>
        </div>
    </aside>
</section>


<script>
    $('[data-toggle="button"]').on('click', function(event) {
        event.preventDefault();
        let target = $(this).data('target');
        $(target).toggleClass('toggled');
    });
</script>
