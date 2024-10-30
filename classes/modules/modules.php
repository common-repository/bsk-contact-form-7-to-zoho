<?php

class BSK_CF7_ZOHO_Modules {
    
    var $_bsk_cf7_to_zoho_supported_modules = array( 
                                                     'leads' => 'Leads', 
                                                     'contacts' => 'Contacts',
                                                     'accounts' => 'Accounts'
                                                   );
    public static $_bsk_cf7_to_zoho_module_layouts_sections_cache_prefix = '_bsk_cf7_to_zoho_module_data_cache_for_';
    public static $_bsk_cf7_to_zoho_module_fields_cache_prefix = '_bsk_cf7_to_zoho_module_fields_cache_for_';
    public static $_bsk_cf7_to_zoho_module_picklist_fields_options_cache_prefix = '_bsk_cf7_to_zoho_module_picklist_options_cache_for_';
    public static $_bsk_cf7_to_zoho_users_cache_option = '_bsk_cf7_to_zoho_users_cache_option';
    
    public static $bsk_cf7zoho_no_multiple_mapping_api_name = array( 'Record_Image' );
    
	function __construct() {
        add_action( 'wp_ajax_bsk_cf7_to_zoho_refresh_module_cache_data', array( $this, 'bsk_cf7_to_zoho_refresh_module_cache_data_ajax_fun' ) );
        add_action( 'wp_ajax_bsk_cf7_to_zoho_enable_disable_module', array( $this, 'bsk_cf7_to_zoho_enable_disable_module_fun' ) );
        add_action( 'wp_ajax_bsk_cf7_to_zoho_create_test_module_data', array( $this, 'bsk_cf7_to_zoho_create_test_module_data_fun' ) );
	}
    
