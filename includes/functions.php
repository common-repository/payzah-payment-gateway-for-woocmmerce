<?php
/*
 * This file contain all function which is custommize woocommerce and  request private key form
 */

/**
 * Register and enqueue CSS for the admin.
 *
 * @version 1.3
 */
if (!function_exists("payzah_admin_css")) {
    function payzah_admin_css()
    {
        $url = PAYZAH_PLUGIN_DIR . "/assets/css/admin-style.css";
        wp_register_style("payzah-admin-style", $url);
        wp_enqueue_style("payzah-admin-style");
    }
}

/**
 * Register and enqueue CSS for the frontend.
 *
 * @version 1.3
 */
if (!function_exists("payzah_css")) {
    function payzah_css()
    {
        $url = PAYZAH_PLUGIN_DIR . "/assets/css/payzah-style.css";
        wp_register_style("payzah-style", $url);
        wp_enqueue_style("payzah-style");
    }
}

/**
 * Set Payment Gatway On Checkout page .
 *
 * @version 1.3
 */
if (!function_exists("wc_payzah_payment_gateways")) {
    function wc_payzah_payment_gateways($gateways)
    {
        $gateways[] = "WC_Payzah";
        return $gateways;
    }
}

/**
 * Store payment type while place order
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_save_order_paymenttype_metadata")) {
    function woocommerce_payzah_save_order_paymenttype_metadata($order)
    {
       
        $transaction_type = sanitize_text_field(
            $_POST["payzah_transaction_type"]
        );
        
        if ($_POST["payment_method"] === "payzah" && isset($transaction_type)) {
            $order->update_meta_data(
                "_payzah_transaction_type",
                $transaction_type
            );
            $order->save();
        }
    }
}

/**
 * Show payment type status on order edit page
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_show_paymenttype_orderedit_page")) {
    function woocommerce_payzah_show_paymenttype_orderedit_page($order)
    {
        if ($order->get_meta("_payzah_transaction_type")) {
            $payment_type = $order->get_meta("_payzah_transaction_type");
            $payzah_payment_type = 
            $payment_type == "1" 
                ? "K-NET" 
                : ($payment_type == "2" 
                    ? "Credit Card" 
                    : ($payment_type == "4" 
                        ? "Apple Pay Debit"
                        : ($payment_type == "5" 
                            ? "Apple Pay Credit" 
                            : "")));
            echo "<p><strong>" .
                __("Payment type") .
                ":</strong> " .
                $payzah_payment_type .
                "</p>";
        }
    }
}

/**
 * Show payment type status on order detail page
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_show_payment_type_thankyou")) {
    function woocommerce_payzah_show_payment_type_thankyou(
        $total_rows,
        $order,
        $tax_display
    ) {
        $payment_method = $order->get_payment_method();
        if ($payment_method == "payzah") {
            $new_rows = [];
            $payment_type = $order->get_meta("_payzah_transaction_type");
            $payzah_payment_type = 
            $payment_type == "1" 
                ? "K-NET" 
                : ($payment_type == "2" 
                    ? "Credit Card" 
                    : ($payment_type == "4" 
                        ? "Apple Pay Debit"
                        : ($payment_type == "5" 
                            ? "Apple Pay Credit" 
                            : "")));
            foreach ($total_rows as $total_key => $total_values) {
                $new_rows[$total_key] = $total_values;
                if ($total_key === "payment_method") {
                    $new_rows["payment_type"] = [
                        "label" => __("Payment type", "payzah") . ":",
                        "value" => $payzah_payment_type,
                    ];
                }
                $new_rows["payment_status"] = [
                    "label" => __("Payment status", "payzah") . ":",
                    "value" => $order->get_status(),
                ];
            }
            $total_rows = $new_rows;
        }
        return $total_rows;
    }
}

/**
 * Show payment type status on order email
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_order_email")) {
    function woocommerce_payzah_order_email(
        $item_id,
        $item,
        $order,
        $plain_text
    ) {
        if ($order->get_meta("_payzah_transaction_type")) {
            $payment_type = $order->get_meta("_payzah_transaction_type");
            // echo $payment_type;exit;
            $payzah_payment_type = 
            $payment_type == "1" 
                ? "K-NET" 
                : ($payment_type == "2" 
                    ? "Credit Card" 
                    : ($payment_type == "4" 
                        ? "Apple Pay Debit"
                        : ($payment_type == "5" 
                            ? "Apple Pay Credit" 
                            : "")));
            echo "<p><strong>" .
                __("Payment type") .
                ":</strong> " .
                $payzah_payment_type .
                "</p>";
        }
    }
}

/**
 * Options for the setting page for the plugin.
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_privatekey_setting_form")) {
    function woocommerce_payzah_privatekey_setting_form($links)
    {
        $url = esc_url(
            add_query_arg(
                "page",
                "payzah-privatekey",
                get_admin_url() . "admin.php"
            )
        );
        $settings_link =
            "<a href='$url'>" . __("Get Private Key", "payzah") . "</a>";
        array_unshift($links, $settings_link);
        return $links;
    }
}

/**
 * Set callback function for add admin menu for the private key form page.
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_privatekey_admin_menu")) {
    function woocommerce_payzah_privatekey_admin_menu()
    {
        add_menu_page(
            "Payzah Private Key",
            "Payzah Private Key",
            "manage_options",
            "payzah-privatekey",
            "woocommerce_pazah_get_privatekey_form"
        );
        //Hide menu item from admin backend only link will active
        remove_menu_page("payzah-privatekey");
    }
}

/**
 * Set callback function for retrive the privake key form.
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_pazah_get_privatekey_form")) {
    function woocommerce_pazah_get_privatekey_form()
    {
        $logo = payzah_image_dir . "logo.png";
        $email_icon_url = payzah_image_dir . "email.png";
        $phone_icon_url = payzah_image_dir . "phone.png";
        ?>
		<div class="request-form">
			<img src="<?php echo $logo; ?>">
			<h2>Request Private key</h2>
			<div class="info_detail"><img src="<?php echo $email_icon_url; ?>" width="20"><span>info@payzah.com</span></div>
			<div class="info_detail"><img src="<?php echo $phone_icon_url; ?>" width="18"><span>+965 22410760/</span><span>+965 56500733</span></div>
			<h4>Please fill the below form to request private key for your website, Payzah team will contact you to complete your payment gateway integration process.</h4>
			<form method="POST" id="payzah-request-form" action="">
				<label><b>Customer Name</b></label><input type="text" name="name" class="regular-text" placeholder="Enter Your Name" />
				<div class="payzah-error" data-error="name"></div>
				<label><b>Business Name</b></label><input type="text" name="business_name" class="regular-text" placeholder="Enter Your Business Name" />
				<div class="payzah-error" data-error="business_name"></div>
				<label><b>Customer Email</b></label><input type="email" name="email" class="regular-text" placeholder="Enter Your Email" />
				<div class="payzah-error" data-error="email"></div>
				<label><b>Customer Phoen</b></label><input type="text" name="phone" class="regular-text" placeholder="Enter Your Phone Number" />
				<div class="payzah-error" data-error="phone"></div>
				<label><b>Business Brief</b></label><textarea name="business_brief" placeholder="Enter Your Business Detail" rows="5"></textarea>
				<div class="payzah-error" data-error="business_brief"></div>
				<label><b>Business Type</b></label><input type="radio" name="business_type" checked="checked" value="1">Corporate
				<input type="radio" name="business_type" value="2">Individual
				<div class="payzah-error" data-error="business_type"></div>
				<input type="submit" name="send_request"  value="Send Request" class="button-primary"/><img src="<?php echo payzah_image_dir .
        "loader.gif"; ?>" id="loader">
			</form>	
		</div>
		<style type="text/css">
		</style> 
		<script type="text/javascript" >
		var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';
		var adminurl = '<?php echo admin_url("admin.php"); ?>';
		jQuery('#payzah-request-form').submit(function(event){
			jQuery('#loader').show();
			event.preventDefault();
			jQuery(".payzah-error").hide();
			jQuery.ajax({
				url: ajaxurl,
				type: "POST",
				data: {
					action: 'payzah_request_privatekey_form_data',
					name: jQuery("[name='name']").val(),
					business_name: jQuery("[name='business_name']").val(),
					email: jQuery("[name='email']").val(),
					phone: jQuery("[name='phone']").val(),
					business_brief: jQuery("[name='business_brief']").val(),
					business_type: jQuery("[name='business_type']").val(),
				},
				success: function(res){
					res = jQuery.parseJSON(res);
					if(res.status == true){
						alert(res.data);
						window.location.href=adminurl+'?page=wc-settings&tab=checkout';
					}else{
						jQuery.each(res.data, function(key, value){
							jQuery(".payzah-error[data-error='"+key+"']").html(value).show();
						});
					}
					jQuery('#loader').hide();
				}
			});
		});
		</script>
	<?php
    }
}

/**
 * Ajax function for send email regarding private key.
 *
 * @version 1.3
 */
