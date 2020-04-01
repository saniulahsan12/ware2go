<?php
function success_message() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Congrats! This order is successfully sent to Ware2Go API.', 'woo-ware2go' ); ?></p>
    </div>
    <?php
}

function error_message() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'Sorry! Woo-Ware2Go needs WooCommerce installed to run, Please install WooCommerce first.', 'woo-ware2go' ); ?></p>
    </div>
    <?php
}

function failed_message() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'Sorry! Failed to send order to Ware2Go API, Please try again.', 'woo-ware2go' ); ?></p>
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

// SETUP CRON
add_action('wp', 'ballax_schedule_cron');
function ballax_schedule_cron() {
    if (!wp_next_scheduled( 'ballax_cron' )){
        wp_schedule_event(time(), 'ballaxRepeatTime', 'ballax_cron');
    }
}

// the CRON hook for firing function
add_action('ballax_cron', 'ballax_cron_function_trigger');
add_action('wp_head', 'ballax_cron_function_trigger'); //test on page load

// the actual function
function ballax_cron_function_trigger() {
    // see if fires via email notification
    try {
//        wp_mail('user@domain.com', 'Cron Worked', date('r'));
    } catch (Exception $e){

    }
}

// CUSTOM TIME INTERVAL
add_filter('cron_schedules', 'ballax_cron_add_intervals');
function ballax_cron_add_intervals($schedules) {
    $schedules['ballaxRepeatTime'] = array(
        'interval' => 3600,
        'display' => __('Every hour repeat Ballax CRON...')
    );
    return $schedules;
}