<?php
function get_forms( $post_type, $post_status = "publish", $numberposts = -1 ) {
    $posts = get_posts(
        array(
            'post_type' => $post_type,
            'post_status' => $post_status,
            'numberposts' => $numberposts
        )
    );

    return $posts;
}

function get_form_connections_by_raoform( $offline_forms ) {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    $where = "";
    $offline_forms = implode(",",$offline_forms);
    $offline_forms = str_replace(",",'","',$offline_forms);
    $offline_forms = '"'.$offline_forms.'"';
    $where = "WHERE rao_form_id IN ($offline_forms)";
    $offline_connections = $wpdb->get_results("Select * from $table $where",ARRAY_A);
    return $offline_connections;
}

function get_connection($form_provider = "",$provider_form_id = "") {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    $where = "";
    if($form_provider !== "") {
        if($provider_form_id !== "")
        $where = "WHERE form_provider = '{$form_provider}' AND provider_form_id = $provider_form_id";
        else
        $where = "WHERE form_provider = '{$form_provider}'";

    }
    $connections = $wpdb->get_row("SELECT * FROM $table $where ORDER BY created_at DESC",ARRAY_A);
    return $connections;
}
function get_connections($form_provider = "",$provider_form_id = "") {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    $where = "";
    if($form_provider !== "") {
        if($provider_form_id !== "")
        $where = "WHERE form_provider = '{$form_provider}' AND provider_form_id = $provider_form_id";
        else
        $where = "WHERE form_provider = '{$form_provider}'";

    }
    $connections = $wpdb->get_results("SELECT * FROM $table $where ORDER BY created_at DESC",ARRAY_A);
    return $connections;
}

function get_rao_forms($set_cookie = "no") {
    
    if(isset($_COOKIE["rao_access_token"]))
    $access_token = sanitize_text_field($_COOKIE["rao_access_token"]);
    else
    return "401";
    if(isset($_COOKIE["rao_refresh_token"]))
	$refresh_token = sanitize_text_field($_COOKIE["rao_access_token"]);
    else
    return "401";
    $path_type   =   "backend-form/list";
    $admin_api = new RFIP\Admin\Api\RFIP_Admin_Api();
	$response_data = $admin_api->get_request( $path_type, $access_token, $refresh_token );
    
    if( $response_data["wp_error"] ) {
        return array();
    } else if( $response_data["status_code"] === 200 ) {
        $body_data = $response_data["body_data"];
        
        //set accesstoken and refreshtoken
        $access_token = $body_data["data"]["accessToken"];
        $refresh_token = $response_data["cookie_data"]->value;
        if($set_cookie === "yes") {
        setcookie("rao_access_token",$access_token);
		setcookie("rao_refresh_token",$refresh_token);
        }
        return $body_data["data"]["forms"];
    } else if($response_data["status_code"] === 401 ) {
        return "401";
    }
    else {
        return array();

    }
    
}

function get_ninja_forms() {
    global $wpdb;

    $ninja_table = $wpdb->prefix."nf3_forms";
    $ninja_tables = $wpdb->get_results("SELECT id, form_title from $ninja_table",ARRAY_A);
    return $ninja_tables;
}

function get_rfb_token( $api_key ) {
    $status = false;
    $message = __("Unsuccessfull","raoforms");
    $path_type = "backend-form/verfiy-user-key/".$api_key;
    $admin_api = new RFIP\Admin\Api\RFIP_Admin_Api();
	$response_data = $admin_api->get_request( $path_type );
    if($response_data["wp_error"]) {
        $status = false;
        $message = _e(sprintf("Couldn't connect to RAO Form Builder, please contact <a href='%s'>here</a>","raoforms"),"raoforms");
        $access_token = "";
    } else if( $response_data["status_code"] === 200 ) {
        $access_token = $response_data["body_data"]["data"];
        $status = true;
        $message = __("Verified and Connected!", "raoforms");
    } else {
        $status = false;
        $message = __("Invalid API Key", "raoforms");
        $access_token = "";
    }

    update_option("rao_auth_status", $status);
    update_option("rao_auth_apikey", $api_key);
    
    setcookie("rao_access_token",$access_token);
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

function display_notice($class, $message) {
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_attr( $message ) );
}

