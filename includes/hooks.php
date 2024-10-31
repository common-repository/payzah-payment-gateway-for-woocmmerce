<?php
//All Hooks
add_action('init','woocommerce_payzah_manage_bank_response');
add_action('admin_enqueue_scripts', 'payzah_admin_css');
add_action('wp_enqueue_scripts', 'payzah_css');
add_action('plugins_loaded','woocommerce_payzah_init');
add_action('woocommerce_checkout_create_order','woocommerce_payzah_save_order_paymenttype_metadata' );
add_action('woocommerce_admin_order_data_after_billing_address','woocommerce_payzah_show_paymenttype_orderedit_page');
add_action('woocommerce_order_item_meta_end','woocommerce_payzah_order_email', 10, 4 );
add_action('admin_menu','woocommerce_payzah_privatekey_admin_menu');
add_action('wp_ajax_payzah_request_privatekey_form_data','payzah_request_privatekey_form_data');
add_action('wp_ajax_nopriv_payzah_request_privatekey_form_data','payzah_request_privatekey_form_data');
add_filter('woocommerce_payment_gateways','wc_payzah_payment_gateways' );
add_filter('woocommerce_get_order_item_totals','woocommerce_payzah_show_payment_type_thankyou' , 10, 3);
add_filter('plugin_action_links_payzah/payzah.php','woocommerce_payzah_privatekey_setting_form' );
add_filter( 'woocommerce_endpoint_order-received_title','woocommerce_payzah_thankyou_title' );
add_filter( 'woocommerce_thankyou_order_received_text', 'woocommerce_payzah_receive_text', 20, 2);
add_action( 'woocommerce_thankyou', 'woocommerce_payzah_tracking_number' );
add_filter( 'woocommerce_checkout_fields' , 'custom_payzan_override_checkout_fields' );
add_action( 'woocommerce_valid_order_statuses_for_order_again','payzan_add_order_again_status', 10, 1 );
add_filter('woocommerce_get_order_item_totals', 'payzah_payment_method_label_change',10,2);
add_filter('woocommerce_email_recipient_customer_completed_order', 'conditionally_manage_completed_order_email', 10, 2);