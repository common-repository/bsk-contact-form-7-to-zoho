<?php

class BSK_CF7_ZOHO_Dashboard_Form_Panel {
    
    private static $_pro_tips_for_lists = array( 
                                                    'Multiple feeds',
                                                    'Mapping multiple form fields to a ZOHO field',
                                                    'Assigning Lead / Contact / Account Owner',
                                                    'ZOHO custom fields',
                                                    'Uploading Lead / Contact / Account image',
                                                    'ZOHO attachments',
                                                    'Triggers( Workflow, Blueprint )'
                                               );
    private static $_pro_tips_for_populating_from_zoho = array(
                                                                'Populating Contact Form 7 fileds from ZOHO Users or Picklist options'
                                                              );
	public function __construct() {
		
        add_action( 'wpcf7_editor_panels', array( $this, 'bsk_cf7_to_zoho_add_panel' ) );
        add_action( 'wpcf7_after_save', array( $this, 'bsk_cf7_to_zoho_save_form_setting' ) );
        add_action( 'wpcf7_after_create', array( $this, 'bsk_cf7_to_zoho_duplicate_form_setting' ) );
        add_action( 'deleted_post', array( $this, 'bsk_cf7_to_zoho_delete_form' ) );
        add_action( 'wp_ajax_bsk_cf7_to_zoho_feed_module_change', array( $this, 'bsk_cf7_to_zoho_feed_module_change_fun' ) );
        add_action( 'wp_ajax_bsk_cf7_to_zoho_feed_active_inactive', array( $this, 'bsk_cf7_to_zoho_feed_active_inactive_fun' ) );
        add_action( 'wp_ajax_bsk_cf7_to_zoho_feed_debug_mode', array( $this, 'bsk_cf7_to_zoho_feed_debug_mode_fun' ) );
        add_action( 'wp_ajax_bsk_cf7_to_zoho_feed_delete', array( $this, 'bsk_cf7_to_zoho_feed_delete_fun' ) );
        add_action( 'wp_ajax_bsk_cf7_to_zoho_get_zoho_picklist_fields', array( $this, 'bsk_cf7_to_zoho_get_zoho_picklist_fields_fun' ) );
        
        add_action( 'bsk_cf7_to_zoho_action_download_feed_last_log', array( $this, 'bsk_cf7_to_zoho_feed_download_feed_last_log_fun' ) );
	}
	
	function bsk_cf7_to_zoho_add_panel( $panels ){
        
        $panels['bsk-cf7-to-zoho-panel'] = array(
                                                                'title'     => __( 'Zoho CRM', 'bsk-cf7-to-zoho' ),
                                                                'callback'  => array( $this, 'bsk_cf7_to_zoho_display_mapping_form' ),
                                                            );
		return $panels;
    }
    
