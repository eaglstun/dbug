<?php

namespace WP_Dbug;

class Admin
{
    protected $dbug = null;

    public function __construct(Dbug &$dbug)
    {
        $this->dbug = $dbug;

        add_action( 'admin_menu', [$this, 'admin_menu'] );
        add_filter( 'plugin_action_links_dbug/_plugin.php', [$this, 'plugin_action_links'] );
    }

    /**
    *
    *   @param string html
    *   @return string html
    */
    public function admin_footer_text($original = '')
    {
        return render( 'admin/options-general_footer', array(
            'version' => version()
        ) );
    }

    /**
    *   setup page for dbug settings
    *   add link to settings page under 'Settings' admin sidebar
    *   update settings from $_POST
    *   attached to `admin_menu` action
    */
    public function admin_menu()
    {
        add_options_page( 'dbug Settings', 'dbug', 'manage_options', 'dbug', [$this, 'route'] );

        add_settings_section(
            'dbug_settings_section',
            '',    // subhead
            [$this, 'description'],
            'dbug_settings'
        );

        add_settings_field(
            'dbug_settings-error-level',
            'Error Level',
            [$this, 'render_error_level'],
            'dbug_settings',
            'dbug_settings_section'
        );

        add_settings_field(
            'dbug_settings-error-logging',
            'Error Logging',
            [$this, 'render_error_logging'],
            'dbug_settings',
            'dbug_settings_section'
        );

        add_settings_field(
            'dbug_settings-log-path',
            'Log Path',
            [$this, 'render_log_path'],
            'dbug_settings',
            'dbug_settings_section'
        );

        add_settings_field(
            'dbug_settings-log-filesize',
            'Log Size',
            [$this, 'render_log_filesize'],
            'dbug_settings',
            'dbug_settings_section'
        );

        add_settings_field(
            'dbug_settings-log-files',
            'Log Files',
            [$this, 'render_log_files'],
            'dbug_settings',
            'dbug_settings_section'
        );

        register_setting( 'dbug_settings', 'dbug_settings', [$this, 'save_setting'] );
    }

    /**
    *
    *   @param array
    *   @return
    */
    public function description($args)
    {
        echo sprintf( '<pre>%s</pre>', version() );
    }

    /**
    *   settings page in wp-admin
    *   callback for `add_options_page`
    */
    function menu()
    {
        wp_enqueue_style( 'dbug', plugins_url( 'public/admin/options-general.css', dirname(__DIR__) ), [], '' );
        add_filter( 'admin_footer_text', [$this, 'admin_footer_text'] );

        $vars = [
            'bug' => plugins_url('public/admin/bug.png', dirname(__DIR__))
        ];

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
    public function render_error_level()
    {
        // possible values
        $error_levels = [
            E_WARNING => '',
            E_NOTICE => '',
            E_STRICT => '',
            E_USER_DEPRECATED => '',
            E_ALL => ''
        ];

        // stored values
        $dbug_error_levels = $this->dbug->get_setting('error_level');
       
        // mereged values
        $dbug_error_levels = is_array($dbug_error_levels) ? $dbug_error_levels + $error_levels : $error_levels;

        $vars = [
            'error_level' => $dbug_error_levels
        ];

        echo render( 'admin/options-general-error-level', $vars );
    }

    /**
    *
    */
    public function render_error_logging()
    {
        $vars = [
            'error_handler' => (object) [
                'screen' => '',
                'log' => ''
            ]
        ];

        if ($selected = $this->dbug->get_setting('error_handler')) {
            $vars['error_handler']->$selected = 'checked="checked"';
        }

        echo render( 'admin/options-general-error-logging', $vars );
    }

    /**
    *
    */
    public function render_log_files()
    {
        // log file viewer
        $log_files = [];
        $log_path = $this->dbug->get_setting('log_path');

        $excluded = [ '.', '..', '.htaccess'];
        
        if ($handle = opendir($log_path)) {
            while (false !== ($entry = readdir($handle))) {
                if (!in_array($entry, $excluded)) {
                    $log_files[] = $entry;
                }
            }
            
            closedir( $handle );
        }

        $vars = [
            'log_files' => $log_files,
        ];

        echo render( 'admin/options-general-log-files', $vars );
    }

    /**
    *
    */
    public function render_log_filesize()
    {
        $log_bytes = $this->dbug->get_setting('log_filesize');

        $vars = [
            'log_filesize' => $log_bytes / (1024 * 1024)
        ];

        echo render( 'admin/options-general-log-filesize', $vars );
    }

    /**
    *
    */
    public function render_log_path()
    {
        $vars = [
            'log_path' => $this->dbug->get_setting('log_path')
        ];

        echo render( 'admin/options-general-log-path', $vars );
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
                break;
        }
    }

    /**
    *
    *   @param array
    *   @return array
    */
    public function save_setting($settings)
    {
        $settings['error_level'] = array_map( 'intval', $settings['error_level'] );
        
        // make sure the path exists and is writable.
        $settings['log_path'] = check_log_dir( $settings['log_path'] );

        // update log filesize
        $megabytes = (float) $settings['log_filesize'];
        $settings['log_filesize'] = $megabytes * 1024 * 1024;
                
        return $settings;
    }

    /**
    *
    *   @param
    *   @return
    */
    protected function view_log($log_file)
    {
        $log_path = $this->dbug->get_setting('log_path');
        
        // dont view files outside of logdir
        $file_exists = file_exists( $log_path.$log_file );
        $file_outside = strpos(realpath($log_path.$log_file), $log_path) !== 0;
        
        // @TODO handle this better
        if (!$file_exists || $file_outside) {
            return;
        }
            
        $vars = [
            'log_content' => file_get_contents( $log_path.$log_file ),
            'log_file' => $log_file
        ];
        
        echo render( 'admin/log_viewer', $vars );
    }
}