if (!function_exists("payzah_request_privatekey_form_data")) {
    function payzah_request_privatekey_form_data()
    {
        $error = [];
        $name = sanitize_text_field($_POST["name"]);
        $business_name = sanitize_text_field($_POST["business_name"]);
        $email = sanitize_text_field($_POST["email"]);
        $phone = sanitize_text_field($_POST["phone"]);
        $business_brief = sanitize_text_field($_POST["business_brief"]);
        $business_type = sanitize_text_field($_POST["business_type"]);
        if (empty($name)) {
            $error["name"] = "Please enter name!";
        }
        if (empty($business_name)) {
            $error["business_name"] = "Please enter business name!";
        }
        if (empty($email)) {
            $error["email"] = "Please enter email!";
        }
        if (empty($phone)) {
            $error["phone"] = "Please enter phone!";
        }
        if (empty($business_brief)) {
            $error["business_brief"] = "Please enter business brief!";
        }
        if (empty($business_type)) {
            $error["business_type"] = "Please enter business type!";
        }
        if (count($error) > 0) {
            $res = [
                "status" => false,
                "data" => $error,
            ];
            echo json_encode($res);
        } else {
            $type = $business_type == 1 ? "Corporate" : "Individual";
            $to = "info@payzah.com";
            $subject = "Request For Payzah Private Key";
            $body = "<html><body><div style='background-color: #e7e4e9;padding: 10px;'><h3>Hello Payzah Team,</h3></br><p style='font-size: 20px;'>$name has request you for private key , below are the details:</p></br><p><b>Business Name:</b> $business_name</p></br><p><b>Email:</b> $email</p></br></p></br><p><b>Phone:</b> $phone</p></br><p><b>Business Brief:</b> $business_brief</p></br><p><b>Business Type:</b> $type</p></br><h3>Thanks & Regards</h3></div></body></html>";
            $headers = ["Content-Type: text/html; charset=UTF-8"];
            wp_mail($to, $subject, $body, $headers);
            $res = [
                "status" => true,
                "data" => "Thank you for contacting us $name, a member of our team will be in touch with you shortly.",
            ];
            echo json_encode($res);
        }
        exit();
    }
}

