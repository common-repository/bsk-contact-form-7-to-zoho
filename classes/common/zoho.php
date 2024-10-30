<?php

class BSK_CF7_ZOHO_Common_ZOHO {
    
	public function __construct() {
		
	}
    
    public static function bsk_cf7_to_zoho_refresh_access_token(){
        $zoho_account_from = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        $client_id = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_id_key, '' );
        $client_secret = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_secret_key, '' );
        $access_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
        $refresh_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, '' );
        $expires = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, '' );
        
        if( $access_token == "" || $refresh_token == "" || $client_id == "" || $client_secret == "" ){
            update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
            update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, '' );

            return;
        }

        if( $expires && current_time('timestamp') <= $expires ){
            return;
        }
        
        $site_url = 'https://accounts.zoho.com';
        if( $zoho_account_from == 'www.zoho.eu' ){
            $site_url = 'https://accounts.zoho.eu';
        }else if( $zoho_account_from == 'www.zoho.in' ){
            $site_url = 'https://accounts.zoho.in';
        }else if( $zoho_account_from == 'www.zoho.com.cn' ){
            $site_url = 'https://accounts.zoho.com.cn';
        }
        
        $refresh_url = $site_url.'/oauth/v2/token?refresh_token='.$refresh_token.'&client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=refresh_token';

        $zoho_respond = wp_remote_post( $refresh_url, array('timeout' => 60) );
        $zoho_respond_body  = wp_remote_retrieve_body( $zoho_respond );
        $zoho_respond_body_array = json_decode( $zoho_respond_body, true );

        if( isset( $zoho_respond_body_array['access_token'] ) && isset( $zoho_respond_body_array['expires_in'] ) ){
            $access_token =  $zoho_respond_body_array['access_token'];
            $expires = $zoho_respond_body_array['expires_in'] - 3 * 60;

            update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, $access_token );
            update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, current_time('timestamp') + $expires );
        }
    }
    
}
