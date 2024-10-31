<?php 
$access_token = "";
$class = 'notice notice-error';
if(isset($_COOKIE["rao_access_token"])) {
    $access_token = sanitize_text_field($_COOKIE["rao_access_token"]);
}
else 
{
    $tab_url = admin_url("admin.php?page=rao-forms");
    //check
    wp_kses_post(_e(sprintf("<div class='notice notice-error'><p>OOPS! Couldn't connect with RAO Forms, please authorize the connection under <a href='%s'>setup</a> tab</p></div>",$tab_url),"raoforms"));
    
    //display_notice($class,$message);
    wp_die();
}

if(!class_exists('WPCF7_Submission'))
{
    $message = __("Please install/activate Contact Form7 to configure connections with RAO Forms","raoforms");
    display_notice(esc_attr($class),esc_attr($message));
    wp_die();
    $contact_forms = $contact_forms_array = array();
} else {
    $contact_forms = get_forms( "wpcf7_contact_form" );
$contact_forms_array = array();
}


if($access_token !== "") {
$rao_forms = maybe_unserialize(get_transient("rao_forms_list"));
if(empty($rao_forms)) {
    display_rao_form_empty_notice("Contact Form7");
    wp_die();
}
} 
$rao_forms_array = array();


$get_connections = get_connections("cf7");
if(!empty($get_connections))
{
    $display_heading = "";
} else {
    $display_heading = 'style="display:none;"';
}

?>
<input type="hidden" id="form_provider" value="cf7" />
<div class="raoforms-error-notice notice notice-error" style="display:none;">
</div>
<div class="raoforms-success-notice notice notice-success" style="display:none;">
</div>
<div class="grid-container">
        <div class="provider-forms">
            <select id="provider-options" class="provider-options" name="provider-options">
                <?php
                     echo '<option value="" selected disabled>'.__("Select Contact Form","").'</option>';
                    if( !empty($contact_forms) ) {
                       
                        foreach( $contact_forms as $form ) {
                            $id = $form->ID;
			                $name = $form->post_title;
                            $contact_forms_array[$id] = $name;
                            if( $id !== "" && $name != "" ) {
                                echo '<option value="'.esc_attr($id).'">'.esc_attr($name).'</option>';
                            }
                        }
                    }
                ?>
            </select>
            <span class="to"><?php _e("TO","raoforms"); ?></span>
        </div>
        
        <div class="rao-forms">
            <select id="rao-options" class="rao-options" name="rao-options">

                <?php
                echo '<option value="" selected disabled>'.__("Select RAO Form","").'</option>';
                    foreach($rao_forms as $id => $form_data) {
                        if($form_data["_live"])
                        $status = ' (Active)';
                        else
                        $status = ' (Inactive)';
                        $rao_forms_array[$form_data["formKey"]] = $form_data["name"].$status;
                        echo '<option value="'.esc_attr($form_data["formKey"]).'">'.esc_attr($form_data["name"]).esc_attr($status).'</option>';
                    }
                ?>
            </select>
            <span class="plus-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.1.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M432 256c0 17.69-14.33 32.01-32 32.01H256v144c0 17.69-14.33 31.99-32 31.99s-32-14.3-32-31.99v-144H48c-17.67 0-32-14.32-32-32.01s14.33-31.99 32-31.99H192v-144c0-17.69 14.33-32.01 32-32.01s32 14.32 32 32.01v144h144C417.7 224 432 238.3 432 256z"/></svg></span>
        </div>
    