/**
 * Manage bank response after payment success and failure.
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_manage_bank_response")) {
    function woocommerce_payzah_manage_bank_response()
    {   
        if (
            isset($_GET["payzah_callback"]) &&
            $_GET["payzah_callback"] == "payzah_response_handle"
        ) {
            if (
                sanitize_text_field($_REQUEST["trackId"]) != "" &&
                sanitize_text_field($_REQUEST["payzahRefrenceCode"]) != ""
            ) {
                global $woocommerce;

                $payzahClassObject = new WC_Payzah();

                $woocommerce->cart->empty_cart();

                $order_id = sanitize_text_field($_REQUEST["trackId"]);
                $order = new WC_Order($order_id);
                $status = sanitize_text_field($_REQUEST["paymentStatus"]);
                $status = strtoupper($status);

                $mailer = WC()
                    ->mailer()
                    ->get_emails();

                $order->update_meta_data(
                    "_payzah_response",
                    sanitize_text_field(serialize($_POST))
                );
                $order->update_meta_data(
                    "_transaction_id",
                    sanitize_text_field($_POST["transactionNumber"])
                );
                $order->update_meta_data(
                    "_payzah_transaction_type",
                    sanitize_text_field($_REQUEST["paymentMethod"])
                );

                $order->save();

                switch ($status) {
                    case "CAPTURED":
                        $msg =
                            "Payzah payment has been completed successfully!";
                        $order->update_status($payzahClassObject->order_status);
                        $mailer["WC_Email_New_Order"]->trigger($order_id);
                        $mailer["WC_Email_Customer_Completed_Order"]->trigger(
                            $order_id
                        );
                        break;
                    case "CANCELED":
                        $msg = "Payzah payment has been canceled!";
                        $order->update_status("cancelled");
                        $mailer["WC_Email_Cancelled_Order"]->trigger($order_id);
                        break;
                    case "VOIDED":
                        $msg = "Payzah payment has been voided!";
                        $order->update_status("on-hold");
                        $mailer["WC_Email_Customer_On_Hold_Order"]->trigger(
                            $order_id
                        );
                        break;
                    case "NOT CAPTURED":
                        $msg = "Payzah payment has not been captured!";
                        $order->update_status("failed");
                        $mailer["WC_Email_Failed_Order"]->trigger($order_id);
                        break;
                    case "DENIED BY RISK":
                        $msg = "Payzah payment has been denied by risk!";
                        $order->update_status("failed");
                        $mailer["WC_Email_Failed_Order"]->trigger($order_id);
                        break;
                    case "HOST TIMEOUT":
                        $msg = "Payzah payment has been timeout!";
                        $order->update_status("failed");
                        $mailer["WC_Email_Failed_Order"]->trigger($order_id);
                        break;
                    default:
                        $msg = "Payzah Payment - Unknown Error!";
                        $order->update_status("cancelled");
                        $mailer["WC_Email_Cancelled_Order"]->trigger($order_id);
                        break;
                }

                $order->add_order_note(
                    $msg .
                        ".<br/>Payzah RefrenceCode: " .
                        sanitize_text_field($_REQUEST["payzahRefrenceCode"]) .
                        "<br/>Tracking Number: " .
                        sanitize_text_field($_REQUEST["trackingNumber"]) .
                        "<br/>Payment ID: " .
                        sanitize_text_field($_REQUEST["paymentId"])
                );

                wp_redirect($order->get_checkout_order_received_url());

                exit();
            }
        }
    }
}

/**
 * Customize the title on the WooCommerce thank you page based on order status.
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_thankyou_title")) {
    function woocommerce_payzah_thankyou_title()
    {
        $order_id = wc_get_order_id_by_order_key($_GET["key"]);
        if ($order_id) {
            $order = wc_get_order($order_id);
            $payment_method = $order->get_payment_method();
            if ($payment_method == "payzah") {
                return $order->get_status() == "processing" ||
                    $order->get_status() == "completed"
                    ? "Your order is successfully placed."
                    : "Failure, Your order is not processed.";
            } else {
                return "Order received";
            }
        }
    }
}

/**
 * Customize the receive text on the WooCommerce thank you page based on order status.
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_receive_text")) {
    function woocommerce_payzah_receive_text($thank_you_title, $order)
    {
        $payment_method = $order->get_payment_method();
        if ($payment_method == "payzah") {
            return $order->get_status() == "processing" ||
                $order->get_status() == "completed"
                ? "Transaction is successful."
                : "Sorry, Your order has failed, please try again.";
        } else {
            return "Thank you. Your order has been received.";
        }
    }
}

/**
 * Customize order detail and show tracking number on the WooCommerce thank you page.
 *
 * @version 1.3
 */
