<ul>
    <?php foreach ($log_files as $log_file) : ?>
    <li><a href="?page=dbug&amp;log_file=<?php echo $log_file; ?>"><?php echo $log_file; ?></a></li>
    <?php endforeach; ?>
</ul>