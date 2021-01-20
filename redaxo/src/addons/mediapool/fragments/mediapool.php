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




<style>
    .mediapool-wrapper {
        position: relative;
        padding-right: 0;
        transition: all 0.2s ease;
    }

    .mediapool-wrapper.toggled {
        padding-right: 235px;
    }

    .mediapool-content {
        position: relative;
    }

    .mediapool-sidebar {
        position: absolute;
        z-index: 1000;
        top: 0;
        right: -235px;
        width: 0;
        height: 100%;
        overflow-y: auto;
        background: #fff;
        transition: all 0.2s ease;
    }

    .mediapool-wrapper.toggled .mediapool-sidebar {
        right: 0;
        width: 220px;
    }

    .mediapool-detail-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000;
        display: none;
    }

    .mediapool-detail-wrapper::after {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: none;
        z-index: 900;
        background: rgba(0, 0, 0, .75);
    }
    .mediapool-detail-wrapper.toggled,
    .mediapool-detail-wrapper.toggled::after  {
        display: block;
    }
    .mediapool-detail {
        position: relative;
        z-index: 1000;
        display: flex;
        height: 100%;
    }
    .mediapool-detail-image {
        flex-grow: 1;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .mediapool-detail-image img {
        max-width: 100%;
        max-height: 100%;
    }
    .mediapool-detail-sidebar {
        width: 220px;
        background: #fff;
    }
</style>