if (!function_exists("woocommerce_payzah_tracking_number")) {
    function woocommerce_payzah_tracking_number($order_id)
    {
        $order = wc_get_order($order_id);
        $payment_method = $order->get_payment_method();
        if ($payment_method == "payzah") { ?>
			<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
				<li class="woocommerce-order-overview__total total">
				    <?php
        _e("Tracking ID:", "payzah");
        $order = wc_get_order($order_id);
        $order_data = unserialize($order->get_meta("_payzah_response"));
        ?>
				    <strong><?php echo $order_data["trackingNumber"]; ?></strong>
				</li>
			</ul>
			<?php }

        error_log("maybe_send_receipt_to_client is called for Order ID: " . $order_id);

        $send_receipt = get_option('woocommerce_enable_receipt');
    }
}

/**
 * Remove some fields from the WooCommerce checkout page.
 *
 * @version 1.3
 */
if (!function_exists("custom_payzan_override_checkout_fields")) {
    function custom_payzan_override_checkout_fields($fields)
    {
        $payzanPGSettingVariabe = new WC_Payzah();

        $billing_first_name =
            $payzanPGSettingVariabe->settings["billing_first_name"];
        $billing_last_name =
            $payzanPGSettingVariabe->settings["billing_last_name"];
        $billing_company = $payzanPGSettingVariabe->settings["billing_company"];
        $billing_address_1 =
            $payzanPGSettingVariabe->settings["billing_address_1"];
        $billing_address_2 =
            $payzanPGSettingVariabe->settings["billing_address_2"];
        $billing_city = $payzanPGSettingVariabe->settings["billing_city"];
        $billing_postcode =
            $payzanPGSettingVariabe->settings["billing_postcode"];
        $billing_country = $payzanPGSettingVariabe->settings["billing_country"];
        $billing_state = $payzanPGSettingVariabe->settings["billing_state"];
        $billing_phone = $payzanPGSettingVariabe->settings["billing_phone"];
        $billing_email = $payzanPGSettingVariabe->settings["billing_email"];

        $billing_fields = [
            "billing_first_name" => $billing_first_name,
            "billing_last_name" => $billing_last_name,
            "billing_company" => $billing_company,
            "billing_address_1" => $billing_address_1,
            "billing_address_2" => $billing_address_2,
            "billing_city" => $billing_city,
            "billing_postcode" => $billing_postcode,
            "billing_country" => $billing_country,
            "billing_state" => $billing_state,
            "billing_phone" => $billing_phone,
            "billing_email" => $billing_email,
        ];

        foreach ($billing_fields as $key => $field) {
            if ($field === "no") {
                unset($fields["billing"][$key]);
            }
        }

        return $fields;
    }
}

