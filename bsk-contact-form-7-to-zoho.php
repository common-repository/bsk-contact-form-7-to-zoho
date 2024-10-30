<?php
/*
Plugin Name: BSK Contact Form 7 to Zoho
Description: The Version 1.0 of Zoho CRM APIs is beting deprecated. This plugin post form data to CRM via Zoho API 2.0 when submit form. Support Leads, Contacts, Accounts module at this stage.
Version: 1.5
Author: BannerSky.com
Author URI: http://www.bannersky.com/
------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, 
or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin Folder Path.
if ( ! defined( 'BSK_CF7_ZOHO_DIR' ) ) {
    define( 'BSK_CF7_ZOHO_DIR', plugin_dir_path( __FILE__ ) );
}
// Plugin Folder URL.
if ( ! defined( 'BSK_CF7_ZOHO_URL' ) ) {
    define( 'BSK_CF7_ZOHO_URL', plugin_dir_url( __FILE__ ) );
}
/**
 * Plugin main class.
 */
class BSK_CF7_ZOHO {
    
    private static $instance;
    public static $_plugin_version = '1.5';
    private static $_db_version = '1.1';
	private static $_saved_db_version_option = '_bsk_cf7_to_zoho_db_ver_';
    
    public static $_feeds_tbl_name = 'cf7_to_zoho_feeds';
    public static $_plugin_settings_option_name = '_bsk_cf7_to_zoho_plugin_settings_';
    public static $_form_populating_settings_option_pre = '_bsk_cf7_to_zoho_form_populating_settings_of_';
    public static $ajax_loader;
    
    public static $_url_to_upgrade = 'https://www.bannersky.com/document/bsk-contact-form-7-to-zoho-documentation/how-to-upgrade-to-pro-version/';

