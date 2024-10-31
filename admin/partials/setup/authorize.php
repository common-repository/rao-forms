<?php
$admin_url = admin_url("admin-post.php");
$authorize_nonce = wp_create_nonce("rfb_authorize_api_nonce");
$api_key = get_option("rao_auth_apikey");
$api_status = get_option("rao_auth_status");
if($api_key == "") {
    //api is not set
    $status_class = "";
    $status_message = "";
} else {
    if(!$api_status) {
        $status_class = "error";
        $status_message = __("Not Connected!","raoforms");
    } else {
        $status_class = "success";
        $status_message = __("Verified & Connected!","raoforms");
    }
}
$help_text = 'Please <a href="%s">click here</a> to get the API key from RAO Forms Dashboard';

?>
 <p class="description">
                <?php _e(sprintf($help_text,RFIP_PROFILE_URL),"raoforms"); ?>
            </p>
<form action="<?php echo esc_url($admin_url);?>" id="authorize_api">
    <input type="hidden" name="action" value="rfb_authorize" />
    <input type="hidden" name="rfb_authorize_api_nonce" value="<?php echo esc_attr($authorize_nonce);?>" />
    
    
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="rfb_api_key"><?php _e("API Key","raoforms") ?></label>
            </th>
            <td>
            <input required id="rfb_api_key" type="text" name="rfb_api_key" value="<?php echo esc_attr($api_key);?>" placeholder="<?php _e("Enter API Key","raoforms"); ?>" />
            </td>
            <td>
            <span class="status <?php echo esc_attr($status_class);?>"><?php echo esc_html($status_message);?></span>
            
            </td>
        </tr>
    </table>
    <input type="submit" class="button button-primary" value="<?php _e("Connect to RAO Forms","raoforms");?>" id="rfb_authorize_api" />
</form>