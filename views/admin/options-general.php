<div class="wrap">
	<h2>dbug</h2>
    
    <form action="options.php" method="POST">
        <?php
        settings_fields( 'dbug_settings' );
        do_settings_sections( 'dbug_settings' );
        submit_button();
        ?>
    </form>

    <marquee direction="right"><img src="<?php echo $path; ?>bug.png"/></marquee>
    <a class="photo" href="http://www.flickr.com/photos/jruud/186673543/">photo by jrundruud</a>
</div>

<!--
<div class="wrap">
    
    <h6>Version: <?php echo $version; ?></h6>
    
    <form action="" method="post" class="dbug">
    
        
        <div>
            <label class="option">Max Log Filesize:</label>
            <input name="dbug_log_filesize" value="<?php echo $dbug_log_filesize; ?>"/> ( megabytes )
        </div>
        
        <div>
            <label class="option">Log Files:</label>
            <ul>
            <?php foreach ($log_files as $log_file) : ?>
            <li><a href="?page=dbug&log_file=<?php echo $log_file; ?>"><?php echo $log_file; ?></a></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </form>
    
    
</div>
-->