<?php

namespace WP_Dbug;

class Admin
{
    public function __construct()
    {
        add_action( 'admin_menu', [$this, 'admin_menu'] );
        add_filter( 'plugin_action_links_dbug/_plugin.php', array($this, 'plugin_action_links') );
    }

    /**
    *   setup page for dbug settings
    *   add link to settings page under 'Settings' admin sidebar
    *   update settings from $_POST
    *   attached to `admin_menu` action
    */
    public function admin_menu()
    {
        add_options_page( 'dbug Settings', 'dbug', 'manage_options', 'dbug', array($this, 'route') );
    
        // update settings $_POST
        if (isset($_GET['page']) && $_GET['page'] == 'dbug' && isset($_POST['submit'])) {
            // remove empty posts
            $allowed = array( 'dbug_error_level', 'dbug_logging', 'dbug_log_path' );
            foreach ($allowed as $allow) {
                if (!isset($_POST[$allow])) {
                    delete_option( $allow );
                }
            }
        
            // update dbug_log_path
            if (isset($_POST['dbug_error_level'])) {
                update_option( 'dbug_error_level', $_POST['dbug_error_level'] );
            }
        
            // update screen or logs
            if (isset($_POST['dbug_logging'])) {
                update_option( 'dbug_logging', $_POST['dbug_logging'] );
            }
        
            // update dbug_log_path
            if (isset($_POST['dbug_log_path'])) {
                //make sure the path exists and is writable.
            
                $dir = $_POST['dbug_log_path'];
                $dir = check_log_dir( $dir );
                update_option( 'dbug_log_path', $dir );
            }
        
            // update log filesize
            if (isset($_POST['dbug_log_filesize'])) {
                $megabytes = (float) $_POST['dbug_log_filesize'];
                $bytes = $megabytes * 1024 * 1024;
                update_option( 'dbug_log_filesize', $bytes );
            }
        }
    }

    /**
    *   settings page in wp-admin
    *   callback for `add_options_page`
    */
    function menu()
    {
        $log_path = get_log_path();
        
        $vars = (object) array(
            'dbug_logging' => (object) array(
                                'screen' => '',
                                'log' => ''
                            ),
            'dbug_log_path' => $log_path,
            'path' => plugins_url('public/', dirname(__DIR__)),
            'version' => version()
        );
       
        // possible values
        $error_levels = array(
            E_WARNING => '',
            E_NOTICE => '',
            E_STRICT => '',
            E_USER_DEPRECATED => '',
            E_ALL => ''
        );
        
        // stored values
        $dbug_error_levels = get_option( 'dbug_error_level' );
        
        // mereged values
        $dbug_error_levels = is_array($dbug_error_levels) ? $dbug_error_levels + $error_levels : $error_levels;
        foreach ($dbug_error_levels as $k => $v) {
            if ((int) $dbug_error_levels[$k] > 0) {
                $dbug_error_levels[$k] = 'checked="checked"';
            }
        }
        
        $vars->dbug_error_level = $dbug_error_levels;
        
        if ($selected = get_option( 'dbug_logging')) {
            $vars->dbug_logging->$selected = 'checked="checked"';
        }
        
        $log_bytes = get_log_filesize();
        $vars->dbug_log_filesize = $log_bytes / (1024 * 1024);
        
        // log file viewer
        $log_files = array();
        $excluded = array( '.', '..', '.htaccess' );
        
        if ($handle = opendir($log_path)) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, $excluded)) {
                    $log_files[] = $entry;
                }
            }
            
            closedir( $handle );
        }
        
        $vars->log_files = $log_files;
        
        echo render( 'admin/options-general', $vars );
    }

    /**
    *   add direct link to 'Settings' in plugins table - plugins.php
    *   attached to 'plugin_action_links_dbug/dbug.php' action
    *   @param array
    *   @return array
    */
    public function plugin_action_links($links)
    {
        $settings_link = '<a href="options-general.php?page=dbug">Settings</a>';
        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
    *
    */
    public function route()
    {
        switch (true) {
            case isset( $_GET['log_file'] ):
                $this->view_log( $_GET['log_file'] );
                break;
                
            default:
                $this->menu();
        }
    }

    /**
    *
    *   @param
    *   @return
    */
    protected function view_log($log_file)
    {
        $log_path = get_log_path();
        
        // dont view files outside of logdir
        $file_exists = file_exists( $log_path.$log_file );
        $file_outside = strpos(realpath($log_path.$log_file), $log_path) !== 0;
        
        // @TODO handle this better
        if (!$file_exists || $file_outside) {
            return;
        }
            
        $vars = (object) array(
            'log_content' => file_get_contents( $log_path.$log_file ),
            'log_file' => $log_file
        );
        
        echo render( 'admin/log_viewer.php', $vars );
    }
}
