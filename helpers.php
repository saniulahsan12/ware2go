<?php
function error_message(){
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'Sorry! Woo-Ware2Go needs WooCommerce installed to run, Please install WooCommerce first.', 'woo-ware2go' ); ?></p>
    </div>
    <?php
}

function retrieve_sku_from_title($title){
    if(!empty($title)){
        $tmp_array = explode('Sku:', $title);
        if(!empty($tmp_array[1])){
            return $tmp_array[1];
        }
    }
    return false;
}

function retrieve_plain_title($title){
    if(!empty($title)){
        $tmp_array = explode(':', $title);
        if(!empty($tmp_array[0])){
            return $tmp_array[0];
        } else {
            return $title;
        }
    }
    return false;
}

add_action( 'woocommerce_product_options_general_product_data', 'bpax_option_group' );

function bpax_option_group(){
    echo '<div class="options_group">';

    woocommerce_wp_checkbox( array(
        'id'      => 'bpax_complex_or_simple',
        'value'   => get_post_meta( get_the_ID(), 'bpax_complex_or_simple', true ),
        'label'   => 'Complex product',
        'desc_tip' => true,
        'description' => 'This field is for to determine that if it is a simple product or a complex product(mix of many product). check it for complex or uncheck for simple',
    ) );

    echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'bpax_save_fields', 10, 2 );
function bpax_save_fields( $id, $post ){
    update_post_meta( $id, 'bpax_complex_or_simple', $_POST['bpax_complex_or_simple'] );
}

if(!empty($_GET['bpax_status']) && $_GET['bpax_status'] == 'sku_failed'){
    echo '<script>alert("Order Status Changed to Failed because of 5 Missing SKU\'s in the Items.");</script>';
}

if(!empty($_GET['bpax_status']) && $_GET['bpax_status'] == 'api_failed'){
    echo '<script>alert("Order Status Changed to Failed because it cannot update the ware 2 go API data, Please try again.");</script>';
}

if(!empty($_GET['bpax_status']) && $_GET['bpax_status'] == 'api_success'){
    echo '<script>alert("Order Status Successfully changed and sent to ware 2 API.");</script>';
}

add_action("wp_ajax_bpax_load_api_logs", "bpax_load_api_logs");
function bpax_load_api_logs() {

    $request = $_GET;
    if ( !isset( $request['nonce'] ) || !wp_verify_nonce( $request['nonce'], 'bpax_submit' ) ) {
        exit;
    }
    $result = [];
    $ware2goApi = new AccountAPI();
    $response = $ware2goApi->viewApiLog($request['page'], $request['data_per_page']);
    if(empty($response)){
        exit;
    }
    foreach($response as $key => $rsp){
        $result[$key] = (array)$rsp;
        $result[$key]['data'] = json_decode($rsp->data);
    }
    wp_send_json($result);
    die();
}