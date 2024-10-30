<?php

class BSK_CF7_ZOHO_Module_Lead {
    
	public function __construct() {
        
	}
    
    public static function bsk_cf7_to_zoho_get_module_fields(){
        $fields_string = '{"Annual_Revenue":{"label":"Annual Revenue","required":false},"City":{"label":"City","required":false},"Company":{"label":"Company","required":false},"Country":{"label":"Country","required":false},"Created_By":{"label":"Created By","required":false},"Created_Time":{"label":"Created Time","required":false},"Data_Processing_Basis_Details":{"label":"Data Processing Basis Details","required":false},"Description":{"label":"Description","required":false},"Email":{"label":"Email","required":false},"Email_Opt_Out":{"label":"Email Opt Out","required":false},"Fax":{"label":"Fax","required":false},"First_Name":{"label":"First Name","required":false},"Full_Name":{"label":"Full Name","required":false},"Industry":{"label":"Industry","required":false},"Last_Name":{"label":"Last Name","required":true},"Record_Image":{"label":"Leads Image","required":false},"Lead_Source":{"label":"Leads Source","required":false},"Lead_Status":{"label":"Leads Status","required":false},"Mobile":{"label":"Mobile","required":false},"Modified_By":{"label":"Modified By","required":false},"Modified_Time":{"label":"Modified Time","required":false},"No_of_Employees":{"label":"No. of Employees","required":false},"Phone":{"label":"Phone","required":false},"Rating":{"label":"Rating","required":false},"Salutation":{"label":"Salutation","required":false},"Secondary_Email":{"label":"Secondary Email","required":false},"Skype_ID":{"label":"Skype ID","required":false},"State":{"label":"State","required":false},"Street":{"label":"Street","required":false},"Designation":{"label":"Title","required":false},"Twitter":{"label":"Twitter","required":false},"Website":{"label":"Website","required":false},"Zip_Code":{"label":"Zip Code","required":false}}';
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
                                'sslverify' => false,
                                'headers' => array( 
                                                        'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                                                        'Content-type' => 'application/json',
                                                        'Content-length' => strlen( $json_data )
                                                  ),
                                'body' => $json_data,
                           );

        $zoho_respond = wp_remote_post( 'https://www.zohoapis.com/crm/v2/Leads', $args );
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
