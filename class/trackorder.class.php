<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'woocommerce_order_status_changed', 'track_order_details' );

function track_order_details($order_id = null) {
	$leapingDebug = false;
	$userIP = getUserIpAddr();
	if ($userIP == '69.201.31.171') $leapingDebug = true;

    // if it is true then it will add the parent item in the sku array, else it will not
    $empty_sku = false;

    // if order is empty get back from here
	if($order_id == null) {
	    return;
    }

	// get the order data from this order id
    $order = wc_get_order($order_id);

	// if order id is not empty and order phase is in fulfillment then good to go, else halt again.
    if($order_id == null || $order->get_status() != 'fulfillment') {
        return;
    }

    // sample order api data for API call, like order create
    $order_line_api_data = [
        'referenceId' => 'BPX001',
        'purchaseOrderNumber' => '123456',
        'skuQuantities' => [],
        'companyName' => 'warehouse south central',
        'address1' => '234 W 42 St.',
        'address2' => NULL,
        'zipCode' => '10036',
        'country' => 'USA',
        'phoneNumber' => '',
        'buyerEmail' => '',
        'city' => 'New York',
        'state' => 'NY',
        'shippingSpeed' => 'TWO_DAY',
    ];

    // get parent item here and put it in the api structured data
    $items = $order->get_items();
    foreach ($items as $item_id => $item_data) {

        $product_variation_id = $item_data['variation_id'];

        // Check if product has variation. if its a variant product then it will be survivor else simple product
        if (!empty($product_variation_id)) {

            $product = wc_get_product($item_data['variation_id']);

            // if it is treated as a complex product, that means it is a yes
            if( get_post_meta($item_data->get_product_id(), 'bpax_complex_or_simple', true) == 'yes' && get_post_meta($item_data->get_product_id(), 'bpax_complex_or_simple', true) != '' ) {
                // product type 2
                // Check if product has variation. then we will insert the first product in sku array prefixed
                // before putting the meta items to it.
                $order_line_api_data['skuQuantities'][] = (object)[
                    'skuId' => strval($product->get_sku()),
                    'upc' => NULL,
                    'skuName' => strval(retrieve_plain_title($product->get_name())),
                    'unitQuantity' => (int)$order->get_item_meta($item_id, '_qty', true), // assign main item quantity
                    'proofOfDelivery' => 'NO',
                    'insuranceRequired' => false,
                ];

                // for a line item, we will retrieve all sub-items individually. we will put it in the order api structured data array
                $add_on_meta_data = $item_data->get_formatted_meta_data();
                if(empty($add_on_meta_data)) {
                    continue;
                }

                foreach ($add_on_meta_data as $meta_data) {
                    $sku_number = retrieve_sku_from_title($meta_data->value);
                    if(!empty($sku_number)) {
                        $order_line_api_data['skuQuantities'][] = (object)[
                            'skuId' => strval($sku_number),
                            'upc' => NULL,
                            'skuName' => strval(retrieve_plain_title($meta_data->value)),
                            'unitQuantity' => (int)$order->get_item_meta($item_id, '_qty', true), // assign child item quantity from main item quantity
                            'proofOfDelivery' => 'NO',
                            'insuranceRequired' => false,
                        ];
                    } else {
                        continue;
                    }
                }

                // for variant product or for the survivor product we will check for 5 skus
                // else determine as a empty sku product and make the order to failed later.
                if( sizeof($order_line_api_data['skuQuantities']) < 5 ) {
                    $empty_sku = true;
                }

            } else {
                // if it is not a complex product, that means it is a ''
                // product type 3.
                // main product sku is not needed, neither the other information or meta. only the variation product sku is needed.
                if(empty($product->get_sku())) {

                    $empty_sku = true;

                } else {

                    $order_line_api_data['skuQuantities'][] = (object)[
                        'skuId' => strval($product->get_sku()),
                        'upc' => NULL,
                        'skuName' => strval(retrieve_plain_title($product->get_name())),
                        'unitQuantity' => (int)$order->get_item_meta($item_id, '_qty', true), // assign main item quantity
                        'proofOfDelivery' => 'NO',
                        'insuranceRequired' => false,
                    ];

                }
            }

        } else {
            // product type 1. simple product
            $product = wc_get_product($item_data['product_id']);
            // for empty sku we will ignore the order
            if(empty($product->get_sku())) {
                $empty_sku = true;
                continue;
            }
            $order_line_api_data['skuQuantities'][] = (object)[
                'skuId' => strval($product->get_sku()),
                'upc' => NULL,
                'skuName' => strval(retrieve_plain_title($product->get_name())),
                'unitQuantity' => (int)$order->get_item_meta($item_id, '_qty', true), // assign main item quantity
                'proofOfDelivery' => 'NO',
                'insuranceRequired' => false,
            ];

        }

    }

    // get other order meta here, like city zip code etc, that suits the api data needs.
    $order_meta = get_post_meta($order_id);
    if(!empty($order_meta)) {
        $order_line_api_data['referenceId'] = strval($order_id);
        $order_line_api_data['purchaseOrderNumber'] = strval($order_id);
        $order_line_api_data['companyName'] = strval($order_meta['_billing_first_name'][0].' '.$order_meta['_billing_last_name'][0]);
        $order_line_api_data['address1'] = strval($order_meta['_billing_address_1'][0]);
        $order_line_api_data['address2'] = strval($order_meta['_billing_address_2'][0]);
        $order_line_api_data['zipCode'] = strval($order_meta['_billing_postcode'][0]);
        $order_line_api_data['country'] = 'USA'; //strval($order_meta['_billing_country'][0]);
        $order_line_api_data['phoneNumber'] = strval($order_meta['_billing_phone'][0]);
        $order_line_api_data['buyerEmail'] = strval($order_meta['_billing_email'][0]);
        $order_line_api_data['city'] = strval($order_meta['_billing_city'][0]);
        $order_line_api_data['state'] = strval($order_meta['_billing_state'][0]);
    }

    if($empty_sku) {

        $order->update_status('failed');
        wp_redirect( wp_get_referer() . '&bpax_status=sku_failed' );
        exit;

    } else {

        $order_data = json_encode($order_line_api_data);
        $ware2goApi = new AccountAPI();
        $acc_stat = $ware2goApi->createOrder($order_data);

        // show final data after submitting the order
        if(!empty($acc_stat)) {
            // if we get an order id. that means we have successfully saved the data. so we can show the success message here.
            if(!empty($acc_stat['orderId'])) {
                wp_redirect( wp_get_referer() . '&bpax_status=api_success' );
                exit;
            } else {
                // or we can change the order status to Failed and show failed message.
                if($leapingDebug == true) {
                    echo "<pre>";print_r($acc_stat);echo "</pre>"; die(1);
                }
                $order->update_status('failed');
                wp_redirect( wp_get_referer() . '&bpax_status=api_failed' );
                exit;
            }
        }

    }

}

function getUserIpAddr() {
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){
		//ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		//ip pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}