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
</label>