<?php

class BSK_CF7_ZOHO_Form_Submit {
    
	public function __construct() {
        add_action( 'wpcf7_mail_sent', array( $this, 'bsk_cf7_to_zoho_form_submit_fun' ) );
	}
    
    function bsk_cf7_to_zoho_form_submit_fun( $contact_form ){
        $submission = WPCF7_Submission::get_instance();
        $submission_uploaded_files = $submission->uploaded_files();
            
        $form_id = $contact_form->id();
        if( $form_id < 1 ){
            return;
        }
        
        BSK_CF7_ZOHO_Common_ZOHO::bsk_cf7_to_zoho_refresh_access_token();
        
        $client_id = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_id_key, '' );
        $client_secret = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_secret_key, '' );
        $access_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
        $refresh_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, '' );
        $expires = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, '' );
        if( $client_id == "" || $client_secret == "" || $access_token == "" || $refresh_token == "" || $expires == "" ){
            return;
        }
        
        //get form active feeds
        global $wpdb;
        
        $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` '.
                  'WHERE `form_id` = %d AND `active` = %d '.
                  'ORDER BY `id` ASC '.
                  'LIMIT 0, 1';
        $sql = $wpdb->prepare( $sql, $form_id, 1 );
        $results = $wpdb->get_results( $sql );
        if( !$results || !is_array( $results ) || count( $results ) < 1 ){
            return;
        }

        $enabled_modules = $this->bsk_cf7_to_zoho_get_enabled_modules();
        foreach( $results as $feed_obj ){
            $log_array = array();
            $log_array[] = 'Date and Time: '.date( 'Y-m-d H:i:s', current_time('timestamp') )."\n\n";
            $log_array[] = 'Feed ID: '.$feed_obj->id.", Form ID: ".$feed_obj->form_id."\n\n";
            $log_array[] = 'Feed Name: '.$feed_obj->name."\n\n";
            
            $module = $feed_obj->module;
            
            if( $module ){
                $log_array[] = 'Module: '.$module."\n\n";
            }else{
                $log_array[] = 'The module has not been saved.'."\n\n";
                
                if( $feed_obj->debug ){
                    $data_to_update = array( 'last_log' => serialize( $log_array ), 'last_log_time' => date('Y-m-d H:i:s', current_time('timestamp') ) );
                    $wpdb->update( $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, $data_to_update, array( 'id' => $feed_obj->id ) );
                }
                continue;
            }
            
            if( !in_array( $module, $enabled_modules ) ){
                $log_array[] = 'Module: '.$module.", hasn't been enabled!\n\n";
                
                if( $feed_obj->debug ){
                    $data_to_update = array( 'last_log' => serialize( $log_array ), 'last_log_time' => date('Y-m-d H:i:s', current_time('timestamp') ) );
                    $wpdb->update( $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, $data_to_update, array( 'id' => $feed_obj->id ) );
                }
                continue;
            }
            
            $triggers_array = array();
            $mapping = $feed_obj->mapping ? unserialize( $feed_obj->mapping ) : false;
            if( $mapping && is_array( $mapping ) && count( $mapping ) ){
                $ret_array = BSK_CF7_ZOHO_Modules::bsk_cf7_to_zoho_post_data_to_module( 
                                                                                        $module, 
                                                                                        $access_token, 
                                                                                        $mapping, 
                                                                                        $triggers_array );
                if( $ret_array['return_bool'] ){
                    $log_array = array_merge( $log_array, $ret_array['logs'] );
                    $log_array[] = "\n\n".'Record created successfully, record ID: '.$ret_array['record_id']."\n\n";
                }else{
                    $log_array[] = 'Create record failed: '."\n\n";
                    $log_array = array_merge( $log_array, $ret_array['logs'] );
                }
            }else{
                $log_array[] = 'No mapping saved for the feed.'."\n\n";
            }
            
            if( $feed_obj->debug ){
                $data_to_update = array( 'last_log' => serialize( $log_array ), 'last_log_time' => date('Y-m-d H:i:s', current_time('timestamp') ) );
                $wpdb->update( $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, $data_to_update, array( 'id' => $feed_obj->id ) );
            }
        }

        return;
    }
    
    function bsk_cf7_to_zoho_get_enabled_modules(){
        $enabled_modules = array();
        $plugin_settings = get_option( BSK_CF7_ZOHO::$_plugin_settings_option_name, false );
        if( $plugin_settings && is_array( $plugin_settings ) && 
            isset( $plugin_settings['enabled_modules'] ) && is_array( $plugin_settings['enabled_modules'] ) ){
            $enabled_modules = $plugin_settings['enabled_modules'];
        }

        return $enabled_modules;
    }
}
