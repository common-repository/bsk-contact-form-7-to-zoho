<?php

class BSK_CF7_ZOHO_Dashboard {
    
	public static $_bsk_cf7_to_zoho_page = 'bsk-cf7-to-zoho';
    
    public $_bsk_cf7_to_zoho_OBJ_modules = NULL;
    public $_bsk_cf7_to_zoho_OBJ_form_panel = NULL;
    public $_bsk_cf7_to_zoho_OBJ_updater = NULL;
	public $_bsk_cf7_to_zoho_OBJ_update_helper = NULL;
    
    private static $_pro_tips_for_lists = array( 
                                                'Multiple feeds',
                                                'Mapping multiple form fields to a ZOHO field',
                                                'Assigning Lead / Contact / Account Owner',
                                                'ZOHO custom fields',
                                                'Uploading Lead / Contact / Account image',
                                                'ZOHO attachments',
                                                'Triggers( Workflow, Blueprint )'
                                             );
	public function __construct() {
		global $wpdb;
		
        require_once( BSK_CF7_ZOHO_DIR.'classes/modules/modules.php' );

        
        require_once( 'dashboard-feeds.php' );
        require_once( 'dashboard-form-panel.php' );
        require_once( 'dashboard-help.php' );
    
        $this->_bsk_cf7_to_zoho_OBJ_modules = new BSK_CF7_ZOHO_Modules();
		$this->_bsk_cf7_to_zoho_OBJ_form_panel = new BSK_CF7_ZOHO_Dashboard_Form_Panel();
            
        /*
          * Actions & Filters
          */
		add_action( 'admin_menu', array( $this, 'bsk_cf7_to_zoho_dashboard_menu' ), 999 );
        
        add_action( 'bsk_cf7_to_zoho_action_get_zoho_approval', array($this, 'bsk_cf7_to_zoho_action_get_zoho_approval_fun') );
        add_action( 'bsk_cf7_to_zoho_action_grant', array($this, 'bsk_cf7_to_zoho_action_zoho_granted_fun') );
	}
	
	function bsk_cf7_to_zoho_dashboard_menu() {
		
		$authorized_level = 'level_10';
		
		//read plugin settings
		$plugin_settings = get_option( BSK_CF7_ZOHO::$_plugin_settings_option_name, '' );
		add_submenu_page( 
                          'wpcf7',
                          'Zoho CRM', 
                          'Zoho CRM',
                          $authorized_level, 
                          self::$_bsk_cf7_to_zoho_page,
                          array($this, 'bsk_cf7_to_zoho_dashboard') 
                        );
	}
	
	function bsk_cf7_to_zoho_dashboard(){
        if( isset($_GET['bsk-cf7-to-zoho-action']) && 
            $_GET['bsk-cf7-to-zoho-action'] == "view-feed-last-log" ){
            
            $this->show_cf7_feed_last_log();
            
            return;
        }
		?>
		<div class="wrap" id="bsk_cf7_to_zoho_setings_wrap_ID">
            <div id="icon-edit" class="icon32"><br/></div>
            <h2>Contact Form 7 to Zoho</h2>
            <?php
            $this->bsk_pdf_manager_show_pro_tip_box( self::$_pro_tips_for_lists );
            ?>
            <p>The Version 1.0 of Zoho CRM APIs is being deprecated.</p>
            <p>This plugin use <span class="bsk-documentation-attr bsk-bold">Zoho API 2.0</span> to integrate Contact Form 7 with your Zoho CRM so DONOT worry your integration suddenly stop.</p>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="javascript:void(0);" id="bsk_cf7_to_zoho_setings_tab-connection">Connect to Zoho</a>
                <a class="nav-tab" href="javascript:void(0);" id="bsk_pdfm_setings_tab-modules" data-tab="modules">Modules</a>
                <a class="nav-tab" href="javascript:void(0);" id="bsk_pdfm_setings_tab-help" data-tab="help">Help</a>
            </h2>
            <div id="bsk_cf7_to_zoho_setings_tab_content_wrap_ID">
				<section><?php $this->settings_connect_to_zoho(); ?></section>
                <section><?php $this->_bsk_cf7_to_zoho_OBJ_modules->bsk_cf7_to_zoho_show_modules_settings(); ?></section>
                <section><?php BSK_CF7_ZOHO_Dashboard_Help::show_help_content(); ?></section>
            </div>
            <?php
            $target_tab = isset($_REQUEST['target-tab']) ? $_REQUEST['target-tab'] : '';
            echo '<input type="hidden" id="bsk_cf7_to_zoho_settings_target_tab_ID" value="'.$target_tab.'" />';
            ?>
        </div>
    <?php
    }
    
