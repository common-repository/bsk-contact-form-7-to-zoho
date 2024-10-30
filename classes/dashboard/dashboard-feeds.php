<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BSK_CF7_ZOHO_Dashboard_Feeds extends WP_List_Table {
   
    var $_form_id = 0;
    
    function __construct( $form_id ) {
        
        $this->_form_id = $form_id;
            
        //Set parent defaults
        parent::__construct( array( 
            'singular' => 'bsk-cf7-to-zoho-feeds',  //singular name of the listed records
            'plural'   => 'bsk-cf7-to-zoho-feeds', //plural name of the listed records
            'ajax'     => false                          //does this table support ajax?
        ) );
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
			case 'id':
				echo $item['id_link'];
				break;
            case 'status':
				echo $item['status_image'];
				break;
            case 'debug':
				echo $item['debug_image'];
				break;
			case 'name':
				echo $item['name_link'];
				break;
            case 'module':
				echo $item['module'];
				break;
            case 'datetime':
				echo $item['date_time'];
				break;
            case 'operation':
                echo $item['operation'];
				break; 
        }
    }
    
    function display_tablenav( $which ) {
        return '';
    }
    
    function column_cb( $item ) {
        return sprintf( 
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            esc_attr( $this->_args['singular'] ),
            esc_attr( $item['id'] )
        );
    }

    function get_columns() {
    
        $columns = array( 
			'cb'        	   => '<input type="checkbox"/>',
            'status'          => 'Status',
            'id'				=> 'ID',
            'name'     	    => 'Name',
            'module'        => 'Module',
            'datetime' 		=> 'Date',
            'debug'         => 'Debug',
            'operation'    => '',
        );
        
        return $columns;
    }
   
	function get_sortable_columns() {
		$c = array(
					//'name' => 'name',
					//'datetime'    => 'date'
					);
		
		return $c;
	}
	
    function get_views() {
        return array();
    }
   
    function get_bulk_actions() {
    
        $actions = array( 
        );
        
        return $actions;
    }

    function do_bulk_action() {
		global $wpdb;
		
		$feeds_id = isset( $_POST['bsk-cf7-to-zoho-feeds'] ) ? $_POST['bsk-cf7-to-zoho-feeds'] : false;
		if ( !$feeds_id || !is_array( $feeds_id ) || count( $feeds_id ) < 1 ){
			return;
		}
		$action = -1;
		if ( isset($_POST['action']) && $_POST['action'] != -1 ){
			$action = $_POST['action'];
		}
		if ( isset($_POST['action2']) && $_POST['action2'] != -1 ){
			$action = $_POST['action2'];
		}
		if ( $action == -1 ){
			return;
		}else if ( $action == 'delete' ){
			if( count($feeds_id) < 1 ){
				return;
			}
			
            foreach( $feeds_id as $mapping_id ){
                //delete all categories
                $sql = 'DELETE FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` '.
                       'WHERE `id` = %d';
                $sql = $wpdb->prepare_results( $sql, $mapping_id );
                $wpdb->query( $sql );
            }
		}
    }
    
    function get_data() {
		global $wpdb;
		
        $sql = 'SELECT * FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` '.
                  'WHERE `form_id` = %d '.
                  'ORDER BY `id` ASC '.
                  'LIMIT 0, 1';
        $sql = $wpdb->prepare( $sql, $this->_form_id );
        $results = $wpdb->get_results( $sql );
        if( !$results || !is_array( $results ) || count( $results ) < 1 ){
            return NULL;
        }
        
        $action_url = admin_url( 'admin.php?page='.BSK_CF7_ZOHO_Dashboard::$_bsk_cf7_to_zoho_page );
        $feed_edit_url_base = admin_url( 'admin.php?page=wpcf7&post='.$this->_form_id.'&action=edit&bsk-cf7-to-zoho-action=edit-feed&feed-id=' );
        
        $lists_data = array();
        foreach( $results as $form_feed ){
            if( count($lists_data) > 0 ){
                continue;
            }
            $feed_edit_url = $feed_edit_url_base.$form_feed->id;
            
            $feed_data = array();
            $feed_data['id'] = $form_feed->id;
            $feed_data['id_link'] = '<a href="'.$feed_edit_url.'" alt="Edit this feed" title="Edit this feed">'.$form_feed->id.'</a>';
            $feed_data['name_link'] = '<a href="'.$feed_edit_url.'" alt="Edit this feed" title="Edit this feed">'.$form_feed->name.'</a>';
            $feed_data['module'] = $form_feed->module;
            $feed_data['date_time'] = substr( $form_feed->date, 0, 10 );
            $feed_data['status_image'] = $form_feed->active ? 
                                                     '<img class="bsk-cf7-to-zoho-feed-active-inactive" src="'.BSK_CF7_ZOHO_URL.'images/active.png" alt="Active" title="Active" data-feed-id="'.$form_feed->id.'" />' : 
                                                     '<img class="bsk-cf7-to-zoho-feed-active-inactive" src="'.BSK_CF7_ZOHO_URL.'images/in-active.png" alt="Inactive" title="Inactive" data-feed-id="'.$form_feed->id.'" />';
            $debug_checked = $form_feed->debug ? ' checked' : '';
            $feed_data['debug_image'] = '<label><input type="checkbox" value="Yes" data-feed-id="'.$form_feed->id.'"'.$debug_checked.' class="bsk-cf7-to-zoho-feed-debug-chk" /> Yes</label>';
            if( $form_feed->debug && $form_feed->last_log ){
                $download_link = add_query_arg( array( 
                                                                            'bsk-cf7-to-zoho-action' => 'download-feed-last-log',
                                                                            'feed_id' => $form_feed->id,
                                                                         ), 
                                                                 $action_url );
                $download_link = wp_nonce_url( $download_link, 'download-feed-last-log', '_wpnonce' );
                
                $view_link = add_query_arg( array( 
                                                    'bsk-cf7-to-zoho-action' => 'view-feed-last-log',
                                                    'feed_id' => $form_feed->id,
                                                 ), 
                                         $action_url );
                $view_link = wp_nonce_url( $view_link, 'view-feed-last-log', '_wpnonce' );
                
                
                $feed_data['debug_image'] .= '<a href="'.$download_link.'" class="bsk-cf7-to-zoho-feed-last-log-download"><span class="dashicons dashicons-download"></span></a>';
                $feed_data['debug_image'] .= '<a href="'.$view_link.'" class="bsk-cf7-to-zoho-feed-last-log-view" style="margin-left:10px;" target="_blank"><span class="dashicons dashicons-visibility"></span></a>';
            }
            $feed_data['operation'] = '<a href="'.$feed_edit_url.'"><span class="dashicons dashicons-edit"></span></a>'.
                                                '<a href="javascript:void(0);" class="bsk-cf7-to-zoho-feed-delete" data-feed-id="'.$form_feed->id.'"><span class="dashicons dashicons-trash"></span></a>';
            
            $lists_data[] = $feed_data;
            continue;
        }

        return $lists_data;
    }
    
    function prepare_items() {
       
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 1;
        $data = array();
		
        add_thickbox();

		$this->do_bulk_action();
       
        $data = $this->get_data();
   
        $current_page = $this->get_pagenum();
        $total_items = 0;
        if( $data && is_array( $data ) ){
            $total_items = count( $data );
        }
	    if ($total_items > 0){
        	$data = array_slice( $data,( ( $current_page-1 )*$per_page ),$per_page );
		}
        $this->items = $data;

        $this->set_pagination_args( array( 
            'total_items' => $total_items,                  // We have to calculate the total number of items
            'per_page'    => $per_page,                     // We have to determine how many items to show on a page
            'total_pages' => ceil( $total_items/$per_page ) // We have to calculate the total number of pages
        ) );
    }
	

	
	function get_column_info() {
		
		$columns = array( 
							'cb'        		=> '<input type="checkbox"/>',
							'status'          => 'Status',
                            'id'				=> 'ID',
                            'name'     	    => 'Name',
                            'module'        => 'Module',
                            'datetime' 		=> 'Date',
                            'debug'         => 'Debug',
                            'operation'    => '',
						);
		
		$hidden = array();

		$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $this->get_sortable_columns() );

		$sortable = array();
		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) )
				continue;

			$data = (array) $data;
			if ( !isset( $data[1] ) )
				$data[1] = false;

			$sortable[$id] = $data;
		}

		$_column_headers = array( $columns, $hidden, $sortable, array() );

		return $_column_headers;
	}
    
    function get_results_count(){
        global $wpdb;
        
        $sql = 'SELECT COUNT(*) FROM `'.$wpdb->prefix.BSK_CF7_ZOHO::$_feeds_tbl_name.'` '.
                  'WHERE `form_id` = %d '.
                  'ORDER BY `id` ASC ';
        $sql = $wpdb->prepare( $sql, $this->_form_id );
        $results_count = $wpdb->get_var( $sql );
        
        return $results_count;
    }
}