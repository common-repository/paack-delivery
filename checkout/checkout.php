<?php

    add_action( 'woocommerce_checkout_order_processed', 'send_order_to_paack', 10, 1 );

    function send_order_to_paack( $order_id ) {
        $paack_two_hour = sanitize_text_field($_POST['paack_two_hour']);
        if ( ! $order_id ) return;
        if ( $paack_two_hour == null || $paack_two_hour == '0') return;

        $order = wc_get_order( $order_id );

        PaackApi::send_order(get_billing_info($order, $paack_two_hour), 0);
    }

    function get_delivery_windows($delivery_slot_key){
        $windows = array();
        $start_time = PaackDeliveryUtils::get_utc_date();
        $end_time = PaackDeliveryUtils::get_utc_date();

        if($delivery_slot_key == 'now') {
            $end_time->modify('+2 hour');
        } else {
            $delivery_values = explode('_', $delivery_slot_key);
            if ($delivery_values[0] == 'SD') {
                $start_time->setTime($delivery_values[1], 0);
                $end_time->setTime($delivery_values[1] + 1, 0);

            } else {
                $start_time->setTime($delivery_values[1], 0)->modify('+1 day');
                $end_time->setTime($delivery_values[1] + 1, 0)->modify('+1 day');
            }
        }

        $windows["start_time"] = PaackDeliveryUtils::format_date($start_time);
        $windows["end_time"] = PaackDeliveryUtils::format_date($end_time);

        return $windows;
    }

    function get_billing_info($order, $delivery_slot_key){
        error_log($order->get_shipping_first_name());
        $res = array(
            "store_id" => sanitize_text_field(get_option('store_id')),
            "name" => $order->get_shipping_first_name() . " " . $order->get_shipping_last_name(),
            "email" => $order->get_billing_email(),
            "phone" => $order->get_billing_phone(),
            // "description"=>$product_data["description"], // TODO: Add this
            "retailer_order_number"=> $order->get_order_number(),
            "sale_number"=> "",
            "delivery_address"=>array(
                "address"=>$order->get_shipping_address_1(),
                "city"=>$order->get_shipping_city(),
                "postal_code"=>$order->get_shipping_postcode(),
                "country"=>$order->get_shipping_country(),
            )
        );

        $res["packages"] = array();
        $res['delivery_window'] = get_delivery_windows($delivery_slot_key);

        return $res;
    }
?>
