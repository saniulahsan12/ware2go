<?php
defined('ABSPATH') or die('No script kiddies please!');

class BpaxAccountAPI
{

    function __construct()
    {
        $this->api_url  = get_option('acc_api_url'); //api url
        $this->merchant_id  = get_option('acc_merchant_id'); //merchannt id
        $this->token  = get_option('acc_token'); //basic token for authentication
    }

    function addApiLog($data)
    {
        global $wpdb;
        return $wpdb->insert($wpdb->prefix . 'ware2go_api_logs_bpax', $data);
    }

    function viewApiLog($page = 1, $items_per_page = 1)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ware2go_api_logs_bpax';
        $page = isset($page) ? abs((int) $page) : 1;
        $offset = ( $page * $items_per_page ) - $items_per_page;
        $query = "SELECT * FROM $table_name ORDER BY id DESC LIMIT $items_per_page OFFSET $offset";
        $result = $wpdb->get_results($query);
        return $result;
    }

    function filterData($data)
    {
        return json_decode($data, true);
    }

    function execCurl($url, $type = "GET", $postData = null)
    {

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => $type,        //set request type post or get
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_HTTPHEADER     => array(
                "Content-Type: application/json",
                "Authorization: Basic {$this->token}"
            )
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        if ($type == 'POST' && !empty($postData)) {
            //attach encoded JSON string to the POST fields
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            //return response instead of outputting
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        $content = curl_exec($ch);

        // log api data response to log table of the API
        self::addApiLog([
            'response' => $content,
            'api' => $url,
            'data' => !empty($postData) ? json_encode($postData) : null,
            'method' => $type,
            'order_id' => !empty(json_decode($content)->orderId) ? json_decode($content)->orderId : null,
            'status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
        ]);

        curl_close($ch);

        return $content;
    }

    function getOrders()
    {
        $str = $this->api_url."/merchants/".$this->merchant_id."/orders";
        $result = self::execCurl($str, "GET");
        return self::filterData($result);
    }

    function getOrderDetails($order_id)
    {
        $str = $this->api_url."/merchants/".$this->merchant_id."/orders".$order_id;
        $result = self::execCurl($str, "GET");
        return self::filterData($result);
    }

    function createOrder($order_data)
    {
        $str = $this->api_url."/merchants/".$this->merchant_id."/orders";
        $result = self::execCurl($str, "POST", $order_data);
        return self::filterData($result);
    }
}
