jQuery(document).ready( function($) {
	/*
     * Settings - connection
     */
    $(".bsk-cf7zoho-copy-anchor").hover(function(){
            $(this).parent().find( ".bsk-cf7zoho-copy-tip" ).css( "visibility", "visible" );
            $(this).parent().find( ".bsk-cf7zoho-copy-tip" ).css( "opacity", "1" );
        }, 
        function() {
            // mouse left
            $(this).parent().find( ".bsk-cf7zoho-copy-tip" ).css( "visibility", "hidden" );
            $(this).parent().find( ".bsk-cf7zoho-copy-tip" ).css( "opacity", "0" );
            $(this).parent().find( ".bsk-cf7zoho-copy-tip" ).html( "click to copy" );
        }
    );
    
    $(".bsk-cf7zoho-copy-anchor").click( function(){
        var text_to_copy = $(this).parents( ".add_client_form_field_value" ).find(".bsk-cf7zoho-client-id-info-input").val();

        var temp_input = $( "<input>" );
        $( "body" ).append( temp_input );
        temp_input.val( text_to_copy ).select();
        document.execCommand("copy");
        temp_input.remove();
        
        $(this).parent().find( ".bsk-cf7zoho-copy-tip" ).html( 'Copied' );
    });
    
	$(".bsk-cf7-to-zoho-account-from-raido").click( function(){
        var radio_value = $("input[name='bsk_cf7_to_zoho_account_from']:checked"). val();
        var console_url = 'https://accounts.zoho.com/developerconsole';    
        if( radio_value == 'www.zoho.eu' ){
            console_url = 'https://accounts.zoho.eu/developerconsole';
        }else if( radio_value == 'www.zoho.in' ){
            console_url = 'https://accounts.zoho.in/developerconsole';
        }else if( radio_value == 'www.zoho.com.cn' ){
            console_url = 'https://accounts.zoho.com.cn/developerconsole';
        }

        $(".bsk-cf7zoho-add-client-id-button").attr( 'href', console_url );
    });
    /* 
      *
      * Settings tab switch 
      *
      */
	$("#bsk_cf7_to_zoho_setings_wrap_ID .nav-tab-wrapper a").click(function(){
		//alert( $(this).index() );
		$('#bsk_cf7_to_zoho_setings_wrap_ID section').hide();
		$('#bsk_cf7_to_zoho_setings_wrap_ID section').eq($(this).index()).show();
		
		$(".nav-tab").removeClass( "nav-tab-active" );
		$(this).addClass( "nav-tab-active" );
        
        if( $("#bsk_cf7_to_zoho_settings_target_tab_ID").length > 0 ){
            $("#bsk_cf7_to_zoho_settings_target_tab_ID").val( $(this).data( "tab" ) );
        }
		
		return false;
	});
    
	/* settings target tab */
	if( $("#bsk_cf7_to_zoho_settings_target_tab_ID").length > 0 ){
		var target = $("#bsk_cf7_to_zoho_settings_target_tab_ID").val();
		if( target ){
			$("#bsk_pdfm_setings_tab-" + target).click();
		}
	}
    
    /* settings authentication */
    $("#bsk_cf7_to_zoho_client_get_zoho_approval_ID").click(function(){
        var client_id = $("#bsk_cf7_to_zoho_client_id_ID").val();
		var client_secret = $("#bsk_cf7_to_zoho_client_secret_ID").val();
		
        if( $.trim( client_id ) == ""  ){
            $("#bsk_cf7_to_zoho_client_id_ID").focus();
            return;
        }
		if( $.trim( client_secret ) == ""  ){
            $("#bsk_cf7_to_zoho_client_secret_ID").focus();
            return;
        }
		
		$("#bsk_cf7_to_zoho_dashboard_form_id").submit();
	});
    
    $("#bsk_cf7_to_zoho_create_test_lead_ID").click(function(){
        var ajax_nonce = $("#bsk_cf7_to_zoho_create_test_lead_ajax_nonce_ID").val();
        var action_val = 'bsk_cf7_to_zoho_create_test_lead';
        var data = { action: action_val, nonce: ajax_nonce };
        
        $("#bsk_cf7_to_zoho_create_test_lead_ajax_loader_ID").css( "display", "inline-block" );
        $.post( ajaxurl, data, function(response) {
            $("#bsk_cf7_to_zoho_create_test_lead_ajax_loader_ID").css( "display", "none" );
            alert( response );
        });
    });
    
    $("#bsk_cf7_to_zoho_new_id_to_authtnticate_ID").click(function(){
        $("#bsk_cf7_to_zoho_authenticate_form_ID").css( "display", "block" );
        $("#bsk_cf7_to_zoho_authenticate_status_form_ID").css( "display", "none" );
        $("#bsk_cf7_to_zoho_new_id_to_authtnticate_ID").css( "display", "none" );
        $("#bsk_cf7_to_zoho_new_id_to_authtnticate_cancel_ID").css( "display", "inline-block" );
    });
    
    $("#bsk_cf7_to_zoho_new_id_to_authtnticate_cancel_ID").click(function(){
        $("#bsk_cf7_to_zoho_authenticate_form_ID").css( "display", "none" );
        $("#bsk_cf7_to_zoho_authenticate_status_form_ID").css( "display", "block" );
        $("#bsk_cf7_to_zoho_new_id_to_authtnticate_ID").css( "display", "inline-block" );
        $("#bsk_cf7_to_zoho_new_id_to_authtnticate_cancel_ID").css( "display", "none" );
    });
    
    /* 
      *
      * Settings modules 
      *
      */
    $(".bsk-cf7-to-zoho-modules-enable-chk").click(function(){
        var module_val = $(this).val();
        var operation_val = 'enable';
        var module_settings_container = $(this).parents( ".bsk-cf7-to-zoho-module-setting-container" );
        
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").removeClass( "bsk-cf7-to-zoho-alert-msg" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").removeClass( "bsk-cf7-to-zoho-prompt-msg" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").html( "" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").css("display", "none");
        if( $(this).is(':checked') ){
            operation_val = 'enable';
            module_settings_container.find( ".bsk-cf7-to-zoho-modules-refresh-cache-btn-container" ).css( "display", "block" );
        }else{
            operation_val = 'disable';
            module_settings_container.find( ".bsk-cf7-to-zoho-modules-refresh-cache-btn-container" ).css( "display", "none" );
        }
        var nonce_val = $("#bsk_cf7_to_zoho_refresh_module_cache_ajax_nonce_ID").val();
        var data = { 
                            action: 'bsk_cf7_to_zoho_enable_disable_module',
                            nonce: nonce_val, 
                            module: module_val,
                            operation: operation_val
                       };
        module_settings_container.find(".bsk-cf7-to-zoho-modules-enable-module-ajax-loader").css( "display", "inline-block" );
        $.post( ajaxurl, data, function( response ) {
            var return_obj = $.parseJSON( response );
            module_settings_container.find(".bsk-cf7-to-zoho-modules-enable-module-ajax-loader").css( "display", "none" );
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").html( return_obj.msg );
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").css( "display", "block" );
            if( return_obj.status ){
                module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").addClass( "bsk-cf7-to-zoho-prompt-msg" );
            }else{
                module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").addClass( "bsk-cf7-to-zoho-alert-msg" );
            }
        });
    });
    
    $(".bsk-cf7-to-zoho-modules-refresh-cache-button").click(function(){
        var module_val = $(this).data("module");
        var module_settings_container = $(this).parents( ".bsk-cf7-to-zoho-module-setting-container" );
        
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").removeClass( "bsk-cf7-to-zoho-alert-msg" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").removeClass( "bsk-cf7-to-zoho-prompt-msg" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").html( "" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").css( "display", "none" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-create-test-button").css( "display", "none" );
        if( $.trim( module_val ) == "" ){
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").css( "display", "block" );
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").addClass( "bsk-cf7-to-zoho-alert-msg" );
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").html( "Invalid module" );
            
            return;
        }
        
        var nonce_val = $("#bsk_cf7_to_zoho_refresh_module_cache_ajax_nonce_ID").val();
        var data = { 
                            action: 'bsk_cf7_to_zoho_refresh_module_cache_data',
                            nonce: nonce_val, 
                            module: module_val
                       };
        module_settings_container.find(".bsk-cf7-to-zoho-modules-refresh-cache-ajax-loader").css( "display", "inline-block" );
        $.post( ajaxurl, data, function( response ) {
			module_settings_container.find(".bsk-cf7-to-zoho-modules-refresh-cache-ajax-loader").css("display", "none");
			var return_obj = $.parseJSON( response );
            
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").html( return_obj.msg );
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").css( "display", "block" );
            if( return_obj.status ){
                module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").addClass( "bsk-cf7-to-zoho-prompt-msg" );
                module_settings_container.find(".bsk-cf7-to-zoho-modules-create-test-button").css( "display", "inline-block" );
                module_settings_container.find(".bsk-cf7-to-zoho-modules-refresh-cache-button").html( "Refresh module data from your Zoho" );
            }else{
                module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").addClass( "bsk-cf7-to-zoho-alert-msg" );
            }
		});
    });
    
    $(".bsk-cf7-to-zoho-modules-create-test-button").click(function(){
        var module_val = $(this).data("module");
        var module_settings_container = $(this).parents( ".bsk-cf7-to-zoho-module-setting-container" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").removeClass( "bsk-cf7-to-zoho-alert-msg" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").removeClass( "bsk-cf7-to-zoho-prompt-msg" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").html( "" );
        module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").css( "display", "none" );
        
        var nonce_val = $("#bsk_cf7_to_zoho_refresh_module_cache_ajax_nonce_ID").val();
        var data = { 
                            action: 'bsk_cf7_to_zoho_create_test_module_data',
                            nonce: nonce_val, 
                            module: module_val
                       };
        module_settings_container.find(".bsk-cf7-to-zoho-modules-create-test-ajax-loader").css( "display", "inline-block" );
        $.post( ajaxurl, data, function( response ) {
            module_settings_container.find(".bsk-cf7-to-zoho-modules-create-test-ajax-loader").css("display", "none");
			var return_obj = $.parseJSON( response );
            
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").html( return_obj.msg );
            module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").css( "display", "block" );
            if( return_obj.status ){
                module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").addClass( "bsk-cf7-to-zoho-prompt-msg" );
            }else{
                module_settings_container.find(".bsk-cf7-to-zoho-modules-module-operation-msg").addClass( "bsk-cf7-to-zoho-alert-msg" );
            }
        });
    });
    
    
    
    $(".bsk-cf7-to-zoho-form-feed-settings-container").on("change", ".bsk-cf7-to-zoho-form-feed-module", function(){
        var module_val = $(this).val();
        var form_id_val = $("#bsk_cf7_to_zoho_form_feed_form_id_ID").val();
        var feed_id_val = $("#bsk_cf7_to_zoho_form_feed_id_ID").val();
        if( module_val == '' || form_id_val == '' ){
            return;
        }
        var tbody_container = $(this).parents( ".bsk-cf7-to-zoho-form-feed-settings-container" ).find(".bsk-cf7zoho-form-feed-mapping-container");
        tbody_container.html( "" );
        
        
        var nonce_val = $("#bsk_cf7_to_zoho_form_feed_ajax_nonce_ID").val();
        var ajax_loader = $(this).parent().find(".bsk-cf7-to-zoho-form-feed-module-change-ajax-loader");
        var data = { 
                            action: 'bsk_cf7_to_zoho_feed_module_change',
                            nonce: nonce_val, 
                            module: module_val,
                            form_id: form_id_val,
                            feed_id: feed_id_val
                       };
        ajax_loader.css( "display", "inline-block" );
        $.post( ajaxurl, data, function( response ) {
			ajax_loader.css("display", "none");

            var return_obj = $.parseJSON( response );

            tbody_container.html( return_obj.html );
        });
        
    });
    
    $(".bsk-cf7-to-zoho-form-feed-settings-container").on("click", ".bsk-cf7-to-zoho-mapping-form-fields-plus", function(){
        var parent_td = $(this).parents( '.bsk-cf7zoho-form-fields' );
        
        parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( 'Mapping multiple form fields only supported in Pro version' );
    });
    
    $(".bsk-cf7-to-zoho-form-feed-settings-container").on("change", ".bsk-cf7zoho-lead-owner", function(){
        var parent_td = $(this).parents( '.bsk-cf7zoho-form-fields' );
        
        parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( 'Assigning Lead / Contact / Account owner only supported in Pro version' );
    });
    
    $(".bsk-cf7-to-zoho-form-feed-settings-container").on("change", ".bsk-cf7-to-zoho-mapping-form-fields-select, .bsk-cf7zoho-mapping-form-fields-zoho-picklist-options-select", function(){
        var parent_td = $(this).parents( '.bsk-cf7zoho-form-fields' );
        
        if( $(this).hasClass( 'bsk-cf7zoho-lead-image' ) || 
            $(this).hasClass( 'bsk-cf7zoho-contact-image' ) || 
            $(this).hasClass( 'bsk-cf7zoho-account-image' ) ){
            
            parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( '' );
            
            if( $(this).val() != '' ){
                parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( 'Upload image to record only supported in Pro version' );
            }
            
            return;
        }
        
        if( $(this).hasClass( 'bsk-cf7zoho-lead-owner' ) || 
            $(this).hasClass( 'bsk-cf7zoho-contact-owner' ) || 
            $(this).hasClass( 'bsk-cf7zoho-account-owner' ) ){
            
            parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( '' );
            
            if( $(this).val() != '' ){
                parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( 'Assigning Lead / Contact / Account Owner only supported in Pro version' );
            }
            
            return;
        }
        
        if( $(this).hasClass( 'bsk-cf7zoho-attahment' ) ){
            parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( '' );
            
            if( $(this).val() != '' ){
                parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( 'Upload attachment to ZOHO only supported in Pro version' );
            }
            
            return;
        }
        
        parent_td.find( '.bsk-cf7-to-zoho-error-message' ).html( '' );
    });
    
    $(".bsk-cf7-to-zoho-feed-active-inactive").click(function(){
        var img_container = $(this).parent().find("img");
        var feed_id_val = $(this).data( "feed-id" );
        var nonce_val = $("#bsk_cf7_to_zoho_form_feeds_list_ajax_nonce_ID").val();
        var data = { 
                            action: 'bsk_cf7_to_zoho_feed_active_inactive',
                            nonce: nonce_val, 
                            feed_id: feed_id_val
                       };
        $.post( ajaxurl, data, function( response ) {
            var return_obj = $.parseJSON( response );
            if( return_obj.status ){
                img_container.attr( "src", return_obj.src );
                img_container.attr( "alt", return_obj.alt );
                img_container.attr( "title", return_obj.alt );
            }else{
                alert( return_obj.msg );
            }
        });
    });
    
    $(".bsk-cf7-to-zoho-feed-debug-chk").click(function(){
        var is_checked = $(this).is( ":checked" ) ? 1 : 0;
        var td_container = $(this).parents( "td" );
        var feed_id_val = $(this).data( "feed-id" );
        var nonce_val = $("#bsk_cf7_to_zoho_form_feeds_list_ajax_nonce_ID").val();
        var data = { 
                            action: 'bsk_cf7_to_zoho_feed_debug_mode',
                            nonce: nonce_val, 
                            feed_id: feed_id_val,
                            checked: is_checked
                       };
        td_container.find( ".bsk-cf7-to-zoho-feed-last-log-download" ).remove();
        $.post( ajaxurl, data, function( response ) {
            var return_obj = $.parseJSON( response );
            if( return_obj.status ){
                if( return_obj.download_link ){
                    $(return_obj.download_link).insertAfter( td_container.find( 'label' ) );
                }
            }else{
                alert( return_obj.msg );
            }
        });
    });
    
    $(".bsk-cf7-to-zoho-feed-delete").click(function(){
        var tr_container = $(this).parents( "tr" );
        var tbody_container = $(this).parents( "tbody" );
        var feed_id_val = $(this).data( "feed-id" );
        var nonce_val = $("#bsk_cf7_to_zoho_form_feeds_list_ajax_nonce_ID").val();
        var data = { 
                            action: 'bsk_cf7_to_zoho_feed_delete',
                            nonce: nonce_val, 
                            feed_id: feed_id_val,
                       };
        $.post( ajaxurl, data, function( response ) {
            var return_obj = $.parseJSON( response );
            if( return_obj.status ){
                if( tbody_container.find("tr").length > 0 ){
                    tr_container.remove();
                }else{
                    tr_container.html( '<td class="colspanchange" colspan="7">No items found.</td>' );
                }
            }else{
                alert( return_obj.msg );
            }
        });
    });
    
    $( ".bsk-cf7zoho-form-feed-mapping-container" ).on( "change", ".bsk-cf7zoho-mapping-form-fields-zoho-picklist-options-select", function(){
        
        if( $(this).val() == '' ){
            return;
        }
        var parent_td = $(this).parents( '.bsk-cf7zoho-form-fields' );
        
        parent_td.find( ".bsk-cf7-to-zoho-mapping-form-fields-select" ).val( '' );
        
    });
    
    $( ".bsk-cf7zoho-form-feed-mapping-container" ).on( "change", ".bsk-cf7-to-zoho-mapping-form-fields-select", function(){
        
        if( $(this).val() == '' ){
            return;
        }
        var parent_td = $(this).parents( '.bsk-cf7zoho-form-fields' );
        
        parent_td.find( ".bsk-cf7zoho-mapping-form-fields-zoho-picklist-options-select" ).val( '' );
        
    });
    
    /*
      Populationg form fields
     */
    $(".bsk-cf7-to-zoho-populate-form-field-module-select").change(function(){
        var module_val = $(this).val();
        var zoho_piclist_select_obj = $(this).parent().find(".bsk-cf7-to-zoho-populate-form-field-zoho-field-select");
        
        zoho_piclist_select_obj.html( '<option value="">Select Zoho module...</option>' );
        zoho_piclist_select_obj.prop( "disabled", true );
        zoho_piclist_select_obj.css( "display", "inline-block" );
        if( module_val == '' ){
            return;
        }
        
        if( module_val == 'Users' ){
            zoho_piclist_select_obj.css( "display", "none" );
            return;
        }
        
        var nonce_val = $(this).parents(".bsk-cf7-to-zoho-form-fields-populating-container").find(".bsk-cf7tozoho-populating-form-ajax-nonce").val();
        var ajax_loader_span = $(this).parent().find(".bsk-cf7-to-zoho-ajax-loader").css( "display", "inline-block" );
        var data = { 
                        action: 'bsk_cf7_to_zoho_get_zoho_picklist_fields',
                        nonce: nonce_val, 
                        module: module_val,
                   };
        $.post( ajaxurl, data, function( response ) {
            ajax_loader_span.css( "display", "none" );
            var return_obj = $.parseJSON( response );
            if( return_obj.status ){
                zoho_piclist_select_obj.html( return_obj.options );
                zoho_piclist_select_obj.removeAttr( "disabled" );
            }else{
                alert( return_obj.msg );
            }
        });
        
    });
});
