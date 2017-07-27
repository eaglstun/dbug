<div class="wrap">
	<h2>dbug</h2>
    
    <form action="options.php" method="POST">
        <?php
        settings_fields( 'dbug_settings' );
        do_settings_sections( 'dbug_settings' );
        submit_button();
        ?>
    </form>

    <marquee direction="right"><img src="<?php echo $bug; ?>"/></marquee>
    <a class="photo" href="https://www.flickr.com/photos/jruud/186673543/">photo by jrundruud</a>
</div>