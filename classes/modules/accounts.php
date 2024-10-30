<?php

class BSK_CF7_ZOHO_Module_Account {
    
	public function __construct() {
        
	}
    
    public static function bsk_cf7_to_zoho_get_module_fields(){
        $fields_string = 'a:32:{s:12:"Record_Image";a:3:{s:5:"label";s:13:"Account Image";s:9:"data_type";s:12:"profileimage";s:8:"required";b:0;}s:6:"Rating";a:3:{s:5:"label";s:6:"Rating";s:9:"data_type";s:8:"picklist";s:8:"required";b:0;}s:12:"Account_Name";a:3:{s:5:"label";s:12:"Account Name";s:9:"data_type";s:4:"text";s:8:"required";b:1;}s:5:"Phone";a:3:{s:5:"label";s:5:"Phone";s:9:"data_type";s:5:"phone";s:8:"required";b:0;}s:3:"Fax";a:3:{s:5:"label";s:3:"Fax";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:12:"Account_Site";a:3:{s:5:"label";s:12:"Account Site";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:14:"Parent_Account";a:3:{s:5:"label";s:14:"Parent Account";s:9:"data_type";s:6:"lookup";s:8:"required";b:0;}s:7:"Website";a:3:{s:5:"label";s:7:"Website";s:9:"data_type";s:7:"website";s:8:"required";b:0;}s:13:"Ticker_Symbol";a:3:{s:5:"label";s:13:"Ticker Symbol";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:12:"Account_Type";a:3:{s:5:"label";s:12:"Account Type";s:9:"data_type";s:8:"picklist";s:8:"required";b:0;}s:14:"Account_Number";a:3:{s:5:"label";s:14:"Account Number";s:9:"data_type";s:6:"bigint";s:8:"required";b:0;}s:9:"Ownership";a:3:{s:5:"label";s:9:"Ownership";s:9:"data_type";s:8:"picklist";s:8:"required";b:0;}s:8:"Industry";a:3:{s:5:"label";s:8:"Industry";s:9:"data_type";s:8:"picklist";s:8:"required";b:0;}s:9:"Employees";a:3:{s:5:"label";s:9:"Employees";s:9:"data_type";s:7:"integer";s:8:"required";b:0;}s:14:"Annual_Revenue";a:3:{s:5:"label";s:14:"Annual Revenue";s:9:"data_type";s:8:"currency";s:8:"required";b:0;}s:8:"SIC_Code";a:3:{s:5:"label";s:8:"SIC Code";s:9:"data_type";s:7:"integer";s:8:"required";b:0;}s:10:"Created_By";a:3:{s:5:"label";s:10:"Created By";s:9:"data_type";s:11:"ownerlookup";s:8:"required";b:0;}s:11:"Modified_By";a:3:{s:5:"label";s:11:"Modified By";s:9:"data_type";s:11:"ownerlookup";s:8:"required";b:0;}s:12:"Created_Time";a:3:{s:5:"label";s:12:"Created Time";s:9:"data_type";s:8:"datetime";s:8:"required";b:0;}s:13:"Modified_Time";a:3:{s:5:"label";s:13:"Modified Time";s:9:"data_type";s:8:"datetime";s:8:"required";b:0;}s:14:"Billing_Street";a:3:{s:5:"label";s:14:"Billing Street";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:15:"Shipping_Street";a:3:{s:5:"label";s:15:"Shipping Street";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:12:"Billing_City";a:3:{s:5:"label";s:12:"Billing City";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:13:"Shipping_City";a:3:{s:5:"label";s:13:"Shipping City";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:13:"Billing_State";a:3:{s:5:"label";s:13:"Billing State";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:14:"Shipping_State";a:3:{s:5:"label";s:14:"Shipping State";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:12:"Billing_Code";a:3:{s:5:"label";s:12:"Billing Code";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:13:"Shipping_Code";a:3:{s:5:"label";s:13:"Shipping Code";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:15:"Billing_Country";a:3:{s:5:"label";s:15:"Billing Country";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:16:"Shipping_Country";a:3:{s:5:"label";s:16:"Shipping Country";s:9:"data_type";s:4:"text";s:8:"required";b:0;}s:11:"Description";a:3:{s:5:"label";s:11:"Description";s:9:"data_type";s:8:"textarea";s:8:"required";b:0;}s:15:"zoho_attachment";a:3:{s:5:"label";s:9:"Attahment";s:9:"data_type";s:4:"file";s:8:"required";b:0;}}';
        
        $fields = json_decode( $fields_string, true );
        
        return $fields;
    }
    
    public static function bsk_cf7_to_zoho_post_data_to_module( $access_token, $data ){
        $to_post_data_array = array();
        $to_post_data_array['data'] = array();
        $to_post_data_array['data'][] = $data;
        
        $json_data = json_encode( $to_post_data_array );
        $args = array(
                                'method' => 'POST',
                                'timeout' => 60,
                                'headers' => array( 
                                                        'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                                                        'Content-type' => 'application/json',
                                                        'Content-length' => strlen( $json_data )
                                                  ),
                                'body' => $json_data,
                           );

        $zoho_respond = wp_remote_post( 'https://www.zohoapis.com/crm/v2/Accounts', $args );
        $error_message = '';
        $message = '';
		$record_id = '';
		if( is_wp_error( $zoho_respond ) ) {
			$error_message = $zoho_respond->get_error_message();
		}else{
            $zoho_respond_body  = wp_remote_retrieve_body( $zoho_respond );
            $zoho_return_array = json_decode( $zoho_respond_body, true );
            if( !isset( $zoho_return_array['data'] ) || !is_array( $zoho_return_array['data'] ) || count( $zoho_return_array['data'] ) < 1 ){
                $error_message = $zoho_return_array['message'];
            }else{
                $record_id = $zoho_return_array['data'][0]['details']['id'];
            }
        }
        
        if( $record_id ){
            return $record_id;
        }
        
        return false;
    }
}
