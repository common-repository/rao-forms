<?php
namespace RFIP\Admin\Api;

class RFIP_Admin_Api {
    private $endpoint;

    public function __contruct() {
        $this->endpoint = RFIP_ENDPOINT_URL;
    }

    public function get_request( $path_type, $access_token = "", $refresh_token="" ) {
        $final_endpoint = RFIP_ENDPOINT_URL.$path_type;
        $args = array(
			'method'      => "GET",
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.0', 
			'blocking'    => true,
		);
        $args["headers"] = array();
        if($path_type === "backend-form/list") {
       
            $args["headers"] = array(
                "authorization" => $access_token,
                "Cookie"    =>  'jwt='.$refresh_token
            );
            
        }
		$response = wp_remote_get($final_endpoint,$args);
        $return_data["wp_error"] = false;
        $return_data["status_code"] = 200;
        $return_data["body_data"] = array();
        $response_code = wp_remote_retrieve_response_code($response);
        if( is_wp_error($response) ) {
            $return_data["wp_error"] = true;
        } else if( 200 === $response_code ) {
            $body = wp_remote_retrieve_body($response);
            $cookie = wp_remote_retrieve_cookie($response,"jwt");
			$body_data = json_decode($body, true);
            $return_data["body_data"] = $body_data;
            $return_data["cookie_data"] = $cookie;
        } else {
            $return_data["status_code"] = $response_code;
           
        }

        return $return_data;
    }
} 