    function bsk_cf7_to_zoho_get_availabel_modules(){
        
        $availabe_modules = array();
        foreach( $this->_bsk_cf7_to_zoho_supported_modules as $file_name => $module_name ){
            if( file_exists( BSK_CF7_ZOHO_DIR.'classes/modules/'.$file_name.'.php' ) ){
                $availabe_modules[] = $module_name;
            }
        }
        
        return $availabe_modules;
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
    
    function bsk_cf7_to_zoho_show_modules_settings(){
        
        $client_id = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_id_key, '' );
        $client_secret = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_secret_key, '' );
        $access_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
        $refresh_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, '' );
        $expires = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, '' );
        
        $enabled_modules = $this->bsk_cf7_to_zoho_get_enabled_modules();
        $availabe_modules = $this->bsk_cf7_to_zoho_get_availabel_modules();
        foreach( $availabe_modules as $module ){
            $chk_checked = '';
            $refresh_btn_container_display = 'none';
            $refresh_or_download_button_value = 'Download module data from your Zoho';
            $create_test_display = 'none';
            if( in_array( $module, $enabled_modules ) ){
                $chk_checked = ' checked';
                $refresh_btn_container_display = 'block';
            }
            $module_layout_sections_data = get_option( self::$_bsk_cf7_to_zoho_module_layouts_sections_cache_prefix.$module, false );
            $module_fields_sections_data = get_option( self::$_bsk_cf7_to_zoho_module_fields_cache_prefix.$module, false );
            if( $module_layout_sections_data && is_array( $module_layout_sections_data ) && count( $module_layout_sections_data ) > 0 &&
                $module_fields_sections_data && is_array( $module_fields_sections_data ) && count( $module_fields_sections_data ) > 0 ){
                $refresh_or_download_button_value = 'Refresh module data from your Zoho';
                $create_test_display = 'inline-block';
            }
        ?>
        <div class="bsk-cf7-to-zoho-module-setting-container module-<?php echo $module; ?>">
            <h3><?php echo $module; ?></h3>
            <p>
                <label><input type="checkbox" name="bsk_cf7_to_zoho_modules_enable_chk" class="bsk-cf7-to-zoho-modules-enable-chk" value="<?php echo $module; ?>"<?php echo $chk_checked; ?> /> Enable this module
                </label>
                <span style="display: none; margin-left: 20px;" class="bsk-cf7-to-zoho-modules-enable-module-ajax-loader" >
                    <img src="<?php echo BSK_CF7_ZOHO_URL; ?>images/ajax-loader.gif" />
                </span>
            </p>
            <?php if( $client_id && $client_secret && $access_token && $refresh_token && $expires ){ ?>
            <p class="bsk-cf7-to-zoho-modules-refresh-cache-btn-container" style="display: <?php echo $refresh_btn_container_display; ?>;">
                <a href="javascript:void(0);" class="button bsk-cf7zoho-add-client-id-button bsk-cf7-to-zoho-modules-refresh-cache-button"  data-module="<?php echo $module; ?>"><?php echo $refresh_or_download_button_value; ?></a>
                <span style="display: none; margin-left: 20px;" class="bsk-cf7-to-zoho-modules-refresh-cache-ajax-loader" >
                    <img src="<?php echo BSK_CF7_ZOHO_URL; ?>images/ajax-loader.gif" />
                </span>
                <a href="javascript:void(0);" class="button bsk-cf7zoho-add-client-id-button bsk-cf7-to-zoho-modules-create-test-button" style="margin-left: 20px;display:<?php echo $create_test_display; ?>;"  data-module="<?php echo $module; ?>">Create test data into this module</a>
                <span style="display: none; margin-left: 20px;" class="bsk-cf7-to-zoho-modules-create-test-ajax-loader">
                    <img src="<?php echo BSK_CF7_ZOHO_URL; ?>images/ajax-loader.gif" />
                </span>
            </p>
            <p class="bsk-cf7-to-zoho-modules-module-operation-msg" style="display: none;"></p>
            <?php } ?>
            <hr />
        </div>
        <?php
        }
        $ajax_nonce = wp_create_nonce( "bsk-cf7-to-zoho-refresh-module-cache-data-ajax" ); 
        ?>
        <input type="hidden" value="<?php echo $ajax_nonce; ?>" id="bsk_cf7_to_zoho_refresh_module_cache_ajax_nonce_ID" />
        <?php
    }
    
    function bsk_cf7_to_zoho_enable_disable_module_fun(){
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-refresh-module-cache-data-ajax', 'nonce', false ) ){
            $array = array( 'status' => false, 'msg' => 'Security check failed or you need refresh the page' );
            wp_die( json_encode( $array ) );
        }
        
        $module = $_POST['module'];
        $operation = $_POST['operation'];
        if( $operation != 'enable' && $operation != 'disable' ){
            $array = array( 'status' => false, 'msg' => 'Invalid operaion' );
            wp_die( json_encode( $array ) );
        }
        
        $availabe_modules = $this->bsk_cf7_to_zoho_get_availabel_modules();
        $module_in_available = false;
        foreach( $availabe_modules as $module_available ){
            if( $module_available == $module ){
                $module_in_available = true;
                break;
            }
        }
        if( $module_in_available == false ){
            $array = array( 'status' => false, 'msg' => 'Invalid module' );
            wp_die( json_encode( $array ) );
        }
        
        $plugin_settings = get_option( BSK_CF7_ZOHO::$_plugin_settings_option_name, false );
        if( !$plugin_settings ){
            $plugin_settings = array();
        }
        if( !isset( $plugin_settings['enabled_modules'] ) || !is_array( $plugin_settings['enabled_modules'] ) ){
            $plugin_settings['enabled_modules'] = array();
        }
        
        if( $operation == 'enable' ){
            if( !in_array( $module, $plugin_settings['enabled_modules'] ) ){
                $plugin_settings['enabled_modules'][] = $module;
            }
        }else if( $operation == 'disable' ){
            if( count($plugin_settings['enabled_modules']) > 0 ){
                foreach( $plugin_settings['enabled_modules'] as $key => $module_exist ){
                    if( $module == $module_exist ){
                        unset( $plugin_settings['enabled_modules'][$key] );
                    }
                }
            }
        }
        
        update_option( BSK_CF7_ZOHO::$_plugin_settings_option_name, $plugin_settings );
        $array = array( 'status' => true, 'msg' => '' );
        wp_die( json_encode( $array ) );
    }
    
    function bsk_cf7_to_zoho_refresh_module_cache_data_ajax_fun(){
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-refresh-module-cache-data-ajax', 'nonce', false ) ){
            $array = array( 'status' => false, 'msg' => 'Security check failed or you need refresh the page' );
            wp_die( json_encode( $array ) );
        }
        
        $module = $_POST['module'];
        $availabe_modules = $this->bsk_cf7_to_zoho_get_availabel_modules();
        if( !in_array( $module, $availabe_modules ) ){
            $array = array( 'status' => false, 'msg' => 'Error, the module '.$module.' is not supported.' );
            wp_die( json_encode( $array ) );
        }
        
        $array = $this->bsk_cf7_to_zoho_refresh_module_cache_datafun( $module );
        
        //refresh users every time
        $this->bsk_cf7_to_zoho_refresh_users_cache_data_fun();
        
        wp_die( json_encode($array) );
    }
    
    function bsk_cf7_to_zoho_refresh_module_cache_datafun( $module ){
        BSK_CF7_ZOHO_Common_ZOHO::bsk_cf7_to_zoho_refresh_access_token();
        $access_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
        if( $access_token == "" ){
            $array = array( 'status' => false, 'msg' => 'Error, you haven\'t connected to Zoho yet.' );
            return $array;
        }

        $zoho_account_from = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        $site_url = 'https://www.zohoapis.com';
        if( $zoho_account_from == 'www.zoho.eu' ){
            $site_url = 'https://www.zohoapis.eu';
        }else if( $zoho_account_from == 'www.zoho.in' ){
            $site_url = 'https://www.zohoapis.in';
        }else if( $zoho_account_from == 'www.zoho.com.cn' ){
            $site_url = 'https://www.zohoapis.com.cn';
        }
        $url = $site_url.'/crm/v2/settings/layouts?module='.$module;
        $headers = array( 'Authorization' => 'Zoho-oauthtoken ' . $access_token, );

        $remote_args = array(
                                            'method' => 'GET',
                                            'timeout' => 60,
                                            'headers' => $headers,
                                            'sslverify' => false,
                                       );
        $response = wp_remote_post( $url, $remote_args );
        $message = '';
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();            
            $array = array( 'status' => false, 'msg' => $error_message );
            return $array;
        }
        
        $response_body  = wp_remote_retrieve_body( $response );
        $return = json_decode( $response_body, true );
        if( $response['response']['code'] != 200 ){
            $error_message = 'code: '.$return['code'].', message: '.$return['message'];
            $array = array( 'status' => false, 'msg' => $error_message );
            return $array;
        }

        $layouts_sections_array = false;
        $picklist_fields_options_array = array();
        $fields_array = false;
        if( $return['layouts'] && is_array( $return['layouts'] ) && count( $return['layouts'] ) > 0 ){
            $layouts_sections_array = array();
            $fields_array = array();
            foreach( $return['layouts'] as $layout_obj ){
                $layout_id = $layout_obj['id'];
                $layouts_sections_array[$layout_id] = array( 'name' => $layout_obj['name'], 'sections' => array() );
                if( isset( $layout_obj['sections'] ) && is_array( $layout_obj['sections'] ) && count( $layout_obj['sections'] ) > 0 ){
                    foreach( $layout_obj['sections'] as $section_obj ){
                        if( !isset( $section_obj['fields'] ) || !is_array( $section_obj['fields'] ) || count( $section_obj['fields'] ) < 1 ){
                            continue;
                        }
                        $section_api_name = $section_obj['api_name'];
                        $layouts_sections_array[$layout_id]['sections'][$section_api_name] = array( 
                                                                                        'label' => $section_obj['display_label'], 
                                                                                        'fields' => array() 
                                                                                     );
                            foreach( $section_obj['fields'] as $field_obj ){
                                if( $field_obj['field_read_only']  || $field_obj['custom_field'] ){
                                    continue;
                                }
                                if( !isset( $field_obj['view_type'] ) || !is_array( $field_obj['view_type'] ) ||
                                    !isset( $field_obj['view_type']['create'] ) || !$field_obj['view_type']['create'] ){

                                    continue;
                                }
                                
                                $field_api_name = $field_obj['api_name'];
                                $fields_array[$field_api_name] = array( 
                                                                          'label' => $field_obj['field_label'], 
                                                                          'data_type' => $field_obj['data_type'], 
                                                                          'required' => $field_obj['system_mandatory'], 
                                                                      );
                                $layouts_sections_array[$layout_id]['sections'][$section_api_name]['fields'][] = $field_api_name;
                                
                                if( ( $field_obj['data_type'] == 'picklist' || 
                                    $field_obj['data_type'] == 'multiselectpicklist' ) && 
                                    isset( $field_obj['pick_list_values'] ) && 
                                    is_array( $field_obj['pick_list_values'] ) && 
                                    count( $field_obj['pick_list_values'] ) > 0 ){

                                    $picklist_values_cache_array = $field_obj['pick_list_values'];
                                    foreach( $picklist_values_cache_array as $key => $option_array ){
                                        unset( $option_array['sequence_number'] );
                                        unset( $option_array['maps'] );

                                        $picklist_values_cache_array[$key] = $option_array;
                                    }

                                    $picklist_fields_options_array[$field_api_name] = array(
                                                                                        'label' => $field_obj['field_label'], 
                                                                                        'options' => $picklist_values_cache_array
                                                                                        );
                                }
                            }//end of foreach fields
                        
                    }//end of foreach section
                }
                
                /*
                  * add attachment for layout
                */
                $fields_array['zoho_attachment'] = array( 
                                                          'label' => 'Attahment', 
                                                          'data_type' => 'file', 
                                                          'required' => false, 
                                                        );
                $layouts_sections_array[$layout_id]['sections']['attachment'] = array( 
                                                                                        'label' => 'Attachments', 
                                                                                        'fields' => array( 'zoho_attachment' ) 
                                                                                     );
            }//end of foreach layout
        }
        
        update_option( self::$_bsk_cf7_to_zoho_module_layouts_sections_cache_prefix.$module, $layouts_sections_array );
        update_option( self::$_bsk_cf7_to_zoho_module_fields_cache_prefix.$module, $fields_array );
        update_option( self::$_bsk_cf7_to_zoho_module_picklist_fields_options_cache_prefix.$module, $picklist_fields_options_array );
        
        $array = array( 'status' => true, 'msg' => 'Now you have latest data for module  '.$module );
        return $array;
    }
    
    function bsk_cf7_to_zoho_create_test_module_data_fun(){
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-refresh-module-cache-data-ajax', 'nonce', false ) ){
            $array = array( 'status' => false, 'msg' => 'Security check failed or you need refresh the page' );
            wp_die( json_encode( $array ) );
        }

        $module = $_POST['module'];
        $availabe_modules = $this->bsk_cf7_to_zoho_get_availabel_modules();
        if( !in_array( $module, $availabe_modules ) ){
            $array = array( 'status' => false, 'msg' => 'Error, the module '.$module.' is not supported.' );
            wp_die( json_encode( $array ) );
        }

        BSK_CF7_ZOHO_Common_ZOHO::bsk_cf7_to_zoho_refresh_access_token();
        $access_token = get_option( BSK_CF7_ZOHO_Dashboard::$_bsk_cf7_to_zoho_access_token_key, '' );
        if( $access_token == "" ){
            $array = array( 'status' => false, 'msg' => 'Error, you haven\'t connected to Zoho yet.' );
            return $array;
        }
        
        $layouts_data_cache = get_option( self::$_bsk_cf7_to_zoho_module_layouts_sections_cache_prefix.$module, false );
        $fields_data_cache = get_option( self::$_bsk_cf7_to_zoho_module_fields_cache_prefix.$module, false );
        
        $module_data_array = array();
        if( $module_fields && is_array( $module_fields ) && count( $module_fields ) > 0 ){
            foreach( $layouts_data_cache as $layout_obj ){
                foreach( $layout_obj['section'] as $section_obj ){
                    foreach( $section_obj['fields'] as $zoho_api_name ){
                        $field_data = $fields_data_cache[$zoho_api_name];
                        if( $field_data['required'] != true ){
                            continue;
                        }
                        if( $field_data['data_type'] == 'text' ){
                            $module_data_array[$field_api_name] = 'test text';
                        }else if( $field_data['data_type'] == 'email' ){
                            $module_data_array[$field_api_name] = 'first.last@gmail.com';
                        }else if( $field_data['data_type'] == 'boolean' ){
                            $module_data_array[$field_api_name] = true;
                        }else if( $field_data['data_type'] == 'phone' ){
                            $module_data_array[$field_api_name] = '111-111-1111';
                        }else if( $field_data['data_type']  == 'picklist' ){
                            $module_data_array[$field_api_name] = 0;
                        }else if( $field_data['data_type']  == 'date' ){
                            $module_data_array[$field_api_name] = date( 'Y-m-d', current_time('timestamp') );
                        }else if( $field_data['data_type']  == 'datetime' ){
                            
                            $offset  = get_option( 'gmt_offset' );
                            $hours   = (int) $offset;
                            $minutes = ( $offset - floor( $offset ) ) * 60;
                            $offset  = sprintf( '%+03d:%02d', $hours, $minutes );
                            
                            $module_data_array[$field_api_name] = date( 'Y-m-dTH:i:s', time() ).$offset;//'2016-04-28T17:59:21+05:30';
                        }
                        
                        if( $field_api_name == 'Zip Code' ){
                            $module_data_array[$field_api_name] = '0000';
                        }
                    }
                }
            }
        }
        
        if( $module == 'Leads' ){
            $module_data_array['Company'] = 'XXX Company';
            $module_data_array['First_Name'] = 'First';
            $module_data_array['Last_Name'] = 'Last';
            $module_data_array['Email'] = 'first.last@gmail.com';
            $module_data_array['Street'] = 'xxx Street';
            $module_data_array['Zip Code'] = '0000';
            $module_data_array['Phone'] = '111-111-1111';
            $module_data_array['City'] = 'xxx City';
        }else if( $module == 'Contacts' ){
            $module_data_array['First_Name'] = 'First';
            $module_data_array['Last_Name'] = 'Last';
            $module_data_array['Email'] = 'first.last@gmail.com';
            $module_data_array['Phone'] = '111-111-1111';
            $module_data_array['Mailing_Street'] = 'xxx Street';
            $module_data_array['Mailing_State'] = 'xxx State';
            $module_data_array['Mailing_City'] = 'xxx City';
            $module_data_array['Mailing_Zip'] = '0000';
            $module_data_array['Mailing_Country'] = 'USA';
        }else if( $module == 'Accounts' ){
            $module_data_array['Account_Name'] = 'BSK Test Account';
            $module_data_array['Account_Site'] = 'https://www.bannersky.com';
            $module_data_array['Email'] = 'first.last@gmail.com';
            $module_data_array['Phone'] = '111-111-1111';
            $module_data_array['Billing_Street'] = 'xxx Street';
            $module_data_array['Billing_State'] = 'xxx State';
            $module_data_array['Billing_City'] = 'xxx City';
            $module_data_array['Billing_Code'] = '0000';
            $module_data_array['Billing_Country'] = 'USA';
            $module_data_array['Shipping_Street'] = 'xxx Street';
            $module_data_array['Shipping_State'] = 'xxx State';
            $module_data_array['Shipping_City'] = 'xxx City';
            $module_data_array['Shipping_Code'] = '0000';
            $module_data_array['Shipping_Country'] = 'USA';
        }
        
        $remote_data_array = array();
        $remote_data_array['data'] = array();
        $remote_data_array['data'][] = $module_data_array;
        
        $json_str = json_encode( $remote_data_array );
        $args = array(
                                'method' => 'POST',
                                'timeout' => 60,
                                'sslverify' => false,
                                'headers' => array( 
                                                        'Authorization' => 'Zoho-oauthtoken ' . $access_token,
                                                        'Content-type' => 'application/json',
                                                        'Content-length' => strlen( $json_str )
                                                  ),
                                'body' => $json_str,
                           );
        $zoho_account_from = get_option( BSK_CF7_ZOHO_Dashboard::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        $site_url = 'https://www.zohoapis.com';
        if( $zoho_account_from == 'www.zoho.eu' ){
            $site_url = 'https://www.zohoapis.eu';
        }else if( $zoho_account_from == 'www.zoho.in' ){
            $site_url = 'https://www.zohoapis.in';
        }else if( $zoho_account_from == 'www.zoho.com.cn' ){
            $site_url = 'https://www.zohoapis.com.cn';
        }
        $zoho_respond = wp_remote_post( $site_url.'/crm/v2/'.$module, $args );
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();
            $array = array( 'status' => false, 'msg' => $error_message );
            wp_die( json_encode( $array ) );
        }
        
        $zoho_respond_body  = wp_remote_retrieve_body( $zoho_respond );
        $zoho_respond_array = json_decode( $zoho_respond_body, true );
        if( !isset( $zoho_respond_array['data'] ) || !is_array( $zoho_respond_array['data'] ) || count( $zoho_respond_array['data'] ) < 1 ){
            $array = array( 'status' => false, 'msg' => $zoho_respond_array['message'] );
            wp_die( json_encode( $array ) );
        }
        
        $message = 'Created Record ID: '.$zoho_respond_array['data'][0]['details']['id'];
        $array = array( 'status' => true, 'msg' => $message );
        wp_die( json_encode( $array ) );
    }
    
    public static function bsk_cf7_to_zoho_post_data_to_module( $module, $access_token, $mapping, $triggers_array ){
        $module_fields_by_api_name = get_option( self::$_bsk_cf7_to_zoho_module_fields_cache_prefix.$module, false );
        
        $logs_array = array();
        
        $logs_array[] = 'Feed mapping: '.serialize( $mapping )."\n\n";
        $logs_array[] = 'Feed triggers: '.serialize( $triggers_array )."\n\n";
        
        //organise zoho fields array
        $zoho_fields_data_array = array();
        foreach( $mapping as $zoho_field_api_name => $cf7_fields_mappings ){
            if( $zoho_field_api_name == 'zoho_attachment' ||
                $zoho_field_api_name == 'Record_Image' ||
                $zoho_field_api_name == 'Owner' ){
                continue;
            }
            
            if( strpos( $zoho_field_api_name, '_BSK_ZOHO_picklist_option' ) !== false ){
                
                if( is_array( $cf7_fields_mappings ) && 
                    count( $cf7_fields_mappings ) ){

                    $zoho_wanted_value = $cf7_fields_mappings[0];
                    $zoho_real_api_name = str_replace( '_BSK_ZOHO_picklist_option', '', $zoho_field_api_name );
                    $zoho_fields_data_array[$zoho_real_api_name] = $zoho_wanted_value;
                    continue;
                }
            }
            
            if( is_array( $cf7_fields_mappings ) && count( $cf7_fields_mappings ) > 0 ){
                $field_data_array = array();
                foreach( $cf7_fields_mappings as $cf7_field_name ){
                    if( !isset($_POST[$cf7_field_name]) || $_POST[$cf7_field_name] == '' ){
                        continue;
                    }
                    $field_data_array[] = $_POST[$cf7_field_name];
                }
                if( count($field_data_array) < 1 ){
                    continue;
                }
                //get zoho filed type
                $zoho_wanted_value = false;
                $zoho_filed_data_type = $module_fields_by_api_name[$zoho_field_api_name]['data_type'];
                switch( $zoho_filed_data_type ){
                    case 'text':
                    case 'textarea':
                        foreach( $field_data_array as $key => $val ){
                            if( is_array( $val ) ){
                                $field_data_array[$key] = implode(', ', $val );
                            }else{
                                $field_data_array[$key] = $val;
                            }
                        }
                        $zoho_wanted_value = array_shift( $field_data_array );
                    break;
                    case 'multiselectpicklist';
                        $options_array = array();
                        foreach( $field_data_array as $key => $val ){
                            if( is_array( $val ) && count( $val ) > 0 ){
                                $options_array = array_merge( $options_array, $val );
                            }else{
                                $options_array[] = $val;
                            }
                        }
                        $zoho_wanted_value = array_shift( $options_array );
                    break;
                    case 'boolean':
                        $zoho_wanted_value = false;
                        $temp_value = '';
                        if( is_array( $field_data_array[0] ) && count( $field_data_array[0] ) > 0 ){
                            $temp_value = strtoupper($field_data_array[0][0]);
                        }else{
                            $temp_value = strtoupper($field_data_array[0]);
                        }

                        if( $temp_value == 'YES' || $temp_value == 'TRUE' || intval($temp_value) ){
                            $zoho_wanted_value = true;
                        }
                    break;
                    case 'integer':
                        $temp_value = '';
                        if( is_array( $field_data_array[0] ) && count( $field_data_array[0] ) > 0 ){
                            $temp_value = $field_data_array[0][0];
                        }else{
                            $temp_value = $field_data_array[0];
                        }
                        $zoho_wanted_value = intval( $temp_value );
                    break;
                    case 'date':
                        $zoho_wanted_value = date( 'Y-m-d', current_time('timestamp') );
                        if( count($field_data_array ) == 1 ){
                            $zoho_wanted_value = $field_data_array[0];
                        }else if( count($field_data_array ) == 3 ){
                            $zoho_wanted_value =  $field_data_array[0].'-'.$field_data_array[1].'-'.$field_data_array[2];
                            if( strlen($field_data_array[2]) == 4 ){
                                $zoho_wanted_value =  $field_data_array[2].'-'.$field_data_array[1].'-'.$field_data_array[0];
                            }
                        }
                    break;
                    case 'datetime':
                        $offset  = get_option( 'gmt_offset' );
                        $hours   = (int) $offset;
                        $minutes = ( $offset - floor( $offset ) ) * 60;
                        $offset  = sprintf( '%+03d:%02d', $hours, $minutes );
                        
                        $time_stamp = time();
                        $zoho_wanted_value = date( 'Y-m-d', $time_stamp ) .'T'. date( 'H:i:s', $time_stamp ).$offset;//'2016-04-28T17:59:21+05:30';
                        
                        if( count($field_data_array ) == 1 ){
                            $zoho_wanted_value = $field_data_array[0];
                            if( strlen($zoho_wanted_value) == 10 ){
                                $zoho_wanted_value = $zoho_wanted_value.'T'.date( 'H:i:s', time() ).$offset;
                            }else if( strlen($zoho_wanted_value) == 19 ){
                                $zoho_wanted_value = substr( $zoho_wanted_value, 0, 10 ).'T'.substr( $zoho_wanted_value, 11, 8 ).$offset;
                            }
                        }else if( count($field_data_array ) == 6 ){
                            $zoho_wanted_value =  $field_data_array[0].'-'.$field_data_array[1].'-'.$field_data_array[2];
                            if( strlen($field_data_array[2]) == 4 ){
                                $zoho_wanted_value =  $field_data_array[2].'-'.$field_data_array[1].'-'.$field_data_array[0];
                            }
                            $zoho_wanted_value .= 'T'.$field_data_array[3].':'.$field_data_array[4].':'.$field_data_array[5];
                            $zoho_wanted_value .= $offset;
                        }
                    break;
                    default:
                        $temp_value = '';
                        if( is_array( $field_data_array[0] ) && count( $field_data_array[0] ) > 0 ){
                            $temp_value = array_shift( $field_data_array[0] );
                        }else{
                            $temp_value = $field_data_array[0];
                        }
                        $zoho_wanted_value = $temp_value;
                    break;
                }

                $zoho_fields_data_array[$zoho_field_api_name] = $zoho_wanted_value;
            }
        }
        
        if( count( $zoho_fields_data_array ) < 1 ){
            $logs_array[] = '$zoho_fields_data_array is empty'.serialize( $_POST )."\n\n";
        }else{
            $logs_array[] = '$zoho_fields_data_array: '.serialize( $zoho_fields_data_array )."\n\n";
        }
        
        $to_post_data_array = array();
        $to_post_data_array['trigger'] = array();
        $to_post_data_array['data'] = array();
        $to_post_data_array['data'][] = $zoho_fields_data_array;
        
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

        $zoho_account_from = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        $site_url = 'https://www.zohoapis.com';
        if( $zoho_account_from == 'www.zoho.eu' ){
            $site_url = 'https://www.zohoapis.eu';
        }else if( $zoho_account_from == 'www.zoho.in' ){
            $site_url = 'https://www.zohoapis.in';
        }else if( $zoho_account_from == 'www.zoho.com.cn' ){
            $site_url = 'https://www.zohoapis.com.cn';
        }
        $zoho_respond = wp_remote_post( $site_url.'/crm/v2/'.$module, $args );
		$record_id = '';
        $return_bool = true;
        
        $logs_array[] = $json_data;
        
		if( is_wp_error( $zoho_respond ) ) {
			$error_message = $zoho_respond->get_error_message();
            $return_bool = false;
            $logs_array[] = $error_message;
		}else{
            $zoho_respond_body  = wp_remote_retrieve_body( $zoho_respond );
            $zoho_return_array = json_decode( $zoho_respond_body, true );

            if( isset( $zoho_return_array['data'] ) && is_array( $zoho_return_array['data'] ) && count( $zoho_return_array['data'] ) > 0 && 
                isset( $zoho_return_array['data'][0]['code'] ) ){
                if( $zoho_return_array['data'][0]['code'] == 'SUCCESS' ){
                    $record_id = $zoho_return_array['data'][0]['details']['id'];
                    $return_bool = true;
                }else{
                $return_bool = false;
                    $error_message = $zoho_return_array['data'][0]['message'];
                    $logs_array[] = "\n\n".$error_message;
                    
                    $details_message = array();
                    foreach( $zoho_return_array['data'][0]['details'] as $key => $val ){
                        $details_message[] = $key.' : '.$val; 
                    }
                    $logs_array = array_merge( $logs_array, $details_message );
                }
            }else{
                $return_bool = false;
                $logs_array[] = $zoho_return_array['message'];
            }
        }

        return array( 'return_bool' => $return_bool, 'record_id' => $record_id, 'logs' => $logs_array );
    }
    
    function bsk_cf7_to_zoho_refresh_users_cache_data_fun(){
        BSK_CF7_ZOHO_Common_ZOHO::bsk_cf7_to_zoho_refresh_access_token();
        $access_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
        if( $access_token == "" ){
            $array = array( 'status' => false, 'msg' => 'Error, you haven\'t connected to Zoho yet.' );
            return $array;
        }
        
        $zoho_account_from = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        $site_url = 'https://www.zohoapis.com';
        if( $zoho_account_from == 'www.zoho.eu' ){
            $site_url = 'https://www.zohoapis.eu';
        }else if( $zoho_account_from == 'www.zoho.in' ){
            $site_url = 'https://www.zohoapis.in';
        }else if( $zoho_account_from == 'www.zoho.com.cn' ){
            $site_url = 'https://www.zohoapis.com.cn';
        }
        $url = $site_url.'/crm/v2/users?type=ActiveUsers';
        $headers = array( 'Authorization' => 'Zoho-oauthtoken ' . $access_token, );

        $remote_args = array(
                                'method' => 'GET',
                                'timeout' => 60,
                                'headers' => $headers,
                                'sslverify' => false,
                           );
        $response = wp_remote_post( $url, $remote_args );
        $message = '';
        if( is_wp_error( $response ) ){
            $error_message = $response->get_error_message();            
            $array = array( 'status' => false, 'msg' => $error_message );
            return $array;
        }
        
        $response_body  = wp_remote_retrieve_body( $response );
        $zoho_users_array = json_decode( $response_body, true );
        if( $response['response']['code'] != 200 ){
            $error_message = 'code: '.$zoho_users_array['code'].', message: '.$zoho_users_array['message'];
            $array = array( 'status' => false, 'msg' => $error_message );
            return $array;
        }
        
        $users_array = array();
        if( $zoho_users_array['users'] && is_array( $zoho_users_array['users'] ) && count( $zoho_users_array['users'] ) ){
            foreach( $zoho_users_array['users'] as $user_obj ){
                $users_array[$user_obj['id']] = array();
                $users_array[$user_obj['id']]['full_name'] = $user_obj['full_name'];
                $users_array[$user_obj['id']]['email'] = $user_obj['email'];
            }
        }else{
            $array = array( 'status' => false, 'msg' => 'invlaid data.' );
            return $array;
        }
       
        update_option( self::$_bsk_cf7_to_zoho_users_cache_option, $users_array );
        
        $array = array( 'status' => true, 'msg' => 'Now you have latest users data' );
        return $array;
    }
}
