<label>Screen (DEV):
    <input type="radio" name="dbug_settings[logging]" value="screen" <?php echo $dbug_logging->screen; ?>/>
</label><br/>

<label>Logs (PROD):
    <input type="radio" name="dbug_settings[logging]" value="log" <?php echo $dbug_logging->log; ?>/>
</label>