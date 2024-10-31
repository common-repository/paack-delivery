<?php
class PaackApi{
    const API_HOST_TEST = 'https://test.api.paack.co';
    const API_HOST_PROD = 'https://api.paack.co';
    const API_PATH = '/api/public/v1';

    public static function get($url){
        $path = self::API_HOST_TEST;

        if(get_option('paack_testing') != 1){
            $path = self::API_HOST_PROD;
        }

        $request = wp_remote_get( $path . self::API_PATH . $url . "?api=" . get_option('api_token'));
        $body=  wp_remote_retrieve_body($request);

        return $body;
    }

    public static function check_store($stores_id){
        $res = array("error"=>1);
        $response = json_decode(self::get('/stores/'.$stores_id),true);

        if(isset($response['data'])){
            $res['error'] = 0;
            $res['data'] = $response['data'];
        }

        return $res;
    }

    public static function send_order($order_json){
        $order_json["api"] = get_option('api_token');
        $url = self::API_HOST_TEST;

        if(get_option('paack_testing') !=1){
            $url = self::API_HOST_PROD;
        }

        $response = wp_remote_post(
            $url . self::API_PATH . '/orders',
            array(
                'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
                'body' => json_encode($order_json),
                'timeout' => 5,
                'method' => 'POST'
            )
        );

        $body= wp_remote_retrieve_body($response);
        return $body;
    }
}

?>
