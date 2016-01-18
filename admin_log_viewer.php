<?php
/*
*	Version: 1.9
*/
?>
<div class="wrap">
	<h2>dbug</h2>
	<h6>Version: 1.9</h6>
	
	<ul class="subsubsub">
		<li><a href="?page=dbug">Settings</a> | </li>
		<li>Log : <a href=""><?php echo $log_file; ?></a></li>
	</ul>
	
	<div style="clear:both">
		<pre><?php echo _wp_specialchars( $log_content ); ?></pre>
	</div>
</div>