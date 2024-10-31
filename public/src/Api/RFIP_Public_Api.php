<?php
namespace RFIP\Publicc\Api;
class RFIP_Public_Api {
    private $endpoint;

    public function __contruct() {
        $this->endpoint = RFIP_ENDPOINT_URL;
    }

    public function post_request( $path_type, $posted_data = "",$boundary = "" ) {
        $api_endpoint = RFIP_ENDPOINT_URL.$path_type;
        //$api_endpoint = 'https://5bca-103-177-194-142.in.ngrok.io/api/v1/form-submissions/22ab9a9d3eeaeccc-3034b6a3fd4ff4e6';
        
        $headers  = array();
        $args = array(
            'headers'     => $headers,
        );
            /*'method'      => 'POST',
           /* 'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
           );
            /*$args["headers"] = array(
                "content-type"  =>  "application/json"
            );*/
        $args["body"] = $posted_data;
        $response = wp_remote_post($api_endpoint,$args);
        $response_code = wp_remote_retrieve_response_code($response);
        if( is_wp_error($response) ) {
            $return_data["wp_error"] = true;
        } else if( 200 === $response_code ) {
            $body = wp_remote_retrieve_body($response);
			$body_data = json_decode($body, true);
            $return_data["body_data"] = $body_data;
        } else {
            $return_data["status_code"] = $response_code;
           
        }
        return $return_data;
    }
}