    function settings_connect_to_zoho(){
        
        $current_base_page = admin_url( "admin.php?page=".self::$_bsk_cf7_to_zoho_page );
        
        $zoho_account_site = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        $client_id = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_id_key, '' );
        $client_secret = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_secret_key, '' );
        $access_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, '' );
        $refresh_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, '' );
        $expires = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, '' );
        
        $authenticate_form_display = 'block';
        if( $client_id && $client_secret && $access_token && $refresh_token && $expires ){
            $authenticate_form_display = 'none';
        }
        ?>
        <form id="bsk_cf7_to_zoho_dashboard_form_id" method="post" action="<?php echo $current_base_page; ?>">
            <p>The Zoho CRM API is authenticated using OAuth2.0 protocol. This allows you to share specific data with any application while keeping your usernames and passwords private. This protocol provides users with a secure and easy way to use authentication.</p>
            <div id="bsk_cf7_to_zoho_authenticate_form_ID" style="display: <?php echo $authenticate_form_display; ?>;">
            <?php $this->bsk_cf7_to_zoho_action_authenticate_form( $current_base_page ); ?>
            </div>
            <?php
            if( $client_id && $client_secret && $access_token && $refresh_token && $expires ){
            ?>
            <div id="bsk_cf7_to_zoho_authenticate_status_form_ID" style="border: #FFFFFF 1px solid; width: 80%; background-color: #FFFFFF;">
                <h3>Connection Status</h3>
                <div class="bsk-cf7-to-zoho-add-client-id-info" style="width: 877px; border: #dfdfdf 2px solid; padding: 20px 20px 20px 20px;">
                    <h3 style="color: #1abb25;">Successfully connected to Zoho!</h3>
                    <p>You'll see a new panel named  <span class="bsk-documentation-attr bsk-bold">Zoho CRM</span> when edit your form. Do form mapping there to insert Leads, Accounts to your Zoho when form submit.</p>
                    <p>&nbsp;</p>
                    <p>Your Zoho account from : <a href="<?php echo $zoho_account_site; ?>" style="color: #F0483E;"><?php echo $zoho_account_site; ?></a></p>
                    <p>Connected client ID: <span style="color: #F0483E;"><?php echo $client_id; ?></span></p>
                </div>
            </div>
            <p style="margin-top: 40px;">
                <a href="javascript:void(0);" class="button bsk-cf7zoho-add-client-id-button" id="bsk_cf7_to_zoho_new_id_to_authtnticate_ID">Use new Client ID to authenticate</a>
                <a href="javascript:void(0);" class="button bsk-cf7zoho-add-client-id-button" id="bsk_cf7_to_zoho_new_id_to_authtnticate_cancel_ID" style="margin-left: 20px; display: none;">Cancel</a>
            </p>
            <?php } ?>
            <p style="margin-top: 40px;">&nbsp;</p>
            <?php wp_nonce_field( 'bsk_cf7_to_zoho_dashboard_oper_nonce', 'bsk_cf7_to_zoho_dashboard_oper_nonce' ); ?>
            <input type="hidden" name="bsk_cf7_to_zoho_action" id="bsk_cf7_to_zoho_action_ID" value="get_zoho_approval" />
        </form>
    <?php
	}
    
    function bsk_cf7_to_zoho_action_authenticate_form( $current_base_page ){
        $zoho_account_site = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        ?>
        <h3 style="font-size: 1.5em;">Step 1, Add Client ID</h3>
        <div style="margin-top: 20px; display: table; line-height: 60px; vertical-align: middle;">
            <div>
                <div style="display: table-cell;"><span class="bsk-cf7zoho-heading-text">Choose</span> correct ZOHO account server<span style="display: inline-block; width: 20px;"></span>
                </div>
                <div style="display: table-cell;">
                    <?php
                    $us_checked = $zoho_account_site == 'www.zoho.com' ? ' checked' : '';
                    $eu_checked = $zoho_account_site == 'www.zoho.eu' ? ' checked' : '';
                    $in_checked = $zoho_account_site == 'www.zoho.in' ? ' checked' : '';
                    $cn_checked = $zoho_account_site == 'www.zoho.cn' ? ' checked' : '';
                    
                    $add_client_id_URI = 'https://accounts.zoho.com/developerconsole';
                    if( $zoho_account_site == 'www.zoho.eu' ){
                        $add_client_id_URI = 'https://accounts.zoho.eu/developerconsole';
                    }
                    ?>
                    <label>
                        <input type="radio" name="bsk_cf7_to_zoho_account_from" value="www.zoho.com" class="bsk-cf7-to-zoho-account-from-raido" <?php echo $us_checked; ?> /> www.zoho.com
                    </label>
                    <label style="margin-left: 15px;">
                        <input type="radio" name="bsk_cf7_to_zoho_account_from" value="www.zoho.eu" class="bsk-cf7-to-zoho-account-from-raido"  <?php echo $eu_checked; ?> /> www.zoho.eu
                    </label>
                    <label style="margin-left: 15px;">
                        <input type="radio" name="bsk_cf7_to_zoho_account_from" value="www.zoho.in" class="bsk-cf7-to-zoho-account-from-raido"  <?php echo $in_checked; ?> /> www.zoho.in
                    </label>
                    <label style="margin-left: 15px;">
                        <input type="radio" name="bsk_cf7_to_zoho_account_from" value="www.zoho.com.cn" class="bsk-cf7-to-zoho-account-from-raido"  <?php echo $cn_checked; ?> /> www.zoho.com.cn
                    </label>
                </div>
            </div>
            <div>
                <div style="display: table-cell;"><span class="bsk-cf7zoho-heading-text">Open</span><span style="display: inline-block; width: 20px;"></span></div>
                <div style="display: table-cell;">
                    <a href="<?php echo $add_client_id_URI; ?>" target="_blank" class="bsk-cf7zoho-add-client-zoho-screenshot bsk-cf7zoho-add-client-id-button">
                        <img src="<?php echo BSK_CF7_ZOHO_URL; ?>images/zdc_logo_dark.png" style="vertical-align: middle;"/>
                    </a>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <div style="display: table-cell;"><span class="bsk-cf7zoho-heading-text">Select</span><span style="display: inline-block; width: 11px;"></span></div>
                <div style="display: table-cell;">
                    <a href="<?php echo $add_client_id_URI; ?>" target="_blank" class="bsk-cf7zoho-add-client-zoho-screenshot bsk-cf7zoho-add-client-id-button">
                        <img src="<?php echo BSK_CF7_ZOHO_URL; ?>images/bsk-cf7-to-zoho-zoho-client-type.png" style="vertical-align: middle;"/>
                    </a>
                </div>
            </div>
            <div style="margin-top: 20px;">
                <div style="display: table-cell;"><span class="bsk-cf7zoho-heading-text">to</span><span style="display: inline-block; width: 48px;"></span></div>
                <div style="display: table-cell;">
                    <a href="<?php echo $add_client_id_URI; ?>" target="_blank" class="button bsk-cf7zoho-add-client-id-button" style="vertical-align: middle;">Add Client ID</a>
                    <span style="display: inline-block; width: 10px;"></span>with the following info.
                </div>
            </div>
            <div style="clear: both;"></div>
            <div style="border: #FFFFFF 1px solid; background-color: #FFFFFF; padding: 25px 25px 25px 65px; line-height: normal;">
                <div class="bsk-cf7-to-zoho-add-client-id-info" style="border: #dfdfdf 2px solid; padding: 20px 20px 20px 20px;">
                    <div class="bsk-cf7-to-zoho-client-id-info-label">Client Type</div>
                    <div class="add_client_form_field_value">
                        <input type="text" name="client_type" class="bsk-cf7zoho-client-id-info-input" value="Server-based Applications" readonly>
                    </div>
                    <div class="bsk-cf7-to-zoho-client-id-info-label" style="margin-top: 20px;">Client Name</div>
                    <div class="add_client_form_field_value">
                        <input type="text" name="client_name" class="bsk-cf7zoho-client-id-info-input" value="Bannersky Contact Form 7" readonly>
                        <div class="bsk-cf7zoho-copy">
                            <span class="bsk-cf7zoho-copy-tip" style="visibility: hidden; opacity: 0;">click to copy</span>
                            <span class="bsk-cf7zoho-copy-anchor"></span>
                        </div>
                    </div>
                    <div class="bsk-cf7-to-zoho-client-id-info-label" style="margin-top: 20px;">Homepage URL</div>
                    <div class="add_client_form_field_value">
                        <input type="text" name="client_name" class="bsk-cf7zoho-client-id-info-input" value="https://www.bannersky.com/" readonly>
                        <div class="bsk-cf7zoho-copy">
                            <span class="bsk-cf7zoho-copy-tip" style="visibility: hidden; opacity: 0;">click to copy</span>
                            <span class="bsk-cf7zoho-copy-anchor"></span>
                        </div>
                    </div>
                    <?php
                    $authorized_url = add_query_arg( 'bsk-cf7-to-zoho-action', 'grant', $current_base_page );
                    ?>
                    <div class="bsk-cf7-to-zoho-client-id-info-label" style="margin-top: 20px;">Authorized Redirect URIs</div>
                    <div class="add_client_form_field_value">
                        <input type="text" name="client_name" class="bsk-cf7zoho-client-id-info-input" value="<?php echo $authorized_url; ?>" readonly>
                        <div class="bsk-cf7zoho-copy">
                            <span class="bsk-cf7zoho-copy-tip" style="visibility: hidden; opacity: 0;">click to copy</span>
                            <span class="bsk-cf7zoho-copy-anchor"></span>
                        </div>
                    </div>
                </div>
            </div>
            <h3 style="margin-top: 40px; font-size: 1.5em;">Step 2, save Client ID, Client Secret</h3>
            <p>Zoho will show you the following screen if Client ID created successfully</p>
            <div style="border: #FFFFFF 1px solid; background-color: #FFFFFF; padding: 25px 25px 25px 65px; line-height: normal;">
                <div class="bsk-cf7-to-zoho-add-client-id-info" style="border: #dfdfdf 2px solid; padding: 20px 20px 20px 20px;">
                    <img src="<?php echo BSK_CF7_ZOHO_URL; ?>images/bsk-cf7-zoho-client-id-infp.png" />
                </div>
            </div>
            <p>Copy Client ID and Client Secret to paste to the following input fields</p>
            <div style="border: #FFFFFF 1px solid; background-color: #FFFFFF; padding: 25px 25px 25px 65px; line-height: normal;">
                <div class="bsk-cf7-to-zoho-add-client-id-info" style="border: #dfdfdf 2px solid; padding: 20px 20px 20px 20px;">
                    <div class="bsk-cf7-to-zoho-client-id-info-label">Client ID</div>
                    <div class="add_client_form_field_value">
                        <input type="text" name="bsk_cf7_to_zoho_client_id" class="bsk-cf7zoho-client-id-info-input" value="" placeholder="paste Client ID here" id="bsk_cf7_to_zoho_client_id_ID">
                        <div class="bsk-cf7-to-zoho-alert-msg"></div>
                    </div>
                    <div class="bsk-cf7-to-zoho-client-id-info-label" style="margin-top: 30px;">Client Secret</div>
                    <div class="add_client_form_field_value">
                        <input type="text" name="bsk_cf7_to_zoho_client_secret" class="bsk-cf7zoho-client-id-info-input" value="" placeholder="paste Client Secret here" id="bsk_cf7_to_zoho_client_secret_ID">
                        <div class="bsk-cf7-to-zoho-alert-msg"></div>
                    </div>
                </div>
            </div>
            <h3 style="margin-top: 40px; font-size: 1.5em;">Step 3, get permission approval</h3>
            <div style="border: #FFFFFF 1px solid; background-color: #FFFFFF; padding: 25px 25px 25px 65px; line-height: normal;">
                <div class="bsk-cf7-to-zoho-add-client-id-info" style="border: #dfdfdf 2px solid; padding: 20px 20px 20px 20px;">
                    <p>
                        <input type="button" value="Get permission approval from Zoho" class="button-primary" id="bsk_cf7_to_zoho_client_get_zoho_approval_ID">
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
	
    function bsk_cf7_to_zoho_action_get_zoho_approval_fun(){
        $nonce = $_POST['bsk_cf7_to_zoho_dashboard_oper_nonce'];
		if( !wp_verify_nonce( $nonce, 'bsk_cf7_to_zoho_dashboard_oper_nonce' ) ){
			wp_die( 'Security checking failed.' );
		}
        
        $zoho_account_from = $_POST['bsk_cf7_to_zoho_account_from'];
        $client_id = trim( $_POST['bsk_cf7_to_zoho_client_id'] );
        $client_secret = trim( $_POST['bsk_cf7_to_zoho_client_secret'] );
        
        update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, $zoho_account_from );
        update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_id_key, $client_id );
        update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_secret_key, $client_secret );
        
        $current_base_page = admin_url( "admin.php?page=bsk-cf7-to-zoho" );
        $authorized_url = add_query_arg( 'bsk-cf7-to-zoho-action', 'grant', $current_base_page );
        $authorized_url = urlencode( $authorized_url );
        
        $zoho_site = 'https://accounts.zoho.com';
        if( $zoho_account_from == 'www.zoho.eu' ){
            $zoho_site = 'https://accounts.zoho.eu';
        }else if( $zoho_account_from == 'www.zoho.in' ){
            $zoho_site = 'https://accounts.zoho.in';
        }else if( $zoho_account_from == 'www.zoho.com.cn' ){
            $zoho_site = 'https://accounts.zoho.com.cn';
        }
        $zoho_autho_url = $zoho_site.'/oauth/v2/auth?scope=ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.users.READ&client_id='.$client_id.'&response_type=code&access_type=offline&redirect_uri='.$authorized_url;
        
        wp_redirect( $zoho_autho_url );
        
        exit();
    }
    
    function bsk_cf7_to_zoho_action_zoho_granted_fun(){
        
        $zoho_account_from = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_account_from_key, 'www.zoho.com' );
        $client_id = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_id_key, '' );
        $client_secret = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_client_secret_key, '' );
        if( $client_id == "" || $client_secret == "" ){
            wp_die( 'No client info saved.' );
        }
        $zoho_grant_code = $_GET['code'];
        if( $zoho_grant_code == "" ){
            wp_die( 'Error data from Zoho.' );
        }
        
        $current_base_page = admin_url( "admin.php?page=bsk-cf7-to-zoho" );
        $authorized_url = add_query_arg( 'bsk-cf7-to-zoho-action', 'grant', $current_base_page );
        $authorized_url = urlencode( $authorized_url );
        
        $zoho_site_url = 'https://accounts.zoho.com';
        if( $zoho_account_from == 'www.zoho.eu' ){
            $zoho_site_url = 'https://accounts.zoho.eu';
        }else if( $zoho_account_from == 'www.zoho.in' ){
            $zoho_site_url = 'https://accounts.zoho.in';
        }else if( $zoho_account_from == 'www.zoho.com.cn' ){
            $zoho_site_url = 'https://accounts.zoho.com.cn';
        }
        $get_token_url = $zoho_site_url.'/oauth/v2/token?code='.$zoho_grant_code.'&redirect_uri='.$authorized_url.'&client_id='.$client_id.'&client_secret='.$client_secret.'&grant_type=authorization_code';
        $zoho_respond = wp_remote_post( $get_token_url, array('sslverify' => false, 'timeout' => 60) );
        $zoho_respond_body  = wp_remote_retrieve_body( $zoho_respond );
        $zoho_respond_array = json_decode( $zoho_respond_body, true );
        
        if( !$zoho_respond_array || !is_array( $zoho_respond_array ) || count( $zoho_respond_array ) < 1 ){
            wp_die( 'Error data from Zoho.' );
        }
        
        if( isset( $zoho_respond_array['access_token'] ) && $zoho_respond_array['access_token'] &&
            isset( $zoho_respond_array['refresh_token'] ) && $zoho_respond_array['refresh_token'] &&
            isset( $zoho_respond_array['expires_in'] ) && $zoho_respond_array['expires_in'] ){
            
            $access_token =  $zoho_respond_array['access_token'];
            $refresh_token =  $zoho_respond_array['refresh_token'];
            $expires_in_sec = $zoho_respond_array['expires_in'] - 3 * 60;
            
            update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, $access_token );
            update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, $refresh_token );
            update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, current_time('timestamp') + $expires_in_sec );
            
            add_action( 'admin_notices', array( $this, 'bsk_cf7_to_zoho_action_get_zoho_approval_success_fun' ) );
        }else if( isset( $zoho_respond_array['access_token'] ) && isset( $zoho_respond_array['token_type'] ) && 
                    $zoho_respond_array['token_type'] == 'Bearer' ){
            $refresh_token = get_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key, false );
            if( $refresh_token ){
                update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key, $zoho_respond_array['access_token'] );
                $expires_in_sec = $zoho_respond_array['expires_in'] - 3 * 60;
                update_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key, current_time('timestamp') + $expires_in_sec );
            }else{
                add_action( 'admin_notices', array( $this, 'bsk_cf7_to_zoho_no_refresh_token_fun' ) );
            }
        }else{
            delete_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_token_key );
            delete_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_refresh_token_key );
            delete_option( BSK_CF7_ZOHO_Common_Options::$_bsk_cf7_to_zoho_access_expires_key );
            
            add_action( 'admin_notices', array( $this, 'bsk_cf7_to_zoho_action_get_zoho_approval_failed_fun' ) );
        }
    }
    
    function bsk_cf7_to_zoho_action_get_zoho_approval_success_fun(){
        ?>
        <div class="notice notice-success">
			<p>Get connection to Zoho successfully. <p>
            <p>Please click modules tab and download modules data from your Zoho first.</p>
            <p>And then you'll see a new panel named  <span class="bsk-documentation-attr bsk-bold">Zoho CRM</span> when you edit your Contact Form 7 form, please do form mapping there.</p>
		</div>
        <?php
    }
    
    function bsk_cf7_to_zoho_action_get_zoho_approval_failed_fun(){
        ?>
        <div class="notice notice-error">
			<p>Get connection to Zoho failed. Please check if you enter corrent Client ID, Client Secret? </p>
            <p>Or check <span class="bsk-documentation-attr bsk-bold">Authorized redirect URIs</span> right for your Client ID?</p>
		</div>
        <?php
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
    
    function show_cf7_feed_last_log(){
        $feed_id = isset( $_GET['feed_id'] ) ? absint( $_GET['feed_id'] ) : 0;
        $nonce_val = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
        
        if( ! wp_verify_nonce( $nonce_val, 'view-feed-last-log' ) ){
            wp_die( 'ERROR - Invaid nonce' );
        }
        if( $feed_id < 1 ){
            wp_die( 'Error - Invalid feed id');
        }
        
        global $wpdb;
        
        $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` WHERE `id` = %d';
        $sql = $wpdb->prepare( $sql, $feed_id );
        
        $feed_obj_results = $wpdb->get_results( $sql );
        if( !$feed_obj_results || !is_array( $feed_obj_results ) || count( $feed_obj_results ) < 1 ){
        ?>
        <div class="wrap">
            <p>No log found</p>
        </div>
        <?php
        }else{
            $form_feed = $feed_obj_results[0];
            
            $logs_array = unserialize( $form_feed->last_log );
            foreach( $logs_array as $key => $val ){
                $logs_array[$key] = '<p>'.$val.'</p>';   
            }
            $logs_string = implode( "\n", $logs_array );

            $log_data = '';
            $log_data .= $logs_string;
            $log_data .= "\n";
        ?>
        <div class="wrap">
            <?php echo $log_data; ?>
        </div>
        <?php
        }
    }
    
    function bsk_cf7_to_zoho_no_refresh_token_fun(){
        ?>
        <div class="notice notice-error is-dismissible">
            <p>Please create a new <b>Client ID</b> in ZOHO to get connection. </p>
        </div>
        <?php
    }
}