    function bsk_cf7_to_zoho_display_mapping_form( $cf7_post ){
        
        $plugin_settings_page = admin_url( 'admin.php?page='.BSK_CF7_ZOHO_Dashboard::$_bsk_cf7_to_zoho_page );
        
        $client_id = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_id_key, '' );
        $client_secret = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_secret_key, '' );
        $access_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
        $refresh_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, '' );
        $expires = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, '' );
        if( $client_id == '' || $client_secret == '' || $access_token == '' || $refresh_token == '' || $expires == '' ){
            echo '<p>You haven\'t connected to Zoho, please go to <a href="'.$plugin_settings_page.'">plugin setting page</a> to get connected first</p>';
            
            return;
        }
        
        $enabled_modules = $this->bsk_cf7_to_zoho_get_enabled_modules();
        if( !$enabled_modules || !is_array($enabled_modules) || count( $enabled_modules ) < 1 ){
            echo '<p>You haven\'t enabled any modules, please go to <a href="'.$plugin_settings_page.'">plugin setting page</a> to enalbe modules first</p>';
            
            return;
        }
        
        foreach( $enabled_modules as $module ){
            $layouts_data_cache = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_layouts_sections_cache_prefix.$module, false );
            $fields_data_cache = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_fields_cache_prefix.$module, false );
            if( !$layouts_data_cache || !$fields_data_cache ){
                echo '<p>You haven\'t downloaded '.$module.' module data from Zoho, please go to <a href="'.$plugin_settings_page.'">plugin setting page</a> to download module data first</p>';
            
                return;
            }
        }
        
        $cf7_form_id = $cf7_post->id();
        $add_new_page_url = admin_url( 'admin.php?page=wpcf7&post='.$cf7_form_id.'&action=edit&bsk-cf7-to-zoho-action=edit-feed&feed-id=0' );
        
        //feeds list
        $_cf7_to_zoho_feeds = new BSK_CF7_ZOHO_Dashboard_Feeds( $cf7_form_id );
        //Fetch, prepare, sort, and filter our data...
        $_cf7_to_zoho_feeds->prepare_items();
        $feeds_count = $_cf7_to_zoho_feeds->get_results_count();
        ?>
        <div class="bsk-cf7-to-zoho-form-panel">
            <?php
            $this->bsk_pdf_manager_show_pro_tip_box( self::$_pro_tips_for_lists );
            ?>
            <h2>Zoho Feed</h2>
            <p>You may create Zoho feeds to insert records to your Zoho modules: <?php echo implode(', ', $enabled_modules ); ?></p>
            <hr />
            <?php
            if( isset( $_GET['bsk-cf7-to-zoho-action'] ) && $_GET['bsk-cf7-to-zoho-action'] == 'edit-feed' ){
                $feed_id = isset( $_GET['feed-id'] ) ? absint( $_GET['feed-id']  ) : 0;
                $this->bsk_cf7_edit_feed_form( $cf7_form_id, $feed_id );
            }else{
                if( $feeds_count < 1 ){
                    echo '<h2><a href="'.$add_new_page_url.'" class="add-new-h2">Add New</a></h2>';
                }
            ?>
            <div class="bsk-cf7-to-zoho-form-feeds-list-container" style="display: <?php echo 'block'; ?>;">
            <?php
                $_cf7_to_zoho_feeds->display();

                $ajax_nonce = wp_create_nonce( "bsk-cf7-to-zoho-form-feeds-list-ajax-nonce" ); 
                $save_nonce = wp_create_nonce( "bsk-cf7-to-zoho-form-feeds-list-operation-nonce" ); 
            ?>
                <input type="hidden" value="<?php echo $ajax_nonce; ?>" id="bsk_cf7_to_zoho_form_feeds_list_ajax_nonce_ID" />
                <input type="hidden" value="<?php echo $save_nonce; ?>" name="bsk_cf7_to_zoho_form_feeds_operation_nonce" />
            </div>
            <?php
                if( isset( $_GET['bsk-cf7-to-zoho-action'] ) && $_GET['bsk-cf7-to-zoho-action'] == 'cancel-edit-feed' ){
                ?>
                <script type="text/javascript">
                    function activate_zoho_panel_4_cancel() {
                        jQuery("#bsk-cf7-to-zoho-panel-tab").find( "a" ).click();
                    }
                    setTimeout( activate_zoho_panel_4_cancel, 1000 );
                </script>
                <?php
                } //end of script
                
                $this->bsk_cf7tozoho_populating_form_settings( $cf7_form_id );
            }//end of else
            ?>
        </div>
        <?php
    }
    
    function bsk_cf7_to_zoho_get_form_fields( $post_id ) {
		$contact_form = WPCF7_ContactForm::get_instance( $post_id );
		$manager = WPCF7_FormTagsManager::get_instance();

		$form_fields = $manager->scan( $contact_form->prop( 'form' ) );

		return $form_fields;
	}
    
    function bsk_cf7_to_zoho_save_form_setting( $contact_form ){
        if ( ! isset( $_POST ) || empty( $_POST ) ) {
			return;
        }
        
        if ( ! wp_verify_nonce( $_POST['bsk_cf7_to_zoho_form_feed_save_nonce'], 'bsk-cf7-to-zoho-form-feed-save' ) ) {
            return;
        }
        
        $form_id = $contact_form->id();
        $feed_id = absint( $_POST['bsk_cf7_to_zoho_form_feed_id'] );
        
        global $wpdb;
        $sql = 'SELECT COUNT(*) FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `form_id` = %d';
        $wpdb->prepare( $sql, $form_id );
        if( $wpdb->get_var( $sql ) ){
            add_action( 'admin_notices', array($this, 'bsk_cf7_to_zoho_only_one_feed_fun') );
            return;
        }
        
        $mapping_array = $_POST['bsk_cf7_to_zoho_mapping_form_fields'];
        foreach( $mapping_array as $zoho_api_name => $cf7_fields_array ){
            foreach( $cf7_fields_array as $cf7_field_key => $cf7_field_name ){
                if( $cf7_field_name == '' ){
                    unset( $cf7_fields_array[$cf7_field_key] );
                }  
            }
            if( count( $cf7_fields_array ) < 1 ){
                unset( $mapping_array[$zoho_api_name] );
            }else if( count( $cf7_fields_array ) > 1 ){
                 $mapping_array[$zoho_api_name] = array_shift( $cf7_fields_array );
            }
        }
        
        //organise form setting
        $form_settings = array();
        $form_settings['name'] = trim( wp_unslash($_POST['bsk_cf7_to_zoho_form_feed_name'] ) );
        $form_settings['module'] = $_POST['bsk_cf7_to_zoho_form_feed_module'];
        $form_settings['active'] = $_POST['bsk_cf7_to_zoho_form_feed_status'];
        $form_settings['approve'] = 0;
        $form_settings['workflow'] = 0;
        $form_settings['blueprint'] = 0;
        $form_settings['mapping'] = serialize( $mapping_array );
        $form_settings['date'] = date('Y-m-d H:i:s', current_time( 'timestamp' ) );
        
        if( $feed_id > 0 ){
            $wpdb->update( $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, $form_settings, array( 'id' => $feed_id, 'form_id' => $form_id ) );
            $sql = 'DELETE FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `form_id` = %d AND `id` NOT IN( %d )';
            $wpdb->prepare( $sql, $form_id, $feed_id );
            $wpdb->query( $sql );
        }else{
            $sql = 'DELETE FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `form_id` = %d';
            $wpdb->prepare( $sql, $form_id );
            $wpdb->query( $sql );
            $form_settings['form_id'] = $form_id;
            $wpdb->insert( $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, $form_settings, array( '%s', '%s', '%d', '%s', '%s', '%d') );
        }
        
        add_action( 'admin_notices', array( $this, 'cf7_to_zoho_feed_saved_fun' ) );
    }
    
    function bsk_cf7_to_zoho_duplicate_form_setting( $contact_form ) {
		$contact_form_id = $contact_form->id();
        
        global $wpdb;
        
		if ( ! empty( $_REQUEST['post'] ) && ! empty( $_REQUEST['_wpnonce'] ) ) {
			$old_form_id = intval( $_REQUEST['post'] );
            
            $old_form_mappings = array();
            $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` '.
                      'WHERE `form_id` = %d ';
            $sql = $wpdb->prepare( $sql, $old_form_id );
            $results = $wpdb->get_results( $sql );
            if( !$results || !is_array( $results ) || count( $results ) < 1 ){
                return;
            }
            foreach( $results as $mapping_obj ){
                $data_to_insert = array();
                $data_to_insert['form_id'] = $contact_form_id;
                $data_to_insert['name'] = $mapping_obj->name;
                $data_to_insert['module'] = $mapping_obj->module;
                $data_to_insert['active'] = $mapping_obj->active;
                $data_to_insert['mapping'] = $mapping_obj->mapping;
                $data_to_insert['date'] = date( 'Y-m-d H:i:s', current_time('timestamp') );
                $data_to_insert['debug'] = $mapping_obj->debug;
                $data_to_insert['last_log'] = '';
                $data_to_insert['approve'] = $mapping_obj->approve;
                $data_to_insert['workflow'] = $mapping_obj->workflow;
                $data_to_insert['blueprint'] = $mapping_obj->blueprint;

                $wpdb->insert( 
                                        $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, 
                                        $data_to_insert, 
                                        array( '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%d', '%d', '%d') 
                                     );
            }
		}
	}
    
    function bsk_cf7_to_zoho_delete_form( $post_id ){
        $post = get_post( $post_id );
        if( $post->post_type != 'wpcf7_contact_form' ){
            return;
        }
        global $wpdb;
        
        $sql = 'DELETE FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` '.
                  'WHERE `form_id` = %d ';
        $sql = $wpdb->prepare( $sql, $post_id );
        $wpdb->query( $sql );
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
    
    function bsk_cf7_edit_feed_form( $form_id, $feed_id ){
        $form_fields = $this->bsk_cf7_to_zoho_get_form_fields( $form_id );
        $enabled_modules = $this->bsk_cf7_to_zoho_get_enabled_modules();
        
        global $wpdb;
        
        $feed_obj = false;
        $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` '.
                  'WHERE `form_id` = %d '.
                  'AND `id` = %d ';
        $sql = $wpdb->prepare( $sql, $form_id, $feed_id );
        $results = $wpdb->get_results( $sql );
        if( $results && is_array( $results ) && count( $results ) > 0 ){
            $feed_obj = $results[0];
        }
        
        $cancel_edit_url = admin_url( 'admin.php?page=wpcf7&post='.$form_id.'&action=edit&bsk-cf7-to-zoho-action=cancel-edit-feed' );
        ?>
        <div class="bsk-cf7-to-zoho-form-feed-settings-container">
            <p style="text-align: right;"><a href="<?php echo $cancel_edit_url; ?>" class="button button-primary">Cancel</a></p>
            <p>
                <label class="bsk-cf7-to-zoho-form-feed-settings-label">Feed Name: </label>
                <input type="text" name="bsk_cf7_to_zoho_form_feed_name" value="<?php echo $feed_obj ? $feed_obj->name : 'Need feed'; ?>" class="bsk-cf7-to-zoho-form-feed-name" />
            </p>
            <p>
                <label class="bsk-cf7-to-zoho-form-feed-settings-label">Feed Status: </label>
                <select name="bsk_cf7_to_zoho_form_feed_status" class="bsk-cf7-to-zoho-form-feed-status">
                    <?php
                    $active_selected = ' selected';
                    $inactive_selected = '';
                    if( $feed_obj && $feed_obj->active == false ){
                        $inactive_selected = ' selected';
                    }
                    ?>
                    <option value="1"<?php echo $active_selected; ?>>Active</option>
                    <option value="0"<?php echo $inactive_selected; ?>>Inactive</option>
                </select>
            </p>
            <p>
                <label class="bsk-cf7-to-zoho-form-feed-settings-label">Zoho Module: </label>
                <select name="bsk_cf7_to_zoho_form_feed_module" class="bsk-cf7-to-zoho-form-feed-module">
                    <option value=""<?php if( !$feed_obj || $feed_obj->module == "" || !in_array( $feed_obj->module, $enabled_modules ) ) echo ' selected'; ?>>Select...</option>
                    <?php
                    foreach( $enabled_modules as $module ){
                        $selected = '';
                        if( $feed_obj && $feed_obj->module == $module ){
                            $selected = ' selected';
                        }
                        echo '<option value="'.$module.'"'.$selected.'>'.$module.'</option>';
                    }
                    ?>
                </select>
                <span style="display: none; margin-left: 20px;" class="bsk-cf7-to-zoho-form-feed-module-change-ajax-loader">
                    <img src="<?php echo BSK_CF7_ZOHO_URL; ?>images/ajax-loader.gif" />
                </span>
            </p>
            <?php
                $workflow_checked = $feed_obj && $feed_obj->workflow ? ' checked' : ' ';
                $blueprint_checked = $feed_obj && $feed_obj->blueprint ? ' checked' : ' ';
            ?>
            <p>
                <label class="bsk-cf7-to-zoho-form-feed-settings-label">Enable Automating Workflow</label>
                <label class="bsk-cf7-to-zoho-form-feed-checkbox-label">
                    <input type="checkbox" name="bsk_cf7_to_zoho_form_feed_workflow" class="bsk-cf7-to-zoho-form-feed-workflow" value="1" disabled /> Yes
                </label>
                <span style="margin-left: 20px;" class="bsk-cf7-to-zoho-read-more">
                    read more about <a href="https://www.zoho.com/crm/help/automation/manage-workflow.html" target="_blank">Automating Workflow</a>
                </span>
            </p>
            <p>
                <label class="bsk-cf7-to-zoho-form-feed-settings-label">Enable Blueprint</label>
                <label class="bsk-cf7-to-zoho-form-feed-checkbox-label">
                    <input type="checkbox" name="bsk_cf7_to_zoho_form_feed_blueprint" class="bsk-cf7-to-zoho-form-feed-blueprint" value="1" disabled /> Yes
                </label>
                <span style="margin-left: 20px;" class="bsk-cf7-to-zoho-read-more">
                    read more about <a href="https://www.zoho.com/crm/help/blueprint/" target="_blank">Blueprint</a>
                </span>
            </p>
            <h2 style="margin-top: 20px;">Field Mapping</h2>
            <div class="bsk-cf7zoho-form-feed-mapping-container">
                <?php 
                if( $feed_obj ){
                    $mapping_trs = $this->bsk_cf7_to_zoho_get_form_mapping( $feed_obj->module, $form_fields, unserialize( $feed_obj->mapping ) );
                    echo $mapping_trs;
                }else{
                    echo '<h4>Choose Zoho Module to load field mapping...</h4>';
                }
                ?>
            </table>
                <div style="clear: both;"></div>
            </div>
            <p class="bsk-cf7-to-zoho-feed-mandatory-fields-alert-msg"></p>
            <script type="text/javascript">
                function activate_zoho_panel() {
                    jQuery("#bsk-cf7-to-zoho-panel-tab").find( "a" ).click();
                }
                setTimeout( activate_zoho_panel, 700 );
            </script>
            <?php 
                $ajax_nonce = wp_create_nonce( "bsk-cf7-to-zoho-form-feed-ajax" ); 
                $save_nonce = wp_create_nonce( "bsk-cf7-to-zoho-form-feed-save" ); 
            ?>
            <input type="hidden" value="<?php echo $ajax_nonce; ?>" id="bsk_cf7_to_zoho_form_feed_ajax_nonce_ID" />
            <input type="hidden" value="<?php echo $save_nonce; ?>" name="bsk_cf7_to_zoho_form_feed_save_nonce" />
            <input type="hidden" value="<?php echo $form_id; ?>" id="bsk_cf7_to_zoho_form_feed_form_id_ID" />
            <input type="hidden" value="<?php echo $feed_id; ?>" name="bsk_cf7_to_zoho_form_feed_id" id="bsk_cf7_to_zoho_form_feed_id_ID" />
        </div>
        <?php
    }
    
    function bsk_cf7_to_zoho_get_form_mapping( $module, $form_fields, $feed_mapping ){
        
        if( $module == "" ){
            return '';
        }
        
        $layouts_data_cache = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_layouts_sections_cache_prefix.$module, false );
        $fields_data_cache = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_fields_cache_prefix.$module, false );
        
        //add owner
        $owner_label = 'Lead Owner';
        if( $module == 'Contacts' ){
            $owner_label = 'Contact Owner';
        }else if( $module == 'Accounts' ){
            $owner_label = 'Account Owner';
        }
        $fields_data_cache['Owner'] = array( 'label' => $owner_label, 'data_type' => 'integer', 'required' => false );

        
        $mapping_body = ''; 
        foreach( $layouts_data_cache as $layout_obj ){
            $mapping_body .= '<div class="bsk-cf7-to-zoho-mapping-form-zoho-layout">
                                 <h2 class="bsk-cf7zoho-layout-title">'.$layout_obj['name'].' Layout</h2>';
            if( $layout_obj['sections'] && is_array( $layout_obj['sections'] ) && count( $layout_obj['sections'] ) > 0 ){
                foreach( $layout_obj['sections'] as $section_obj ){
                    $mapping_body .= '
                    <div class="bsk-cf7-to-zoho-mapping-form-zoho-section">
                        <h3>'.$section_obj['label'].'</h3>
                        <table class="widefat striped">
                            <thead>
                                <th style="width: 50%;">Zoho Field</th>
                                <th style="width: 40%;">Form Field Name</th>
                            </thead>
                            <tbody class="bsk-cf7zoho-zoho-fields-section">';
                    
                    //adjust ZOHO fields display sequence
                    $section_obj = $this->bsk_cf7zoho_adjust_zoho_fields_sequence( $section_obj, $module );
                    if( $section_obj['fields'] && is_array( $section_obj['fields'] ) && count( $section_obj['fields'] ) > 0 ){
                        foreach( $section_obj['fields'] as $zoho_api_name ){
                            $field_obj = $fields_data_cache[$zoho_api_name];
                            $zoho_field_label = $field_obj['label'];
                            
                            //adjust ZOHO fields display sequence
                            $zoho_field_label = $this->bsk_cf7zoho_adjust_zoho_fields_label( $zoho_api_name, $zoho_field_label, $module );
                            $form_fields_select_class = 'bsk-cf7zoho-'.sanitize_title( $zoho_field_label );
                            
                            $mapping_body .= '
                            <tr>
                                <td>'.$zoho_field_label.( $field_obj['required'] ? '<span class="bsk-cf7-to-zoho-mandatory">*</span>' : '' ).'</td>
                                <td class="bsk-cf7zoho-form-fields">';
                                    if( $feed_mapping && is_array( $feed_mapping ) && count( $feed_mapping ) > 0 && 
                                        isset( $feed_mapping[$zoho_api_name] ) && 
                                        is_array( $feed_mapping[$zoho_api_name] ) && 
                                        count( $feed_mapping[$zoho_api_name] ) > 0 ){

                                        foreach( $feed_mapping[$zoho_api_name] as $cf7_field_name ){
                                            $mapping_body .= $this->bsk_cf7zoho_mapping_display_form_fields_select( $module, $zoho_api_name, $field_obj, $form_fields, $cf7_field_name, $form_fields_select_class, $feed_mapping );
                                        }
                                    }else{
                                        $mapping_body .= $this->bsk_cf7zoho_mapping_display_form_fields_select( $module, $zoho_api_name, $field_obj, $form_fields, false, $form_fields_select_class, $feed_mapping );
                                    }
                            
                            $mapping_body .= '
                                </td>
                            </tr>';
                        } //end for each fields
                    }
                    $mapping_body .= '</tbody>
                            </table>
                            <div style="clear:both;"></div>
                        </div>';
                }//end of section
            }
            $mapping_body .= '
                    <div style="clear:both;"></div>
                </div>';
        }//end of layout

        return $mapping_body;
    }
    
    function bsk_cf7zoho_mapping_display_form_fields_select( $module, $zoho_api_name, $zoho_field_obj, $form_fields, $saved_cf7_field_name, $class, $feed_mapping ){
        $class = $class ? ' '.$class : '';
        $mapping_body = '';
        $mapping_body .= '
            <p>
                <select name="bsk_cf7_to_zoho_mapping_form_fields['.$zoho_api_name.'][]" class="bsk-cf7-to-zoho-mapping-form-fields-select'.$class.'" style="width: 70%;">';
        $select_option_null = '<option value="">Select a form field...</option>';
        $select_valid_opions = '';
        foreach( $form_fields as $form_field ){
            if( $form_field->name == "" ){
                continue;
            }
            if( in_array( $zoho_field_obj['data_type'], array( 'profileimage', 'file' ) ) ){
                if( $form_field->basetype != 'file' ){
                    continue;
                }
            }
            $selected = '';
            if( $saved_cf7_field_name && $saved_cf7_field_name == $form_field->name ){
                $selected = ' selected';
            }
            //$select_valid_opions .= '<option value="'.$form_field->name.'">'.$form_field->name.'</option>';
            $select_valid_opions .= '<option value="'.$form_field->name.'"'.$selected.'>'.$form_field->name.'</option>';
        }
        if( $select_valid_opions == '' ){
            $select_option_null = '<option value="">No valid form field found...</option>';
            if( in_array( $zoho_field_obj['data_type'], array( 'profileimage', 'file' ) ) ){
                $select_option_null = '<option value="">No file upload field found in form...</option>';
            }
        }
        $mapping_body .= $select_option_null;
        $mapping_body .= $select_valid_opions;

        $mapping_body .= '
            </select>';

        if( in_array( $zoho_api_name, BSK_CF7_ZOHO_Modules::$bsk_cf7zoho_no_multiple_mapping_api_name ) ||
            $zoho_field_obj['data_type'] == 'integer' ||
            $zoho_field_obj['data_type'] == 'picklist' ||
            $zoho_api_name == 'Owner' ){

            if( $zoho_api_name == 'Owner' ){

                $mapping_body .= '
                    </p>
                    <p>
                        <select name="bsk_cf7_to_zoho_mapping_form_fields['.$zoho_api_name.'_BSK_ZOHO_picklist_option][]" class="bsk-cf7zoho-mapping-form-fields-zoho-picklist-options-select'.$class.'" style="width: 70%;">
                            <option value="">Select a user from ZOHO...</option>';
                $users_array = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_users_cache_option, false );
                if( $users_array && is_array( $users_array ) && count( $users_array ) ){
                    foreach( $users_array as $zoho_user_ID => $zoho_user_data  ){
                        $selected = '';
                        if( $feed_mapping && isset($feed_mapping) && isset($feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option']) &&     
                            is_array($feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option']) && count( $feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option'] ) &&
                            $feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option'][0] == $zoho_user_ID ){

                            $selected = ' selected';
                        }
                        $label = $zoho_user_data['full_name'].' - '.$zoho_user_data['email'];
                        $mapping_body .= '<option value="'.$zoho_user_ID.'"'.$selected.'>'.$label.'</option>';
                    }
                }
                $mapping_body .= '
                        </select>
                    </p>';
            }else if( $zoho_field_obj['data_type'] == 'picklist' ){

                $mapping_body .= '
                    </p>
                    <p>
                        <select name="bsk_cf7_to_zoho_mapping_form_fields['.$zoho_api_name.'_BSK_ZOHO_picklist_option][]" class="bsk-cf7zoho-mapping-form-fields-zoho-picklist-options-select'.$class.'" style="width: 70%;">
                            <option value="">Select an option from ZOHO...</option>';
                $zoho_picklist = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_picklist_fields_options_cache_prefix.$module, false );
                if( $zoho_picklist && is_array( $zoho_picklist ) && count( $zoho_picklist ) && isset( $zoho_picklist[$zoho_api_name] ) ){
                    if( isset( $zoho_picklist[$zoho_api_name]['options'] ) && 
                        is_array( $zoho_picklist[$zoho_api_name]['options'] ) && 
                        count( $zoho_picklist[$zoho_api_name]['options'] ) ){

                        foreach( $zoho_picklist[$zoho_api_name]['options'] as $zoho_option ){
                            $selected = '';
                            if( $feed_mapping && isset($feed_mapping) && isset($feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option']) &&     
                                is_array($feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option']) && count( $feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option'] ) &&
                                $feed_mapping[$zoho_api_name.'_BSK_ZOHO_picklist_option'][0] == $zoho_option['actual_value'] ){

                                $selected = ' selected';
                            }
                            $mapping_body .= '<option value="'.$zoho_option['actual_value'].'"'.$selected.'>'.$zoho_option['display_value'].'</option>';
                        }
                    }
                }
                $mapping_body .= '
                        </select>
                    </p>';
            }

        }else{
            $mapping_body .= '
                <span class="bsk-cf7-to-zoho-mapping-form-fields-operaion-span">
                    <a href="javascript:void(0);" class="bsk-cf7-to-zoho-mapping-form-fields-plus">&#43;</a>
                </span>
            </p>';
        }

        $mapping_body .= '<p class="bsk-cf7-to-zoho-error-message"></p>';
        
        return $mapping_body;
    }
    
    function bsk_cf7_to_zoho_feed_module_change_fun(){
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-form-feed-ajax', 'nonce', false ) ){
            $array = array( 'status' => false, 'html' => '<tr><td colspan="2">Security check failed or you need refresh the page</td></tr>' );
            wp_die( json_encode( $array ) );
        }

        $module = $_POST['module'];
        $enabled_modules = $this->bsk_cf7_to_zoho_get_enabled_modules();
        if( !in_array( $module, $enabled_modules ) ){
            $array = array( 'status' => false, 'html' => '<tr><td colspan="2">Invalid module</td></tr>' );
            wp_die( json_encode( $array ) );
        }
        $form_id = absint($_POST['form_id']);
        $form_fields = $this->bsk_cf7_to_zoho_get_form_fields( $form_id );
        if( !$form_fields || !is_array( $form_fields ) || count( $form_fields ) < 1 ){
            $array = array( 'status' => false, 'html' => '<tr><td colspan="2">Invalid form ID</td></tr>' );
            wp_die( json_encode( $array ) );
        }
        
        global $wpdb;
        
        $mapping = false;
        $feed_id = absint($_POST['feed_id']);
        if( $feed_id > 0 ){
            $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `id` = %d';
            $sql = $wpdb->prepare( $sql, $feed_id );
            $feed_obj_results = $wpdb->get_results( $sql );
            if( !$feed_obj_results || !is_array( $feed_obj_results ) || count( $feed_obj_results ) < 1 ){
                $array = array( 'status' => false, 'html' => '<tr><td colspan="2">Invalid feed ID</td></tr>' );
                wp_die( json_encode( $array ) );
            }
            $mapping = unserialize( $feed_obj_results[0]->mapping );
        }
        
        $form_mapping = $this->bsk_cf7_to_zoho_get_form_mapping( $module, $form_fields, $mapping );
        if( $form_mapping ){
            $array = array( 'status' => true, 'html' => $form_mapping );
            wp_die( json_encode( $array ) );
        }
        
        $array = array( 'status' => false, 'html' => '<tr><td colspan="2">Failed to get form mapping</td></tr>' );
        wp_die( json_encode( $array ) );
    }
    
    function cf7_to_zoho_feed_saved_fun(){
        ?>
        <div class="notice notice-success is-dismissible">
            <p>Your feed saved.</p>
        </div>
        <?php
    }
    
    function bsk_cf7_to_zoho_feed_active_inactive_fun(){
        
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-form-feeds-list-ajax-nonce', 'nonce', false ) ){
            $array = array( 'status' => false, 'msg' => 'Security check!' );
            wp_die( json_encode( $array ) );
        }
        global $wpdb;
        
        $feed_id = absint($_POST['feed_id']);
        $sql = 'SELECT `active` FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `id` = %d';
        $sql = $wpdb->prepare( $sql, $feed_id );
        
        $feed_obj_results = $wpdb->get_results( $sql );
        if( !$feed_obj_results || !is_array( $feed_obj_results ) || count( $feed_obj_results ) < 1 ){
            $array = array( 'status' => false, 'msg' => 'Invalid feed ID!' );
            wp_die( json_encode( $array ) );
        }
        $active = $feed_obj_results[0]->active;
        $new_active = $active ? 0 : 1;
        
        $wpdb->update( $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, array( 'active' => $new_active ),  array( 'id' => $feed_id ) );
        $status_img = $new_active ? BSK_CF7_ZOHO_URL.'images/active.png' : BSK_CF7_ZOHO_URL.'images/in-active.png';
        $alt = $new_active ? 'Active' : 'Inactive';
        
        $array = array( 'status' => true, 'src' => $status_img, 'alt' => $alt );
        wp_die( json_encode( $array ) );
    }
    
    function bsk_cf7_to_zoho_feed_debug_mode_fun(){
        
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-form-feeds-list-ajax-nonce', 'nonce', false ) ){
            $array = array( 'status' => false, 'msg' => 'Security check!' );
            wp_die( json_encode( $array ) );
        }
        global $wpdb;
        
        $feed_id = absint($_POST['feed_id']);
        $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `id` = %d';
        $sql = $wpdb->prepare( $sql, $feed_id );
        
        $feed_obj_results = $wpdb->get_results( $sql );
        if( !$feed_obj_results || !is_array( $feed_obj_results ) || count( $feed_obj_results ) < 1 ){
            $array = array( 'status' => false, 'msg' => 'Invalid feed ID!' );
            wp_die( json_encode( $array ) );
        }
        $form_feed = $feed_obj_results[0];

        $new_debug_mode = absint( $_POST['checked'] ) ? true : false;
        $wpdb->update( $wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name, array( 'debug' => $new_debug_mode ),  array( 'id' => $feed_id ) );
        $download_link = '';
        if( $new_debug_mode && $form_feed->last_log ){
            $download_link = add_query_arg( array( 
                                                                        'bsk-cf7-to-zoho-action' => 'download-feed-last-log',
                                                                        'feed_id' => $form_feed->id,
                                                                     ), 
                                                             $action_url );
            $download_link = wp_nonce_url( $download_link, 'download-feed-last-log', '_wpnonce' );
            $download_link = '<a href="'.$download_link.'" class="bsk-cf7-to-zoho-feed-last-log-download"><span class="dashicons dashicons-download"></span></a>';
        }
        
        $array = array( 'status' => true, 'download_link' => $download_link );
        wp_die( json_encode( $array ) );
    }
    
    function bsk_cf7_to_zoho_feed_delete_fun(){
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-form-feeds-list-ajax-nonce', 'nonce', false ) ){
            $array = array( 'status' => false, 'msg' => 'Security check!' );
            wp_die( json_encode( $array ) );
        }
        global $wpdb;
        
        $feed_id = absint($_POST['feed_id']);
        $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `id` = %d';
        $sql = $wpdb->prepare( $sql, $feed_id );
        
        $feed_obj_results = $wpdb->get_results( $sql );
        if( !$feed_obj_results || !is_array( $feed_obj_results ) || count( $feed_obj_results ) < 1 ){
            $array = array( 'status' => false, 'msg' => 'Invalid feed ID!' );
            wp_die( json_encode( $array ) );
        }
        $sql = 'DELETE FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `id` = %d';
        $sql = $wpdb->prepare( $sql, $feed_id );
        $wpdb->query( $sql );
        
        $array = array( 'status' => true, 'msg' => 'Feed deleted' );
        wp_die( json_encode( $array ) );
    }
    
    function bsk_cf7_to_zoho_feed_download_feed_last_log_fun( $data ){
        if( ! wp_verify_nonce( $data['_wpnonce'], 'download-feed-last-log' ) ){
            wp_die( 'ERROR - Invaid nonce' );
        }
        
        if( !current_user_can( 'manage_options' ) ){
            wp_die( 'ERROR - you are not allowed to visit this' );
        }
        
        ob_clean();
        
        $log_file_name = 'bsk_cf7_to_zoho_debug_log_for_feed_'.$data['feed_id'].'.txt';
        //
        global $wpdb;
        
        $feed_id = absint($data['feed_id']);
        $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `id` = %d';
        $sql = $wpdb->prepare( $sql, $feed_id );
        
        $feed_obj_results = $wpdb->get_results( $sql );
        if( !$feed_obj_results || !is_array( $feed_obj_results ) || count( $feed_obj_results ) < 1 ){
            header("Content-type: text/plain");
            header("Content-Transfer-Encoding: binary");
            header("Content-Disposition: attachment; filename=".$log_file_name);
            header("Pragma: no-cache");
            header("Expires: 0");
            
            echo 'No log found';
            
            exit;
        }
        $form_feed = $feed_obj_results[0];
        $logs_array = unserialize( $form_feed->last_log );
        $logs_string = implode( "\n", $logs_array );
        
        $log_data = '';
        $log_data .= $logs_string;
        $log_data .= "\n";
        
        $log_file_name = 'bsk_cf7_to_zoho_debug_log_for_feed_'.$data['feed_id'].'_'.date('Y-m-d_H-i-s', strtotime($form_feed->last_log_time) ).'.txt';
        
        header("Content-type: text/plain");
        header("Content-Transfer-Encoding: binary");
        header("Content-Disposition: attachment; filename=".$log_file_name);
        header("Pragma: no-cache");
        header("Expires: 0");

        echo $log_data;

        exit;
    }
    
    function bsk_cf7_to_zoho_only_one_feed_fun(){
        $class = 'notice notice-error';
        $message = 'Free verion only allow one feed for every form'.

        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
    }
    
    function bsk_pdf_manager_show_pro_tip_box( $tips_array ){
        $tips = implode( ', ', $tips_array );
		$str = 
        '<div class="bsk-pro-tips-box">
			<b>Pro Tip: </b><span class="bsk-pro-tips-box-tip">'.$tips.' only supported in Pro version</span>
			<a href="'.BSK_CF7_ZOHO::$_url_to_upgrade.'" target="_blank">Upgrade to Pro</a>
		</div>';
		
		echo $str;
	}
    
    function bsk_cf7tozoho_populating_form_settings( $cf7_form_id ){
        ?>
        <h2 style="margin-top: 40px;">Populating form field from ZOHO Users or picklist options</h2>
        <p>Here you may populate Contact Form 7 <span class="bsk-cf7-bold">checkbox</span> and <span class="bsk-cf7-bold">dropdown</span> options from Zoho <span class="bsk-cf7-bold">picklist( dropdown )</span> otpions.</p>
        <p>With this setting the form field will have same options as the selected ZOHO field</p>
        <?php
        $this->bsk_pdf_manager_show_pro_tip_box( self::$_pro_tips_for_populating_from_zoho );

        $enabled_modules = $this->bsk_cf7_to_zoho_get_enabled_modules();
        $has_cached_picklist_options = false;
        foreach( $enabled_modules as $module ){
            $cached_data = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_picklist_fields_options_cache_prefix.$module, false );
            if( $cached_data && is_array($cached_data) && count($cached_data) > 0 ){
                $has_cached_picklist_options = true;
                break;
            }
        }

        if( !$has_cached_picklist_options ){
            $refresh_module_data_url = admin_url( 'admin.php?page='.BSK_CF7_ZOHO_Dashboard::$_bsk_cf7_to_zoho_page );
            $refresh_module_data_url = add_query_arg( 
                                                      array( 
                                                            'target-tab' => 'modules',
                                                         ), 
                                                      $refresh_module_data_url 
                                                    );
            ?>
            <p>You have no Zoho picklist options cached, please <a href="<?php echo $refresh_module_data_url; ?>">refresh module data</a> here first!</p>
            <?php
        }else{
            $form_fields = $this->bsk_cf7_to_zoho_get_form_fields( $cf7_form_id );

            $form_populating_setting = get_option( BSK_CF7_ZOHO::$_form_populating_settings_option_pre.$cf7_form_id.'_data', false );
            ?>
            <div class="bsk-cf7-to-zoho-form-fields-populating-container">
                <table class="widefat striped">
                    <thead>
                        <th style="width: 40%;">Form Field</th>
                        <th style="width: 55%;">Zoho Users / Picklist fields</th>
                    </thead>
                    <tbody>
                    <?php
                    $has_checkbox_or_dropdown = false;
                    if( $form_fields && is_array( $form_fields ) && count( $form_fields ) ){
                        //add Ueers to select
                        $enabled_modules[] = 'Users';
                        
                        foreach( $form_fields as $field_obj ){
                            if( $field_obj->basetype != 'checkbox' && $field_obj->basetype != 'select' ){
                                continue;
                            }
                            if( $field_obj->name == '' ){
                                continue;
                            }
                            $has_checkbox_or_dropdown = true;
                            $module_select_options = '';
                            $selected_module = '';
                            $none_selected = ' selected';
                            foreach( $enabled_modules as $module ){
                                $selected_str = '';
                                if( $form_populating_setting && 
                                    isset( $form_populating_setting[$field_obj->name]) &&
                                    $form_populating_setting[$field_obj->name]['module'] == $module ){
                                    $selected_module = $module;
                                    $selected_str = ' selected';
                                    $none_selected = '';
                                }
                                $module_select_options .= '<option value="'.$module.'"'.$selected_str.'>'.$module.'</option>';
                            }

                            $fields_select_disabled = ' disabled';
                            $fields_none_selected = ' selected';
                            $fields_select_options = '';
                            if( $selected_module && $selected_module != 'Users' ){
                                //get fields for the module
                                $fields_select_disabled = '';

                                $cached_data = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_picklist_fields_options_cache_prefix.$selected_module, false );

                                if( $cached_data && is_array($cached_data) && count($cached_data) > 0 ){
                                    foreach( $cached_data as $api_name => $picklist_obj ){
                                        $selected_str = '';
                                        if( isset($form_populating_setting[$field_obj->name]['zoho_field']) &&
                                            $form_populating_setting[$field_obj->name]['zoho_field'] == $api_name ){
                                            $selected_str = ' selected';
                                            $fields_none_selected = '';
                                        }
                                        $fields_select_options .= '<option value="'.$api_name.'"'.$selected_str.'>'.$picklist_obj['label'].'</option>';
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td><?php echo $field_obj->name; ?></td>
                                <td>
                                    <select name="bsk_cf7_to_zoho_populate_form_field_module[<?php echo $field_obj->name; ?>]" class="bsk-cf7-to-zoho-populate-form-field-module-select">
                                        <option value=""<?php echo $none_selected; ?>>Select Zoho module...</option>
                                        <?php echo $module_select_options; ?>
                                    </select>
                                    <?php
                                    $fields_select_display = '';
                                    if( $selected_module == 'Users' ){
                                        $fields_select_display = 'style="display: none;"';
                                    }
                                    ?>
                                    <select name="bsk_cf7_to_zoho_populate_form_field_zoho_field[<?php echo $field_obj->name; ?>]" class="bsk-cf7-to-zoho-populate-form-field-zoho-field-select"<?php echo $fields_select_disabled; ?> <?php echo $fields_select_display; ?>>
                                        <option value=""<?php echo $fields_none_selected; ?>>Select Zoho field...</option>
                                        <?php echo $fields_select_options; ?>
                                    </select>
                                    <span class="bsk-cf7-to-zoho-ajax-loader" style="display: none;"><?php echo BSK_CF7_ZOHO::$ajax_loader;?></span>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    
                    if( $has_checkbox_or_dropdown == false ){
                        $module_select_options = '';
                        foreach( $enabled_modules as $module ){
                            $selected_str = '';
                            if( $form_populating_setting && 
                                isset( $form_populating_setting[$field_obj->name]) &&
                                $form_populating_setting[$field_obj->name]['module'] == $module ){
                                $selected_module = $module;
                                $selected_str = ' selected';
                                $none_selected = '';
                            }
                            $module_select_options .= '<option value="'.$module.'"'.$selected_str.'>'.$module.'</option>';
                        }
                    ?>
                        <tr>
                            <td>No checkbox or dropdown found in the form</td>
                            <td>
                                <select name="bsk_cf7_to_zoho_populate_form_field_module_no_field" class="bsk-cf7-to-zoho-populate-form-field-module-select">
                                    <option value="" selected>Select ZOHO module...</option>
                                    <?php echo $module_select_options; ?>
                                </select>
                                <select name="bsk_cf7_to_zoho_populate_form_field_zoho_field_no_field" class="bsk-cf7-to-zoho-populate-form-field-zoho-field-select">
                                    <option value="" selected>Select ZOHO field...</option>
                                    <?php echo $fields_select_options; ?>
                                </select>
                                <span class="bsk-cf7-to-zoho-ajax-loader" style="display: none;"><?php echo BSK_CF7_ZOHO::$ajax_loader;?></span>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
                
                <?php
                $ajax_nonce = wp_create_nonce( "bsk-cf7-to-zoho-populating-form-fields-setting-ajax-nonce" );
                $save_nonce = wp_create_nonce( "bsk-cf7tozoho-populating-form-save" ); 
                ?>
                <input type="hidden" class="bsk-cf7tozoho-populating-form-ajax-nonce" value="<?php echo $ajax_nonce; ?>" />
                <input type="hidden" value="<?php echo $save_nonce; ?>" name="bsk_cf7tozoho_populating_form_save_nonce" />
                <input type="hidden" value="<?php echo $cf7_form_id; ?>" name="bsk_cf7tozoho_populating_form_save_form_id" />
            </div>
        <?php
        } //end for if( !$has_cached_picklist_options )
        
    } //end of function
    
    function bsk_cf7_to_zoho_get_zoho_picklist_fields_fun(){
        if( !check_ajax_referer( 'bsk-cf7-to-zoho-populating-form-fields-setting-ajax-nonce', 'nonce', false ) ){
            $array = array( 'status' => false, 'msg' => 'Security check!' );
            wp_die( json_encode( $array ) );
        }
        
        $module = $_POST['module'];
        $cached_data = get_option( BSK_CF7_ZOHO_Modules::$_bsk_cf7_to_zoho_module_picklist_fields_options_cache_prefix.$module, false );
        
        $options = '<option value="" selected>Select ZOHO field...</option>';
        if( $cached_data && is_array($cached_data) && count($cached_data) > 0 ){
            foreach( $cached_data as $api_name => $picklist_obj ){
                $options .= '<option value="'.$api_name.'">'.$picklist_obj['label'].'</option>';
            }
        }
        
        $array = array( 'status' => true, 'options' => $options );
        wp_die( json_encode( $array ) );
    }
    
    function bsk_cf7zoho_adjust_zoho_fields_sequence( $sections_data, $module ){
        if( !isset( $sections_data['fields'] ) || !is_array( $sections_data['fields'] ) || count( $sections_data['fields'] ) < 1 ){
            return $sections_data;
        }

        $new_section_fields = array();
        switch ( $module ){
            case 'Leads':
                if( $sections_data['label'] == 'Lead Information' ){
                    $new_section_fields[] = 'Owner';
                    $new_section_fields[] = 'Company';
                    $new_section_fields[] = 'Salutation';
                    $new_section_fields[] = 'First_Name';
                    $new_section_fields[] = 'Last_Name';
                }

                $zoho_fields = $sections_data['fields'];
                foreach( $zoho_fields as $zoho_api_name ){
                    if( !in_array( $zoho_api_name, $new_section_fields ) ){
                        $new_section_fields[] = $zoho_api_name;
                    }
                }
            break;
            
            case 'Contacts':
                if( $sections_data['label'] == 'Contact Information' ){
                    $new_section_fields[] = 'Owner';
                    $new_section_fields[] = 'Salutation';
                    $new_section_fields[] = 'First_Name';
                    $new_section_fields[] = 'Last_Name';
                }

                $zoho_fields = $sections_data['fields'];
                foreach( $zoho_fields as $zoho_api_name ){
                    if( !in_array( $zoho_api_name, $new_section_fields ) ){
                        $new_section_fields[] = $zoho_api_name;
                    }
                }
            break;
                
            case 'Accounts':
                if( $sections_data['label'] == 'Account Information' ){
                    $new_section_fields[] = 'Owner';
                }

                $zoho_fields = $sections_data['fields'];
                foreach( $zoho_fields as $zoho_api_name ){
                    if( !in_array( $zoho_api_name, $new_section_fields ) ){
                        $new_section_fields[] = $zoho_api_name;
                    }
                }
            break;
            default:
                $new_section_fields = $sections_data['fields'];
            break;
        }
        
        $sections_data['fields'] = $new_section_fields;
        
        return $sections_data;
    }
    
    function bsk_cf7zoho_adjust_zoho_fields_label( $zoho_api_name, $zoho_field_label, $module ){
        $new_label = $zoho_field_label;
        
        switch ( $module ){
            case 'Leads':
                $lead_name_zoho_api_name = array( 'Salutation', 'First_Name', 'Last_Name', 'Full_Name' );

                if( in_array( $zoho_api_name, $lead_name_zoho_api_name ) ){
                    $new_label = 'Lead Name - '.$zoho_field_label;
                }
            break;
            
            case 'Contacts':
                $contact_name_zoho_api_name = array( 'Salutation', 'First_Name', 'Last_Name', 'Full_Name' );

                if( in_array( $zoho_api_name, $contact_name_zoho_api_name ) ){
                    $new_label = 'Contact Name - '.$zoho_field_label;
                }
            default:
                //
            break;
        }
        
        return $new_label;
    }
}
