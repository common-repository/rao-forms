<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://app.raoforms.com
 * @since      1.0.0
 *
 * @package    RFIP
 * @subpackage RFIP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    RFIP
 * @subpackage RFIP/admin
 * @author     Your Name <email@example.com>
 */
class RFIP_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $rao_forms    The ID of this plugin.
	 */
	private $rao_forms;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $rao_forms       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $rao_forms, $version ) {

		$this->rao_forms = $rao_forms;
		$this->version = $version;

	}

	public function load_rfip_menu() {
		$object = new RFIP\Admin\Settings\RFIP_Settings();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rao_Forms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rao_Forms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->rao_forms, plugin_dir_url( __FILE__ ) . 'css/rfip-admin.css', array(), $this->version, 'all' );
		$current_request_page = isset($_GET["page"]) ? sanitize_text_field($_GET["page"]) : "";
		if($current_request_page && ($current_request_page === "wpcf7" || $current_request_page === "wpforms-builder")) {
			wp_register_style( 'rfip-select2', RFIP_PLUGIN_URL ."assets/select2/select2.css", false, '1.0', 'all' );
			wp_enqueue_style( 'rfip-select2' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in RFIP_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The RFIP_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$current_request_page = isset($_GET["page"]) ? sanitize_text_field($_GET["page"]) : "";
		$current_tab = isset($_GET["tab"]) ? sanitize_text_field($_GET["tab"]) : "";
		$allowed_pages = array("wpcf7","wpforms-builder","ninja-forms");
		if($current_request_page && in_array($current_request_page, $allowed_pages)) {
			wp_register_script( 'rfip-select2', RFIP_PLUGIN_URL ."assets/select2/select2.min.js", array( 'jquery' ), '1.0', true );
    		wp_enqueue_script( 'rfip-select2' );
			wp_enqueue_script( $this->rao_forms, plugin_dir_url( __FILE__ ) . 'js/rfip-admin.js', array( 'jquery','select2' ), $this->version, false );
		}
		
		if( $current_tab !== "general" ) {
			wp_enqueue_script( "rao-forms-connect", plugin_dir_url( __FILE__ ) . 'js/rfip-connect.js', array( 'jquery' ), $this->version, false );
			wp_localize_script( "rao-forms-connect", 'raoforms', array('ajaxurl'=>admin_url('admin-ajax.php')));
		}

		if( $current_request_page === "rao-forms" ){
			wp_enqueue_script( "rao-forms-general", plugin_dir_url( __FILE__ ) . 'js/rfip-general.js', array( 'jquery' ), $this->version, false );
			//wp_localize_script( "rao-forms-connect", 'raoforms', array('ajaxurl'=>admin_url('admin-ajax.php')));
		}
		

	}

	public function edit_form_connection() {
		global $wpdb;
	
		$table = $wpdb->prefix."rao_form_connections";
		$provider_form_id = sanitize_text_field( $_POST["provider_form_id"] );
		$form_provider = sanitize_text_field($_POST["form_provider"]);
		$rao_form_id = sanitize_text_field( $_POST["rao_form_id"] );
		if( $provider_form_id == "" || $rao_form_id == "") {
			$error_message = __('Provider Form ID/Rao Form ID cannot be left empty');
			wp_send_json_error(esc_html(stripslashes($error_message)));
			wp_die();
		}
		$check_if_exist = $wpdb->get_col('select id from '.$table.' where provider_form_id='.$provider_form_id.' AND rao_form_id="'.$rao_form_id.'" AND form_provider="'.$form_provider.'"');
		
		if(!empty($check_if_exist)) {
			$error_message = __('This connection already exist');
			wp_send_json_error(esc_html(stripslashes($error_message)));
			wp_die();
		}
		
		$update_data["rao_form_id"] = $rao_form_id;
		$update_data["updated_at"] = current_time("mysql");
		$array = array("%s","%s");
		$id = str_replace("connection-","",sanitize_text_field($_POST["id"]));
		$update = $wpdb->update($table, $update_data, array("id"=>$id),$array,array("%d"));
		if($form_provider === "ninjaforms")
		{
			$this->update_ninja_meta($provider_form_id,$rao_form_id);
		}
		$success_message = __("Connection updated successfully","raoforms");
		wp_send_json_success(esc_html(stripslashes($success_message)));
		wp_die();


	}

	public function update_ninja_meta($ninja_form_id = "", $rao_form_id = "") {
		if( $ninja_form_id === "" || $rao_form_id === "" )
		return;
		global $wpdb;
		$ninja_table = $wpdb->prefix."nf3_form_meta";
		//delete ninja form cache
		$wpn_helper = new WPN_Helper();
		$wpn_helper->delete_nf_cache($ninja_form_id);
		//delete from ninja form
		$update = $wpdb->update($ninja_table, array("value"=>$rao_form_id,"meta_value" => $rao_form_id), array("parent_id"=>$ninja_form_id,"meta_key"=>"raoformslist"),array("%s","%s"),array("%d","%s"));
	}

	public function add_form_connection() {
		global $wpdb;
		$table = $wpdb->prefix."rao_form_connections";
		$provider_form_id = sanitize_text_field( $_POST["provider_form_id"] );
		$rao_form_id = sanitize_text_field( $_POST["rao_form_id"] );
		$form_provider = sanitize_text_field( $_POST["form_provider"]);
		if( $provider_form_id == "" || $rao_form_id == "") {
			$error_message = __('Provider Form ID/Rao Form ID cannot be left empty');
			wp_send_json_error(esc_html(stripslashes($error_message)));
			wp_die();
		}
		
		$check_if_exist = $wpdb->get_col('select id from '.$table.' where provider_form_id='.$provider_form_id.' AND rao_form_id="'.$rao_form_id.'" AND form_provider="'.$form_provider.'"');
		if(!empty($check_if_exist)) {
			$error_message = __('This connection already exist');
			wp_send_json_error(esc_html(stripslashes($error_message)));
			wp_die();
		}
		
		$check_if_more_than_one = $wpdb->get_col('select count(id) from '.$table.' where provider_form_id='.$provider_form_id.' AND rao_form_id != "" AND form_provider="'.$form_provider.'"');
		
		if($check_if_more_than_one[0] >= 1) {
			$error_message = __('You can not connect configured CF7 form to more than one RAO Form',"raoforms");
			wp_send_json_error(esc_html(stripslashes($error_message)));
			wp_die();
		}
		$current_time = current_time("mysql");
		$insert_data["form_provider"] = $form_provider;
		$insert_data["provider_form_id"] = $provider_form_id;
		$insert_data["rao_form_id"] = $rao_form_id;
		$insert_data["created_at"] = $current_time;
		$insert_data["updated_at"] = $current_time;
		
		$array = array("%s","%d","%s","%s","%s");
		$insert = $wpdb->insert($table, $insert_data, $array);
		$inserted_id = $wpdb->insert_id;
		if($form_provider === "ninjaforms")
		{
			$this->update_ninja_meta($provider_form_id,$rao_form_id);
		}
		
		wp_send_json_success(esc_attr($inserted_id));
		
		wp_die();
	}

	

	/**
	 * Remove FORM Connection from Database
	 */
	public function remove_form_connection() {
		global $wpdb;
		$id = sanitize_text_field( $_POST["id"] );
		$form_provider = sanitize_text_field( $_POST["form_provider"]);
		$provider_form_id = sanitize_text_field( $_POST["provider_form_id"]);
		$table = $wpdb->prefix."rao_form_connections";
		$id = str_replace("connection-","",$id);
		$delete = $wpdb->delete($table,array('id' => $id));
		if( $form_provider === "ninjaforms" )
		{
			$this->update_ninja_meta($provider_form_id,"");
		}
		if($delete)
		wp_send_json_success();
		else
		wp_send_json_error();
		wp_die();
	}

	/**
	 * 
	 */
	public function rfb_authorize() {

		if(isset($_GET["rfb_authorize_api_nonce"]) && wp_verify_nonce( $_GET['rfb_authorize_api_nonce'], 'rfb_authorize_api_nonce'))
		{
			$api_key = sanitize_text_field( $_GET["rfb_api_key"] );
			$get_token_response = $this->get_rfb_token($api_key);
			$get_token_response = json_encode($get_token_response);

			//wp_safe_redirect(admin_url("admin.php")."?page=rao-forms-settings&processed=yes");
			wp_send_json_success($get_token_response);
		} else {
			wp_send_json_error();
		}
		wp_die();
	}
	public static function get_rfb_token( $api_key ) {
		$status = false;
		$message = __("Unsuccessfull","raoforms");
		$path_type = "backend-form/verfiy-user-key/".$api_key;
		$admin_api = new RFIP\Admin\Api\RFIP_Admin_Api();
		$response_data = $admin_api->get_request( $path_type );
		
		if($response_data["wp_error"]) {
			$status = false;
			$message = _e(sprintf("Couldn't connect to RAO Form Builder, please contact <a href='%s'>here</a>",esc_url(RFIP_APP_URL)),"raoforms");
			$access_token = $refresh_token = "";
		} else if( $response_data["status_code"] === 200 ) {
			
			$access_token = $response_data["body_data"]["data"];
			$refresh_token = $response_data["cookie_data"]->value;
			
			$status = true;
			$message = __("Verified and Connected!", "raoforms");
		} else {
			$status = false;
			$message = __("Invalid API Key", "raoforms");
			$access_token = $refresh_token = "";
		}

		update_option("rao_auth_status", $status);
		update_option("rao_auth_apikey", $api_key);
		
		setcookie("rao_access_token",$access_token);
		setcookie("rao_refresh_token",$refresh_token);
		//update_option("rao_auth_token", $access_token);
		if(!$status) {
			$api_key = "";
			update_option("rao_auth_apikey_backend", $api_key);

			if(isset($_COOKIE["rao_access_token"]))
			unset($_COOKIE["rao_access_token"]);
		} else {
			update_option("rao_auth_apikey_backend", $api_key);
		}
		$return["status"]	=	$status;
		$return["message"]	=	$message;
		return $return;
	}	

	public static function check_rao_access_token() {
		if(!is_admin())
		return;
		global $rao_forms_list;
		
		$allowed_pages = array("rao-forms","wpcf7","wpforms-builder","ninja-forms");
		$current_request_page = isset($_GET["page"]) ? sanitize_text_field($_GET["page"]) : "";
		if( $current_request_page && ( in_array( $current_request_page, $allowed_pages ) ) && !isset($_GET["status"]) ) {
			//check for rao access token
			$query_param = "";
			$count = 0;
			
			$access_token = "";
			$api_key = get_option("rao_auth_apikey");
			
			if($api_key == "")
			return;
			if(isset($_COOKIE["rao_access_token"]))
			$access_token = sanitize_text_field($_COOKIE["rao_access_token"]);

			if($access_token !== "") {
				//try to fetch raoforms
				$rao_forms = get_rao_forms("yes");
				$rao_forms_list = $rao_forms;
				
				if(empty($rao_forms) || !is_array($rao_forms)) {
				$access_token = "";
				delete_transient("rao_forms_list");
				
				} else {
					set_transient("rao_forms_list",maybe_serialize($rao_forms));
				}
			}
			
			if($access_token == "" || !$access_token && !isset($_GET["status"]))
			{
			
				if($api_key !== "" || $api_key ) {
					
					if(!empty($_REQUEST)) {
						foreach($_REQUEST as $key => $value) {
							if($count < 1) {
								$query_param .= '?'.$key.'='.$value;
							} else {
								$query_param .= '&'.$key.'='.$value;
							}
							$count++;
						}
					}	
				$token_data = RFIP_Admin::get_rfb_token($api_key);
				
				$status = $token_data["status"];
				
				if(!$status){
				if($query_param === "")
				$query_param .= '?status=' .$param;
				else
				$query_param .= '&status='.$param;
				}
				$url = admin_url("admin.php");
				$url = $url.$query_param;
				
				wp_safe_redirect($url);
				} else {
					return;
				}
			}
		} else {
			return;
		}
	}

	/**
	 * Remove Cf7 & WPForms if forms are removed by admin
	 */
	public function validate_form_connections( $post_id, $post ) {
		if( $post->post_type === "wpcf7_contact_form")
		{
			//remove particular connection
			$this->remove_connection($post_id,"cf7");
		}

		if( $post->post_type === "wpforms" ) {
			$this->remove_connection($post_id,"wpforms");
		}
	}

	/**
	 * Run db queries to remove specific connections
	 */
	public function remove_connection( $post_id,$form_provider ) {
		global $wpdb;
		$table = $wpdb->prefix."rao_form_connections";
		$where_data["provider_form_id"] = $post_id;
		$where_data["form_provider"] = $form_provider;
		$where_format = array("%d","%s");
		$delete = $wpdb->delete($table,$where_data,$where_format);
	}

	/**
	 * Add extra tab on CF7 Settings page
	 */
	public function add_raoforms_tab_cf7( $panels ) {
		$panels["rao-forms-connection"] = array(
			"title" => __('RAO Forms',"raoforms"),
			"callback" => "render_rao_forms"
		);
		return $panels;
	}

	/**
	 * Save CF7 connections from CF7 Additional Tab Source
	 */
	public function save_cf7_connections( $contact_form, $args, $context ) {
		$contact_form_id = $args["id"];

		/*if(isset($args["connect_to_rao"]) && !empty($args["connect_to_rao"])) {
			$new_rao_connections = $args["connect_to_rao"];
			$db_connections = get_connections("cf7",$contact_form_id);
			$present_rao_connections = wp_list_pluck($db_connections,"rao_form_id","id");
			$no_longer_connections = array_diff($present_rao_connections,$new_rao_connections);
			$no_longer_connections = array_keys($no_longer_connections);
			if(!empty($no_longer_connections))
			remove_bulk_connections($no_longer_connections);
			
			$new_connections_to_add = array_diff($new_rao_connections,$present_rao_connections);
			if(!empty($new_connections_to_add))
			add_bulk_connections($contact_form_id, $new_connections_to_add, "cf7");
			
		} else {
			//user might have removed all connections
			remove_form_connections_by_provider($contact_form_id,"cf7");
		}*/

		if(isset($args["connect_to_rao"]) && $args["connect_to_rao"] !== "") {
			$selected_form = $args["connect_to_rao"];
			$db_connection = get_connection("cf7",$contact_form_id);
			if(empty($db_connection)) {
				add_rao_form_connection($contact_form_id,$selected_form,"cf7");
			} else {
				$db_id = $db_connection["id"];
				
				$db_rao_form = $db_connection["rao_form_id"];
				if($db_rao_form !== $selected_form)
				update_rao_form_connection($db_id, $contact_form_id, $selected_form,"cf7");

			}

		} else {
			$this->remove_connection($contact_form_id,"cf7");
		}

		
	}


	/**
	 * 
	 */
	public function add_raoforms_tab_wpforms( $sections, $form_data) {
		$sections["rfb-settings"] = __("RAO Forms","raoforms");
		return $sections;
	}

	public function render_raoforms_content( $wpforms ) {
		echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-rfb-settings" data-panel="rfb-settings">';
		echo '<h5>'.__("Connect this form to RAO Forms","raoforms").'</h5>';
		$form_options = array();
		$form_value = array();
		
		$rao_forms = maybe_unserialize(get_transient("rao_forms_list"));
		$wpform_id = $wpforms->form->ID;
		$db_connections = get_connections("wpforms",$wpform_id);
    	$active_connections = wp_list_pluck($db_connections,"rao_form_id");
		?>
		<style>
        div#s2id_connect_to_rao {
    		width: 75% !important;
		}
		.select2-results,.select2-drop-active {
			z-index: 9999999;
		}
    	</style>
		<div id="wpforms-panel-field-settings-form_title-wrap" class="wpforms-panel-field">
		<?php if(!empty($rao_forms)) { ?>
			<label for="wpforms-panel-field-settings-form_title"><?php _e("Select RAO Forms","raoforms");?></label>
			<select id="connect_to_rao" name="connect_to_rao">
				<option></option>
				<?php
				foreach($rao_forms as $id => $form_data) {
					if($form_data["_live"])
					$status = ' (Active)';
					else
					$status = ' (Inactive)';
					if(in_array($form_data["formKey"],$active_connections))
					$selected = 'selected="selected"';
					else
					$selected = "";
					echo '<option value="'.esc_attr($form_data["formKey"]).'" '.esc_attr($selected).'>'.esc_attr($form_data["name"]).esc_attr($status).'</option>';
				}
				?>
			</select>
		<?php } elseif($rao_forms === "401") {
			display_rao_form_empty_notice("WPForms");
		} else {
			
			$tab_url = admin_url("admin.php?page=rao-forms");
    
			echo '<div class="notice notice-error"><p>';
			_e(sprintf("OOPS! Couldn't connect with RAO Forms, please authorize the connection under <a href='%s'>setup</a> tab",esc_url($tab_url)),"raoforms");
			echo '</p></div>';
		}
		?>
		</div>
		<!--<input type="hidden" id="rao_forms_json" name="settings[rao_forms_json]" value="[]" />-->
		<?php
		echo '</div>';
	}

	/**
	 * Save WPForms Connections
	 */
	public function save_wpforms_connections( $form_id, $form_data ) {
		
		$selected_form = $form_data["connect_to_rao"];
		if($selected_form !== "") {
			$db_connection = get_connection("wpforms",$form_id);
			if(empty($db_connection)) {
				add_rao_form_connection($form_id,$selected_form,"wpforms");
			} else {
				$db_id = $db_connection["id"];
				
				$db_rao_form = $db_connection["rao_form_id"];
				if($db_rao_form !== $selected_form)
				update_rao_form_connection($db_id, $contact_form_id, $selected_form,"wpforms");
			}
		} else {
			$this->remove_connection($form_id,"wpforms");
		}



	}

	public function add_rao_forms_tab_ninjaforms( $setting_types ) {
		$setting_types["raoforms"] = array(
			'id'	=>	"raoforms",
			"nicename"	=>	"RAO Forms"
		);
		return $setting_types;
	}

	public function add_rao_forms_content_ninjaforms( $form_settings ) {
		$ninja_form_id = sanitize_text_field($_GET["form_id"]);
		if(isset($_COOKIE["rao_access_token"]) && $_COOKIE["rao_access_token"] !== "") {
			$access_token = sanitize_text_field($_COOKIE["rao_access_token"]);
		} else {
			$access_token = "";
		}
		if($access_token !== "") {
		$rao_forms = maybe_unserialize(get_transient("rao_forms_list"));
		if(!empty($rao_forms)) {
		$status = true;
		$db_connections = get_connection("ninjaforms",$ninja_form_id);

		$selected_form = "";
		if( !empty($db_connections) ) 
		$selected_form = $db_connections["rao_form_id"];
    	//$active_connections = wp_list_pluck($db_connections,"rao_form_id");
		$options[] = array(
			"label"	=>	__("Select RAO Form","raoforms"),
			"value"	=>	""
		);

			foreach($rao_forms as $key => $form_data) {
				if($form_data["_live"])
				$status = ' (Active)';
				else
				$status = ' (Inactive)';
				$options[] = array(
					"label"	=>	$form_data["name"].$status,
					"value" => $form_data["formKey"]
				);
			}
		
		$form_settings["raoforms"] = array(
			"raoformslist"	=>	array(
				"name"	=>	'raoformslist',
				"type"	=>	"select",
				"label"	=>	"RAO Forms",
				"width"	=>	"full",
				"group"	=>	"primary",
				"options"	=> $options,
				"value"	=>	$selected_form,
				"help"	=>	""

			)
			);
		} else {
			$status = false;
		}
		} else {
			$status = false;
		}
		if(!$status) {
			if($rao_forms === "401"){
				$message = __("Please create a RaoForm to integrate with Ninja Forms","raoforms");
    
    			$message .= '<a href="'.RFIP_LIST_URL.'"> here</a>';
				$final_text = $message;
			}
			else{
				$tab_url = admin_url("admin.php?page=rao-forms");
				$text = __("OOPS! Couldn't connect with RAO Forms, please authorize","raoforms");
				$link = '<a href="'.esc_url($tab_url).'">'.__('here','raoforms').'</a>';
				$final_text = "<p style='color:red;'><b>".esc_attr(stripslashes($text))." ".esc_url($link)."</b></p>"; 
			}
			$form_settings["raoforms"] = array(
				"raoformserror" =>	array(
					"name"	=>	'raoformserror',
					"type"	=>	"textbox",
					"label"	=>	wp_kses_post($final_text),
					"width"	=>	"full",
					"group"	=>	"primary",
					"value"	=>	"",
					"help"	=>	""
					
	
				)
			);
		}
			return $form_settings;
	}

	public function save_ninjaform_connections( $form_id ) {
		$form_data = json_decode( stripslashes( $_POST['form'] ), ARRAY_A );

		$selected_form = $form_data["settings"]["raoformslist"];
		
		
		$db_connections = get_connection("ninjaforms",$form_id);

		if($selected_form == "")
		$this->remove_connection($form_id,"ninjafoms");

		if(empty($db_connections)) {
			//insert
			add_rao_form_connection($form_id,$selected_form,"ninjaforms");

		} else {
			//update
			$db_id = $db_connections["id"];
			$db_rao_form = $db_connections["rao_form_id"];
			if($db_rao_form !== $selected_form && $selected_form !== "")
			update_rao_form_connection($db_id, $form_id, $selected_form,"ninjaforms");
			else
			$this->remove_connection($form_id,"ninjafoms");
		}

	}

	public function register_my_nf_action( $actions ) {
		
		require_once plugin_dir_path( __FILE__ ) . 'class-ninjaactionclass.php';
		$actions["raoformslist"] = new NinjaActionClass();
		return $actions;

	}

	public function get_offline_forms( $var ) {
		return $var === 0;
	}

	public function display_online_offline_notice() {
		global $rao_forms_list,$form_status_list;
		
		$alert_message = "";
		if(!empty($rao_forms_list)) {
			//get the form status
			$form_status = wp_list_pluck($rao_forms_list,"_live","formKey");
			$offline_forms = array();
			$form_title = wp_list_pluck($rao_forms_list,"name","formKey");
			
			foreach($form_status as $key => $value) {
				if(!$value)
				$offline_forms[] = $key;
			}

			
			if(!empty($offline_forms)) {
				$form_connections = get_form_connections_by_raoform( $offline_forms );
				
				if(!empty($form_connections)) {
					
					$plugins_list = array();
					foreach($form_connections as $key => $connection_data) {
						
						if($connection_data["form_provider"] === "cf7")
						$provider_name = "Contact Form7";
						else if($connection_data["form_provider"] === "wpforms")
						$provider_name = "WPForms";
						else if($connection_data["form_provider"] === "ninjaforms")
						$provider_name = "Ninja Forms";
						
						$form_title_text[] = $form_title[$connection_data["rao_form_id"]];
						$plugins_list[] = $provider_name;
					}
					$form_title_text = array_unique($form_title_text);
					$form_title_text_count = count($form_title_text);
					if($form_title_text_count <= 1)
					$singular_text = "is inactive and ";
					else
					$singular_text = "are inactive and those aree ";
				$form_title_text = implode(", ",$form_title_text);
					$plugins_list = array_unique($plugins_list);
					$provider_name = implode(", ",$plugins_list);
					$here = '<a href="'.RFIP_LIST_URL.'">here</a>';
					$alert_message .= __("This is to notify you that RAo Forms <b>{forms_list}</b> {singular_text} connected with the plugins <b>{plugins}</b>. Due to the inactive state, form data won't be reflected in RAO Forms. Please enable the forms {here} to receive form submissions data on RAO FORMS","raoforms");
					$alert_message = str_replace("{forms_list}",$form_title_text,$alert_message);
				$alert_message = str_replace('{plugins}',$provider_name,$alert_message);
				$alert_message = str_replace('{here}',$here,$alert_message);
				$alert_message = str_replace('{singular_text}',$singular_text,$alert_message);
				echo wp_kses_post('<div class="notice notice-warning"><p>'.stripslashes($alert_message).'</p></div>'); 
				}
				
			}
		}
	}

	
}