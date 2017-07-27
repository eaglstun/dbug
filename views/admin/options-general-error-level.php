<label>E_WARNING (<?php echo E_WARNING; ?>)
    <input name="dbug_settings[error_level][<?php echo E_WARNING; ?>]" type="checkbox" value="<?php echo E_WARNING; ?>" <?php checked($error_level[E_WARNING], E_WARNING); ?>/>
</label><br/>

<label>E_NOTICE (<?php echo E_NOTICE; ?>)
    <input name="dbug_settings[error_level][<?php echo E_NOTICE; ?>]" type="checkbox" value="<?php echo E_NOTICE; ?>" <?php checked($error_level[E_NOTICE], E_NOTICE); ?>/>
</label><br/>

<label>E_STRICT (<?php echo E_STRICT; ?>)
    <input name="dbug_settings[error_level][<?php echo E_STRICT; ?>]" type="checkbox" value="<?php echo E_STRICT; ?>" <?php checked($error_level[E_STRICT], E_STRICT); ?>/>
</label><br/>

<label>E_USER_DEPRECATED (<?php echo E_USER_DEPRECATED; ?>)
    <input name="dbug_settings[error_level][<?php echo E_USER_DEPRECATED; ?>]" type="checkbox" value="<?php echo E_USER_DEPRECATED; ?>" <?php checked($error_level[E_USER_DEPRECATED], E_USER_DEPRECATED); ?>/>
</label><br/>

<label>E_ALL (<?php echo E_ALL; ?>)
    <input name="dbug_settings[error_level][<?php echo E_ALL; ?>]" type="checkbox" value="<?php echo E_ALL; ?>" <?php checked($error_level[E_ALL], E_ALL); ?>/>
</label>