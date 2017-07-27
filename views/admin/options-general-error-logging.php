<label>Screen (DEV):
    <input type="radio" name="dbug_settings[error_handler]" value="screen" <?php echo $error_handler->screen; ?>/>
</label><br/>

<label>Logs (PROD):
    <input type="radio" name="dbug_settings[error_handler]" value="log" <?php echo $error_handler->log; ?>/>
</label>