function display_rao_form_empty_notice($provider) {
    wp_kses_post(printf( '<div class="notice notice-error"><p>Please create a RaoForm to integrate with %s <a href="%s">here</a></p></div>', $provider, RFIP_LIST_URL ));
}
function remove_bulk_connections( $delete_connections ) {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    $delete_connections = implode(",",$delete_connections);
    $wpdb->query("DELETE FROM $table where id in ($delete_connections)");

}

function update_rao_form_connection($db_id, $provider_form_id, $rao_form_id, $form_provider) {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    
    $update_data = array();
    $update_data["rao_form_id"] = $rao_form_id;
    $update_data["updated_at"] = current_time("mysql");
    $format_array = array("%s","%s");
    $wpdb->update($table, $update_data, array('id'=>$db_id),$format_array, array("%d"));
}

function remove_form_connections_by_provider( $contact_form_id, $form_provider ) {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    $wpdb->query("DELETE FROM $table where form_provider= 'cf7' AND provider_form_id = $contact_form_id");
}

function add_rao_form_connection( $provider_form_id, $rao_form_id, $form_provider ) {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    $insert_data = array();
        $insert_data["form_provider"] = $form_provider;
		$insert_data["provider_form_id"] = $provider_form_id;
		$insert_data["rao_form_id"] = $rao_form_id;
		$insert_data["created_at"] = current_time("mysql");
		$insert_data["updated_at"] = current_time("mysql");
        $array = array("%s","%d","%s","%s","%s");
		$insert = $wpdb->insert($table, $insert_data, $array);
}

function add_bulk_connections( $contact_form_id, $new_connections, $form_provider) {
    global $wpdb;
    $table = $wpdb->prefix.'rao_form_connections';
    foreach($new_connections as $key => $rao_form_id) {
        $insert_data = array();
        $insert_data["form_provider"] = $form_provider;
		$insert_data["provider_form_id"] = $contact_form_id;
		$insert_data["rao_form_id"] = $rao_form_id;
		$insert_data["created_at"] = current_time("mysql");
		$insert_data["updated_at"] = current_time("mysql");
        $array = array("%s","%d","%s","%s","%s");
		$insert = $wpdb->insert($table, $insert_data, $array);
    }
}

function render_rao_forms() {
    $rao_forms = maybe_unserialize(get_transient("rao_forms_list"));
    $contact_form_id = sanitize_text_field($_GET["post"]);
    $db_connections = get_connections("cf7",$contact_form_id);
    
    $active_connections = wp_list_pluck($db_connections,"rao_form_id");

    
    ?>
    <style>
        div#s2id_connect_to_rao {
    width: 75% !important;
}
    </style>
    <h5><?php _e('Connect this form to RAO Forms', "raoforms"); ?></h5>
    <?php if(!empty($rao_forms)) { ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="connect_to_rao"><?php _e("Connect To","raoforms");?></label>
            </th>
            <td>
                <select id="connect_to_rao" name="connect_to_rao">
                    <option value="" disabled="disabled"><?php _e("Select RAO Form","raoforms"); ?></option>
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
            </td>
        </tr>
    </table>
    <?php } elseif($rao_forms === "401") {
    display_rao_form_empty_notice("Contact Form7");
    }  else {
    $tab_url = admin_url("admin.php?page=rao-forms");
    
    wp_kses_post(_e(sprintf("<div class='notice notice-error'><p>OOPS! Couldn't connect with RAO Forms, please authorize the connection under <a href='%s'>setup</a> tab</p></div>",esc_url($tab_url)),"raoforms"));
     
    } ?>
    <?php
    
}