    public $_CLASS_OBJ_dashboard;
    public $_CLASS_OBJ_form_submit;

    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BSK_CF7_ZOHO ) ) {
            global $wpdb;
            
			self::$instance = new BSK_CF7_ZOHO;
            
            /*
              * Initialize variables 
            */
            self::$ajax_loader = '<img src="'.BSK_CF7_ZOHO_URL.'images/ajax-loader.gif" />';
            
            /*
              * plugin hook
            */
            register_activation_hook( __FILE__, array( self::$instance, 'bsk_cf7_to_zoho_plugin_activate' ) );
            register_deactivation_hook( __FILE__, array( self::$instance, 'bsk_cf7_to_zoho_deactivate' ) );
            register_uninstall_hook( __FILE__, 'BSK_CF7_ZOHO::bsk_cf7_to_zoho_uninstall' );
            
            /*
              * classes
              */
            require_once BSK_CF7_ZOHO_DIR. 'classes/common/options.php';
            require_once BSK_CF7_ZOHO_DIR. 'classes/common/zoho.php';
            require_once BSK_CF7_ZOHO_DIR . 'classes/dashboard/dashboard.php';
            require_once BSK_CF7_ZOHO_DIR . 'classes/submit/submit.php';
            
            self::$instance->_CLASS_OBJ_dashboard = new BSK_CF7_ZOHO_Dashboard();
            self::$instance->_CLASS_OBJ_form_submit = new BSK_CF7_ZOHO_Form_Submit();
            /*
              * Actions
              */
            add_action( 'admin_enqueue_scripts', array(self::$instance, 'bsk_cf7_to_zoho_enqueue_scripts') );
            add_action( 'wp_enqueue_scripts', array(self::$instance, 'bsk_cf7_to_zoho_enqueue_scripts') );
            
            add_action( 'init', array(self::$instance, 'bsk_cf7_to_zoho_post_action') );
            
            add_action( 'plugins_loaded', array(self::$instance, 'bsk_cf7_to_zoho_update_database'), 12 );
		}
        
		return self::$instance;
	}
	/**
	 * Activation handler.
	 */
	public function bsk_cf7_to_zoho_plugin_activate( $network_wide ) {
        
        $plugin_settings = array();
        $plugin_settings['enabled_modules']  = array( 'Leads', 'Contacts' );

        //create or update table
        self::$instance->bsk_cf7_to_zoho_create_table();
        //initialize settings
        update_option( self::$_plugin_settings_option_name, $plugin_settings );
	}

	public function bsk_cf7_to_zoho_deactivate() {
	}
    
    public function bsk_cf7_to_zoho_uninstall(){
        
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $has_active_pro_verison = false;
        $plugins = get_plugins();
        foreach( $plugins as $plugin_key => $data ){
            if( 'bsk-contact-form-7-to-zoho-pro/bsk-contact-form-7-to-zoho-pro.php' == $plugin_key && 
                is_plugin_active( $plugin_key ) ){
                $has_active_pro_verison = true;
                break;
            }
        }
        if( $has_active_pro_verison == true ){
            return;
        }
        
        self::$instance->bsk_cf7_to_zoho_remove_tables_n_options();
    }
    
    public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__,  'Cheatin&#8217;', '1.0' );
	}
    
    public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__,  'Cheatin&#8217;', '1.0' );
	}
    
    public function bsk_cf7_to_zoho_enqueue_scripts(){
        
        if( is_admin() ){
            
            wp_enqueue_script( 'bsk-cf7-to-zoho-pro-admin', 
                                          BSK_CF7_ZOHO_URL . 'js/bsk-cf7-to-zoho-pro-admin.js', 
                                          array('jquery'), 
                                          filemtime( BSK_CF7_ZOHO_DIR.'js/bsk-cf7-to-zoho-pro-admin.js' ) 
                                        );			
            wp_enqueue_style(  'bsk-cf7-to-zoho-pro-admin', 
                                          BSK_CF7_ZOHO_URL . 'css/bsk-cf7-to-zoho-pro-admin.css', 
                                          array(), 
                                          filemtime( BSK_CF7_ZOHO_DIR.'css/bsk-cf7-to-zoho-pro-admin.css' ) 
                                        );	
		}else{
            //do nothing
		}
    }
    
    function bsk_cf7_to_zoho_post_action(){
		if( isset( $_POST['bsk_cf7_to_zoho_action'] ) && strlen($_POST['bsk_cf7_to_zoho_action']) >0 ) {
			do_action( 'bsk_cf7_to_zoho_action_' . $_POST['bsk_cf7_to_zoho_action'], $_POST );
		}
		if( isset( $_GET['bsk-cf7-to-zoho-action'] ) && strlen($_GET['bsk-cf7-to-zoho-action']) >0 ) {
			do_action( 'bsk_cf7_to_zoho_action_' . str_replace( '-', '_', $_GET['bsk-cf7-to-zoho-action'] ), $_GET );
		}
	}
    
    function bsk_cf7_to_zoho_create_table(){
		global $wpdb;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset_collate = $wpdb->get_charset_collate();
		
		$mappings_table = $wpdb->prefix.self::$_feeds_tbl_name;
		$sql = "CREATE TABLE $mappings_table (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `form_id` int(11) NOT NULL,
              `name` varchar(512) NOT NULL,
              `module` varchar(256) NOT NULL,
              `active` TINYINT NOT NULL DEFAULT '1',
              `approve` TINYINT NOT NULL DEFAULT '0',
              `workflow` TINYINT NOT NULL DEFAULT '0',
              `blueprint` TINYINT NOT NULL DEFAULT '0',
              `mapping` text DEFAULT NULL,
              `date` datetime DEFAULT NULL,
              `debug` TINYINT NOT NULL DEFAULT '0',
              `last_log` text DEFAULT NULL,
              `last_log_time` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
		) $charset_collate;";
		dbDelta( $sql );
		
		update_option( self::$_saved_db_version_option, self::$_db_version );
	}
    
    function bsk_cf7_to_zoho_remove_tables_n_options(){
		global $wpdb;
		
        $mappings_table = $wpdb->prefix.self::$_feeds_tbl_name;
		
		$wpdb->query("DROP TABLE IF EXISTS $mappings_table");
        
        delete_option( '_bsk_cf7_to_zoho_db_ver_' );
        delete_option( '_bsk_cf7_to_zoho_plugin_settings_' );
        
        //remove zoho conneciton info
        delete_option( '_bsk_cf7_to_zoho_client_id_' );
        delete_option( '_bsk_cf7_to_zoho_client_secret_' );
        delete_option( '_bsk_cf7_to_zoho_access_token_' );
        delete_option( '_bsk_cf7_to_zoho_refresh_token_' );
        delete_option( '_bsk_cf7_to_zoho_access_token_expires_' );
        
        $sql = 'DELETE FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_bsk_cf7_to_zoho_%"';
        $wpdb->query( $sql );
	}
	
    function bsk_cf7_to_zoho_update_database(){
        $saved_db_version = get_option( self::$_saved_db_version_option, false );
		if( $saved_db_version && version_compare( $saved_db_version, self::$_db_version, '>=' ) ) {
			return;
		}
		global $wpdb;
        
        //upgrade db version to 1.1
		$mappings_table = $wpdb->prefix.self::$_feeds_tbl_name;
        $sql = 'ALTER TABLE `'.$mappings_table.'` ADD `last_log_time` datetime DEFAULT NULL AFTER `last_log`;';
        $wpdb->query( $sql );
        
        update_option( self::$_saved_db_version_option, self::$_db_version );
    }
    
}

BSK_CF7_ZOHO::instance();
