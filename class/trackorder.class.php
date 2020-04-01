<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'woocommerce_order_status_changed', 'track_order_details' );

function track_order_details($order_id = null) {

    // if order is empty get back from here
	if($order_id == null){
	    return;
    }

	// if it is true then it will add the parent item in the sku array, else it will not
    $include_main_item = false;

	// get the order data from this order id
    $order = wc_get_order($order_id);

	// if order id is not empty and order phase is in fulfillment then good to go, else halt again.
    if($order_id == null || $order->get_status() != 'fulfillment'){
        return;
    }

    // sample order api data for API call, like order create
    $order_line_api_data = [
        'referenceId' => '******',
        'purchaseOrderNumber' => '*********',
        'skuQuantities' => [],
        'companyName' => '*********',
        'address1' => '***********',
        'address2' => NULL,
        'zipCode' => '**********',
        'country' => '*********',
        'phoneNumber' => '',
        'buyerEmail' => '',
        'city' => '**********',
        'state' => '*******',
        'shippingSpeed' => '**********',
    ];

    // get parent item here and put it in the api structured data
    $items = $order->get_items();
    foreach ($items as $item_id => $item_data){

        if($include_main_item){
            $product = wc_get_product($item_data->get_product_id());
            // for empty sku we will ignore the product item
            if(empty($product->get_sku())){
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

        // for a line item, we will retrieve all sub-items individually. we will put it in the order api structured data array
        $add_on_meta_data = $item_data->get_formatted_meta_data();
        if(empty($add_on_meta_data)){
            continue;
        }

        foreach ($add_on_meta_data as $meta_data){
            $sku_number = retrieve_sku_from_title($meta_data->value);
            // for empty sku we will ignore the product item
            if(empty($sku_number)){
                continue;
            }
            $order_line_api_data['skuQuantities'][] = (object)[
                'skuId' => strval($sku_number),
                'upc' => NULL,
                'skuName' => strval(retrieve_plain_title($meta_data->value)),
                'unitQuantity' => (int)$order->get_item_meta($item_id, '_qty', true), // assign child item quantity from main item quantity
                'proofOfDelivery' => 'NO',
                'insuranceRequired' => false,
            ];
        }
    }

    // get other order meta here, like city zip code etc, that suits the api data needs.
    $order_meta = get_post_meta($order_id);
    if(!empty($order_meta)){
        $order_line_api_data['purchaseOrderNumber'] = strval($order_id);
        $order_line_api_data['companyName'] = strval($order_meta['_billing_first_name'][0].' '.$order_meta['_billing_last_name'][0]);
        $order_line_api_data['address1'] = !empty(strval($order_meta['_shipping_address_index'][0])) ? strval($order_meta['_shipping_address_index'][0]) : strval($order_meta['_billing_address_index'][0]);
        $order_line_api_data['address2'] = strval($order_meta['_billing_address_1'][0]) .' / '. strval($order_meta['_billing_address_2'][0]);
        $order_line_api_data['zipCode'] = strval($order_meta['_billing_postcode'][0]);
        $order_line_api_data['phoneNumber'] = strval($order_meta['_billing_phone'][0]);
        $order_line_api_data['buyerEmail'] = strval($order_meta['_billing_email'][0]);
        $order_line_api_data['city'] = strval($order_meta['_billing_city'][0]);
        $order_line_api_data['state'] = strval($order_meta['_billing_state'][0]);
    }

    $order_data = json_encode($order_line_api_data);
    $ware2goApi = new AccountAPI();
    $acc_stat = $ware2goApi->createOrder($order_data);

    // show final data after submitting the order
	if(!empty($acc_stat)){
	    // if we get an order id. that means we have successfully saved the data. so we can show the success message here.
	    if(!empty($acc_stat['orderId'])){
            add_action('shop_order_updated_messages', 'success_message');
        } else {
            $order->update_status('failed');
            add_action('shop_order_updated_messages', 'failed_message');
        }
    } else {
        $order->update_status('failed');
        add_action('shop_order_updated_messages', 'failed_message');
    }
}
