<?php
require_once 'paack_api.php';

add_action("admin_menu", "paack_delivery_create_menu");
add_action("admin_init", "paack_delivery_register_data");

function paack_delivery_create_menu() {
    // Aquí va el código para crear opciones del menú
    add_menu_page('Configuración Paack', 'Paack', 'manage_options', 'paack_slug', 'paack_delivery_output_menu','dashicons-share-alt2');
}

function paack_delivery_output_menu() {
    $idStore = esc_html(get_option('store_id'));
    $is_store_valid = esc_html(get_option('is_store_valid'));
    ?>
    <h1>Paack Plugin configuration</h1>
    <p>Plugin to send orders to Paack</p>
    <?php
        if($idStore != null && $idStore != ''){
            if($is_store_valid==1){
                echo paack_delivery_messages('updated notice','Paack plugin successfully configured');
            }else{
                echo paack_delivery_messages('error notice','The store ID is not valid. Please contact Paack');
            }
        }else{
            echo paack_delivery_messages('error notice','Please enter a valid store ID.');
        }

    ?>
    <div class="wrap">
        <form action="options.php" method="POST">
            <?php
                settings_fields('paack_setting_group');
                do_settings_sections('paack_setting_group');
            ?>
            <input type="hidden" name="is_store_valid" value="<?=$is_store_valid?>">
            <table class="form-table">
                <tbody>
                    <tr valing="top">
                        <th><label for="api_token">API Token</label></th>
                        <td>
                            <input type="text" name="api_token" id="api_token" value="<?=esc_html(get_option('api_token'))?>" maxlength="100" style="width:600px" required> *
                        </td>
                    </tr>
                    <tr valing="top">
                        <th><label for="store_id">Store ID</label></th>
                        <td>
                            <input type="number" name="store_id" id="store_id" value="<?=$idStore?>" required=""> *
                        </td>
                    </tr>
                    <tr valing="top">
                        <th><label for="text_popup">Popup text</label></th>
                        <td>
                            <textarea name="text_popup" id="text_popup" style="width:600px; height: 100px;"><?=esc_html(get_option('text_popup'))?></textarea>
                        </td>
                    </tr>
                    <tr valing="top">
                        <th><label for="zip_codes">Postcodes</label></th>
                        <td>
                            <textarea name="zip_codes" id="zip_codes" style="width:600px; height: 100px;"><?=esc_html(get_option('zip_codes'))?></textarea><br>
                            <span class="description">Add all the available postcodes for Paack separated by a comma (,)</span>
                        </td>
                    </tr>
                    <tr valing="top">
                        <th><label for="paack_message_zip_code_success">Postcode OK message</label></th>
                        <td>
                            <input type="text" name="paack_message_zip_code_success" id="paack_message_zip_code_success" value="<?=esc_html(get_option('paack_message_zip_code_success'))?>" maxlength="100" style="width:600px">
                        </td>
                    </tr>
                    <tr valing="top">
                        <th><label for="paack_message_zip_code_error">Postcode error message</label></th>
                        <td>
                            <input type="text" name="paack_message_zip_code_error" id="paack_message_zip_code_error" value="<?=esc_html(get_option('paack_message_zip_code_error'))?>" maxlength="100" style="width:600px" >
                        </td>
                    </tr>
                    <tr valing="top">
                        <th><label for="paack_testing">Test mode</label></th>
                        <td>
                            <input type="checkbox" name="paack_testing" value="1" <?php checked( 1 == esc_html(get_option( 'paack_testing' ))); ?> />
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button();?>
        </form>
    </div>
    <?php
  }

  function paack_delivery_register_data(){
      $idStore = esc_html(get_option('store_id'));
      update_option( 'is_store_valid', paack_delivery_validate_stores_id($idStore));
      if(get_option('text_popup')==''){update_option('text_popup','Would you like to schedule your delivery, or get it in two hours? Check your postcode here');}
      if(get_option('paack_message_zip_code_success')==''){update_option( 'paack_message_zip_code_success', 'Valid postcode');}
      if(get_option('paack_message_zip_code_error')==''){update_option( 'paack_message_zip_code_error', 'Unfortunately we do not offer this service in your location');}


      register_setting('paack_setting_group','text_popup');
      register_setting('paack_setting_group','store_id');
      register_setting('paack_setting_group','api_token');
      register_setting('paack_setting_group','is_store_valid');
      register_setting('paack_setting_group','zip_codes');

      register_setting('paack_setting_group','paack_message_zip_code_success');
      register_setting('paack_setting_group','paack_message_zip_code_error');
      register_setting('paack_setting_group','paack_testing');
  }

    function paack_delivery_validate_stores_id($idStore){
        $isValid = 0;
        if($idStore != null && $idStore != ''){
            $res = PaackApi::check_store($idStore);
            if($res["error"] == 0){
                $isValid = 1;
            }
        }

        return $isValid;
    }

    function paack_delivery_messages($class, $message){
        return '<div class="'.$class.'"><p>'.$message.'</p></div>';
    }
?>
