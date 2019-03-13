<div class="wrap">
    <h2>Primary Cat for Posts Settings</h2>

    <form method="post" action="options.php">
        <?php
            settings_fields( 'pcp_options_group' );
            do_settings_sections( 'primary-cat-for-posts' );
            submit_button();
        ?>
    </form>
</div>