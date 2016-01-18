<div class="dbug">
	<?php echo $error; ?>

	<?php if( count($backtrace) ): ?>
		<span class="backtrace"><strong>backtrace:</strong></span><br/>

		<?php foreach( $backtrace as $item ): ?>
			<span class="backtrace">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $item['file'].' line '.$item['line']; ?>
			</span>

			<br/>
		<?php endforeach; ?>
	<?php endif; ?>
</div>