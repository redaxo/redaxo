</div><!-- END .rex-page -->
<?php if (rex_be_controller::getCurrentPage() != 'login' && !rex_be_controller::getCurrentPageObject()->isPopup()): ?>
    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".rex-nav-main > .navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
<?php endif; ?>
</body>
</html>