/**
 * Function to add order statuses for re-ordering.
 *
 * @version 1.3
 */
if (!function_exists("payzan_add_order_again_status")) {
    //Re-Order Function
    function payzan_add_order_again_status($array)
    {
        $array = array_merge($array, [
            "on-hold",
            "processing",
            "completed",
            "pending-payment",
            "cancelled",
            "failed",
            "refunded",
        ]);
        return $array;
    }
}

/**
 * Function to Change payment method value.
 *
 * @version 1.4
 */
if (!function_exists("payzah_payment_method_label_change")) {
function payzah_payment_method_label_change($total_rows, $order_items){
	$total_rows['payment_method']['value']= $total_rows['payment_type']['value'];
	return $total_rows;
}
}

function conditionally_manage_completed_order_email($recipient, $order) {
    // Check if the receipt email toggle is enabled
    $send_receipt = get_option('woocommerce_enable_receipt');

    // Log the current state
    error_log("Send receipt toggle status: " . $send_receipt);
    
    // If the toggle is set to 'no', prevent the email from being sent
    if ($send_receipt !== 'yes') {
        error_log("Receipt sending disabled, email not sent for Order ID: " . $order->get_id());
        return ''; // Return an empty string to prevent sending the email
    }

    // Return the recipient email (default behavior)
    return $recipient;
}

  