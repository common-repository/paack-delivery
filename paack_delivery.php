<?php
/**
* Plugin Name: Paack Delivery
* Plugin URI: https://github.com/PaackEng/woocommerce
* Description: Plugin para consultar y generar envios.
* Version: 1.0.2
* Author: Paack Logistics Iberia S.L.
* License: GPL2
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once 'admin/paack_delivery_utils.php';
require_once 'admin/paack_delivery_menu.php';
require_once 'checkout/checkout.php';

class PaackDelivery {

    function __construct() {

    }

    public function load() {
        add_action( 'wp_ajax_is_zip_code', [$this, 'is_zip_code_ajax']);
        add_action( 'wp_ajax_nopriv_is_zip_code', [$this, 'is_zip_code_ajax']);
        add_action( 'woocommerce_after_order_notes', [$this, 'hidden_input_field'] );
        add_action('woocommerce_checkout_before_order_review', [$this, 'bottom_filter']);
    }

    public function is_zip_code_ajax(){
        $zip_codes = get_option('zip_codes');
        $is = false;
        $message = get_option('paack_message_zip_code_error');

        if($zip_codes!=null && $zip_codes != '') {
            $zip_codes_permited = explode(',',$zip_codes);
            $zipCode = sanitize_text_field($_POST['zip_code']);
            $is =in_array($zipCode,$zip_codes_permited);

            if($is){
                $message = get_option('paack_message_zip_code_success');
            }
        }
        $res = array("availability"=> $is,"message" => $message);
        wp_send_json($res);
        wp_die();
    }

    public function hidden_input_field(){
        echo "<input type='hidden' name='paack_two_hour' value ='0' id='paack-two-hour'>";
    }


    public function bottom_filter(){
        $store_id = get_option('store_id');
        $is_store_valid = get_option('is_store_valid');
        $zip_codes = get_option('zip_codes');

        if($is_store_valid == 1 && $zip_codes != ''){
        $this->add_assets();
        echo $this->paack_html();
        }
    }

    private function paack_html(){
        ?>
        <div class="isa_success">
            <a id="paack_delivery_slot_link" href="#test-form" class="wp-paack-pop">
            Get your order in the next 2h or schedule your delivery
            </a>
        </div>
        <div id="delivery_slot_info" class="isa_success isa_hidden text-center"></div>
        <div id="test-form" class="mfp-hide white-popup-block">
            <h2>Please enter your postcode</h2>
            <hr/>
            <p><?php esc_html(get_option("text_popup"));?></p>
            <div id="consult-zip-code">
                <fieldset style="border:0;">
                    <label for="name">Name</label>
                    <input id="zip_code" name="zip_code" type="text" style="width:250px;" placeholder="Postcode" required="">
                    <button type="button" id="button-zip-code">Check</button>
                </fieldset>
            </div>
            <div class="isa_hidden no-padding" id="message_zip_code">
                <i class="fa fa-info-circle"></i>
                <span></span>
            </div>
            <table class="isa_hidden" id="table_options"></table>
            <button type="button" class="isa_hidden right" id="button_zip_code">
                Continue
            </button>
        </div>
        <?php
    }

    private function add_assets(){
            $this->register_assets();
            wp_enqueue_style('style-magnific');
            wp_enqueue_style('style-paack');
            wp_enqueue_script('script-magnific');
            wp_enqueue_script('script-paack');
            wp_localize_script ('script-paack', 'paack', array ('ajax_url' => admin_url ('admin-ajax.php')));

    }

    private function register_assets(){
        wp_register_style('style-magnific',(plugin_dir_url( __FILE__ ) .  "assets/css/magnific-popup.css"), false);
        wp_register_style('style-paack',( plugin_dir_url( __FILE__ ) . "assets/css/paack.css"), false);
        wp_register_script('script-magnific', (plugin_dir_url( __FILE__ ) . "assets/js/jquery.magnific-popup.min.js"), false);
        wp_register_script('script-paack', (plugin_dir_url( __FILE__ ) . "assets/js/paack-delivery-script.js"), false);
    }
}

$paack_delivery = new PaackDelivery();
$paack_delivery->load();
