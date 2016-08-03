<?php
/*
*	Version: 1.9
*/
?>
<div class="wrap">
	<h2>dbug</h2>
	<h6>Version: 1.9</h6>
	
	<form action="" method="post" class="dbug">
		<div>
			<label class="option">Error Level:</label><br/>
			
			<label>E_WARNING (<?php echo E_WARNING; ?>)
				<input name="dbug_error_level[<?php echo E_WARNING; ?>]" type="checkbox" value="<?php echo E_WARNING; ?>" <?php echo $dbug_error_level[2]; ?>/>
			</label><br/>
			
			<label>E_NOTICE (<?php echo E_NOTICE; ?>)
				<input name="dbug_error_level[<?php echo E_NOTICE; ?>]" type="checkbox" value="<?php echo E_NOTICE; ?>" <?php echo $dbug_error_level[8]; ?>/>
			</label><br/>

			<label>E_STRICT (<?php echo E_STRICT; ?>)
				<input name="dbug_error_level[<?php echo E_STRICT; ?>]" type="checkbox" value="<?php echo E_STRICT; ?>" <?php echo $dbug_error_level[E_STRICT]; ?>/>
			</label><br/>

			<label>E_USER_DEPRECATED (<?php echo E_USER_DEPRECATED; ?>)
				<input name="dbug_error_level[<?php echo E_USER_DEPRECATED; ?>]" type="checkbox" value="<?php echo E_USER_DEPRECATED; ?>" <?php echo $dbug_error_level[E_USER_DEPRECATED]; ?>/>
			</label><br/>
			
			<label>E_ALL (<?php echo E_ALL; ?>)
				<input name="dbug_error_level[<?php echo E_ALL; ?>]" type="checkbox" value="<?php echo E_ALL; ?>" <?php echo $dbug_error_level[E_ALL]; ?>/>
			</label><br/>
		</div>
		
		<div>
			<label class="option">Error Logging:</label><br/>
			
			<label>Screen (DEV):
				<input type="radio" name="dbug_logging" value="screen" <?php echo $dbug_logging->screen; ?>/>
			</label><br/>
			
			<label>Logs (PROD):
				<input type="radio" name="dbug_logging" value="log" <?php echo $dbug_logging->log; ?>/>
			</label>
		</div>
		
		<div>
			<label class="option">Log Path:</label>
			<input name="dbug_log_path" value="<?php echo $dbug_log_path; ?>" size="80"/>
			
			<br/><code><?php echo __DIR__; ?></code>
		</div>
		
		<div>
			<label class="option">Max Log Filesize:</label>
			<input name="dbug_log_filesize" value="<?php echo $dbug_log_filesize; ?>"/> ( megabytes )
		</div>
		
		<div>
			<label class="option">Log Files:</label>
			<ul>
			<?php foreach( $log_files as $log_file ): ?>
			<li><a href="?page=dbug&log_file=<?php echo $log_file; ?>"><?php echo $log_file; ?></a></li>
			<?php endforeach; ?>
			</ul>
		</div>
		
		<input type="submit" name="submit" value="Update Options"/>
	</form>
	
	<marquee direction="right"><img src="<?php echo $path; ?>bug.png"/></marquee>
	<a class="photo" href="http://www.flickr.com/photos/jruud/186673543/">photo by jrundruud</a>
</div>

<style>

h2, h6{
	float: left;
}

form.dbug{
	clear: left;
}

form.dbug label.option{
	font-weight: bold;
	display: block;
	margin: 1em 0 0 0;
	width: 10em;
}

marquee{
	padding-top: 4em;
}

a.photo{
	font-size: .6em;
}
</style>