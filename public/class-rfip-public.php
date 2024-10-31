<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://app.raoforms.com
 * @since      1.0.0
 *
 * @package    RFIP
 * @subpackage RFIP/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    RFIP
 * @subpackage RFIP/public
 * @author     Your Name <email@example.com>
 */
class RFIP_Public {

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
	 * @param      string    $rao_forms       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $rao_forms, $version ) {

		$this->rao_forms = $rao_forms;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->rao_forms, plugin_dir_url( __FILE__ ) . 'css/rfip-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->rao_forms, plugin_dir_url( __FILE__ ) . 'js/rfip-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * 
	 */
	public function send_cf7_data( $contactform7 ) {
		$form_id = $contactform7->id();
		$connections = get_connections("cf7",$form_id);
		$form_tags = $contactform7->scan_form_tags();
		
		$submission = WPCF7_Submission::get_instance(); 
    	$posted_data = $submission->get_posted_data();
		$boundary = wp_generate_password( 24 );
		$payload = "";
		foreach( $posted_data as $key => $form_data ) {
			if( strpos($key,"ile-") )
			unset($posted_data[$key]);

		}
		$files = $submission->uploaded_files();
		$file_data = array();
		$uploaded_files = array();
		
		$file_names = array();
		foreach($_FILES as $file_key => $file) {

			if( empty($file) ) continue;
			$posted_data[$file_key] = $file;

		}
		
		/*foreach($files as $key => $file) {
			$file = is_array( $file ) ? reset( $file ) : $file;
			if(empty($file)) continue;
			$posted_data[$file_names[$key]] = file_get_contents( $file );
			/*$payload .= '--' . $boundary;
			$payload .= "\r\n";
			$payload .= 'Content-Disposition: form-data; name="' . 'upload' .
				'"; filename="' . basename( $file ) . '"' . "\r\n";
			//        $payload .= 'Content-Type: image/jpeg' . "\r\n";
			$payload .= "\r\n";
			$payload .= file_get_contents( $file );
			$payload .= "\r\n";
		}*/


		foreach($connections as $connection_id => $connection_data) {
			$rao_form_id	=	$connection_data["rao_form_id"];
			$path_type		=	"form-submissions/".$rao_form_id;
			
			$public_api		= new RFIP\Admin\Api\RFIP_Public_Api();
			$response_data	= 	$public_api->post_request($path_type, $posted_data, $boundary);
		}
		
	}

	public function send_wpforms_data( $fields, $entry, $form_data, $entry_id ) {
		
		$posted_data = array();
		$form_id = $form_data["id"];
		$connections = get_connections("wpforms",$form_id);
		if(!empty($connections)) {
		foreach($fields as $key => $field_data) {
			if($field_data["type"] == "name") {
				$posted_data["Name"] = $field_data["value"];
			}
			else {
				$key = $field_data["name"];
				$value = $entry["fields"][$field_data["id"]];
				$posted_data[$key] = $value;
			}
		}
		foreach($connections as $connection_id => $connection_data) {
			$rao_form_id	=	$connection_data["rao_form_id"];
			$path_type		=	"form-submissions/".$rao_form_id;
			$public_api		=	new RFIP\Admin\Api\RFIP_Public_Api();
			$response_data	= 	$public_api->post_request($path_type, $posted_data);
		}

		}
		
		
	}

	public function send_ninjaform_data( $form_data ) {
		$fields_by_key = $form_data["fields_by_key"];
		$posted_data = array();
		$form_id = $form_data["form_id"];
		$connections = get_connections("ninjaforms",$form_id);
		if(!empty($connections)) {
		foreach($fields_by_key as $field_key => $field_object) {
			$value = $field_object["value"];
			$posted_data[$field_key] = $value;
		}
		
		foreach($connections as $connection_id => $connection_data) {
			$rao_form_id	=	$connection_data["rao_form_id"];
			$path_type		=	"form-submissions/".$rao_form_id;
			$public_api		= new RFIP\Admin\Api\RFIP_Public_Api();
			$response_data	= 	$public_api->post_request($path_type, $posted_data);
		}


		}
	}
	}



	

