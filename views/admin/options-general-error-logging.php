<label>Screen (DEV):
    <input type="radio" name="dbug_settings[logging]" value="screen" <?php echo $logging->screen; ?>/>
</label><br/>

<label>Logs (PROD):
    <input type="radio" name="dbug_settings[logging]" value="log" <?php echo $logging->log; ?>/>
</label>