</div>
<div class="connected-forms-section">
    <div class="dummy connection-data" style="display:none;">
        <div class="grid-container">
            <div class="">
                <input data-provider-form-id="" type="text" class="provider-form-id" value="" disabled="disabled" />
                <span class="to"><?php echo wp_kses_post(__("TO","raoforms"));?></span>
            </div>
                <div class="">
                    <select class="rao-options-selected" name="rao-options-selected" disabled="disabled">
                    <?php
                        echo '<option value="" selected disabled>'.__("Select RAO Form","").'</option>';
                        foreach($rao_forms as $id => $form_data) {
                            if($form_data["_live"])
                            $status = ' (Active)';
                            else
                            $status = ' (Inactive)';
                            $rao_forms_array[$form_data["formKey"]] = $form_data["name"].$status;
                            echo '<option value="'.esc_attr($form_data["formKey"]).'">'.esc_attr($form_data["name"]).esc_attr($status).'</option>';
                        }
                        ?>
                    </select>
                    <span class="edit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M373.1 24.97C401.2-3.147 446.8-3.147 474.9 24.97L487 37.09C515.1 65.21 515.1 110.8 487 138.9L289.8 336.2C281.1 344.8 270.4 351.1 258.6 354.5L158.6 383.1C150.2 385.5 141.2 383.1 135 376.1C128.9 370.8 126.5 361.8 128.9 353.4L157.5 253.4C160.9 241.6 167.2 230.9 175.8 222.2L373.1 24.97zM440.1 58.91C431.6 49.54 416.4 49.54 407 58.91L377.9 88L424 134.1L453.1 104.1C462.5 95.6 462.5 80.4 453.1 71.03L440.1 58.91zM203.7 266.6L186.9 325.1L245.4 308.3C249.4 307.2 252.9 305.1 255.8 302.2L390.1 168L344 121.9L209.8 256.2C206.9 259.1 204.8 262.6 203.7 266.6zM200 64C213.3 64 224 74.75 224 88C224 101.3 213.3 112 200 112H88C65.91 112 48 129.9 48 152V424C48 446.1 65.91 464 88 464H360C382.1 464 400 446.1 400 424V312C400 298.7 410.7 288 424 288C437.3 288 448 298.7 448 312V424C448 472.6 408.6 512 360 512H88C39.4 512 0 472.6 0 424V152C0 103.4 39.4 64 88 64H200z"/></svg>
                    </span>
                    <span class="minus-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.1.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M400 288h-352c-17.69 0-32-14.32-32-32.01s14.31-31.99 32-31.99h352c17.69 0 32 14.3 32 31.99S417.7 288 400 288z"/></svg>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- Check -->
    <div class="page-title" <?php echo esc_attr($display_heading);?>>
        <h2><?php _e('Contact Form 7 Connections',"raoforms"); ?></h2>
    </div>
    <div class="connected-forms">
        <?php 
            $delete_connections = array();
            
            if( !empty($get_connections) ) :
            foreach($get_connections as $connection) :
            if(!isset($contact_forms_array[$connection["provider_form_id"]])) {
            $delete_connections[] = $connection["id"];
            continue;
            }

            if(!isset($rao_forms_array[$connection["rao_form_id"]]))
            continue;

            $provider_form_title = $contact_forms_array[$connection["provider_form_id"]];
            $rao_form_title = $rao_forms_array[$connection["rao_form_id"]];
            echo wp_kses_post('<div id="connection-'.esc_attr($connection["id"]).'" class="connection-data">');
            echo wp_kses_post('<div class="grid-container">');
            echo '<div><input type="text" data-provider-form-id="'.esc_attr($connection["provider_form_id"]).'" class="provider-form-id" value="'.esc_attr($provider_form_title).'" disabled="disabled"/><span class="to">TO</span></div>';
           // echo '<div><input type="text" class="rao-form-id" value="'.$rao_form_title.'" disabled="disabled"/><span class="minus-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.1.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M400 288h-352c-17.69 0-32-14.32-32-32.01s14.31-31.99 32-31.99h352c17.69 0 32 14.3 32 31.99S417.7 288 400 288z"></path></svg></span></div>';
            
            ?>
             <div class="">
                    <select class="rao-options-selected" name="rao-options-selected" disabled="disabled">
                    <?php
                        echo '<option value="" selected disabled>'.__("Select RAO Form","").'</option>';
                        foreach($rao_forms as $id => $form_data) {
                            $selected = "";
                            if($form_data["_live"])
                            $status = ' (Active)';
                            else
                            $status = ' (Inactive)';
                            if($form_data["formKey"] === $connection["rao_form_id"])
                            $selected = 'selected="selected"';
                            $rao_forms_array[$form_data["formKey"]] = $form_data["name"].$status;
                            echo '<option value="'.esc_attr($form_data["formKey"]).'" '.esc_attr($selected).'>'.esc_attr($form_data["name"]).esc_attr($status).'</option>';
                        }
                        ?>
                    </select>
                    <span class="edit-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M373.1 24.97C401.2-3.147 446.8-3.147 474.9 24.97L487 37.09C515.1 65.21 515.1 110.8 487 138.9L289.8 336.2C281.1 344.8 270.4 351.1 258.6 354.5L158.6 383.1C150.2 385.5 141.2 383.1 135 376.1C128.9 370.8 126.5 361.8 128.9 353.4L157.5 253.4C160.9 241.6 167.2 230.9 175.8 222.2L373.1 24.97zM440.1 58.91C431.6 49.54 416.4 49.54 407 58.91L377.9 88L424 134.1L453.1 104.1C462.5 95.6 462.5 80.4 453.1 71.03L440.1 58.91zM203.7 266.6L186.9 325.1L245.4 308.3C249.4 307.2 252.9 305.1 255.8 302.2L390.1 168L344 121.9L209.8 256.2C206.9 259.1 204.8 262.6 203.7 266.6zM200 64C213.3 64 224 74.75 224 88C224 101.3 213.3 112 200 112H88C65.91 112 48 129.9 48 152V424C48 446.1 65.91 464 88 464H360C382.1 464 400 446.1 400 424V312C400 298.7 410.7 288 424 288C437.3 288 448 298.7 448 312V424C448 472.6 408.6 512 360 512H88C39.4 512 0 472.6 0 424V152C0 103.4 39.4 64 88 64H200z"/></svg>
                    </span>
                    <span class="minus-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.1.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M400 288h-352c-17.69 0-32-14.32-32-32.01s14.31-31.99 32-31.99h352c17.69 0 32 14.3 32 31.99S417.7 288 400 288z"/></svg>
                    </span>
                </div>
            <?php
            
            echo wp_kses_post('</div></div>');
    
            endforeach;    
            
        endif;  

        ?>
    </div>

</div>
<?php
if(!empty($delete_connections))
remove_bulk_connections($delete_connections);