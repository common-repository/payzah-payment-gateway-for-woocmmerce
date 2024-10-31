<?php
//extend woocommerce class
if (!function_exists("woocommerce_payzah_init")) {
    function woocommerce_payzah_init()
    {
        class WC_Payzah extends WC_Payment_Gateway
        {
            //constructor for the gateway.
            public function __construct()
            {
                $this->id = "payzah";
                
                $this->has_fields = false;
                $this->method_title = __("Payzah Gateway", "payzah");
                $this->method_description = __(
                    "Pay securely with your debit or credit card)",
                    "payzah"
                );
            
                $this->supports = ["products", "refunds"];
                $this->hide_text_box = $this->get_option("hide_text_box");

                // Load the settings.
                $this->init_form_fields();
                $this->init_settings();

                if ($this->settings["payzah_order_redirection_urls"] === "direct_url") {
                    $this->icon = PAYZAH_IMAGE_DIR . "direct-payment-method-big.png";
                } else {
                    $this->icon = PAYZAH_IMAGE_DIR . "transit-payment-method-big.png";
                }

                $this->options = [];
                // Define "payment type" radio buttons options field
                if (
                    $this->settings["payzah_knet_option"] == "yes" &&
                    $this->settings["payzah_cc_option"] == "yes"
                ) {
                    $this->options = [
                        "1" => __("K-Net", "payzah"),
                        "2" => __("Credit Card", "payzah"),
                    ];
                }
                if (
                    $this->settings["payzah_knet_option"] == "yes" &&
                    $this->settings["payzah_cc_option"] == "no"
                ) {
                    $this->options = [
                        "1" => __("K-Net", "payzah"),
                    ];
                }
                if (
                    $this->settings["payzah_cc_option"] == "yes" &&
                    $this->settings["payzah_knet_option"] == "no"
                ) {
                    $this->options = [
                        "2" => __("Credit Card", "payzah"),
                    ];
                }

                if (!empty($this->settings["title"])) {
                    $title = $this->settings["title"];
                } else {
                    $title = __("change to Payzah Gateway", "payzah");
                }
                $this->title = $title;
                if ($this->settings["ui_language"] === "arabic") {
                    if($this->settings["payzah_order_redirection_urls"] === "direct_url") {
                        $this->description = $this->settings["description_in_arabic"].":";
                    }
                    else{
                        $this->description = $this->settings["description_in_arabic"];
                    }
                }
                else {
                    if($this->settings["payzah_order_redirection_urls"] === "direct_url") {
                        $this->description = $this->settings["description"].":";
                    }
                    else{
                        $this->description = $this->settings["description"];
                    }
                }
                $this->private_key = $this->settings["private_key"];
                $this->order_status =
                    $this->settings["payzah_order_defult_status"];

                /*Checkout field Variabe Start*/
                $this->billing_first_name =
                    $this->settings["billing_first_name"];
                $this->billing_last_name = $this->settings["billing_last_name"];
                $this->billing_company = $this->settings["billing_company"];
                $this->billing_address_1 = $this->settings["billing_address_1"];
                $this->billing_address_2 = $this->settings["billing_address_2"];
                $this->billing_city = $this->settings["billing_city"];
                $this->billing_postcode = $this->settings["billing_postcode"];
                $this->billing_country = $this->settings["billing_country"];
                $this->billing_state = $this->settings["billing_state"];
                $this->billing_phone = $this->settings["billing_phone"];
                $this->billing_email = $this->settings["billing_email"];
                /*Checkout field Variabe End*/

                if ($this->settings["testmode"] == "yes") {
                    $this->apiurl = PAYZAH_API_URL_TEST;
                    $this->refundapiurl = PAYZAH_REFUND_API_URL_TEST;
                } else {
                    $this->apiurl = PAYZAH_API_URL_PROD;
                    $this->refundapiurl = PAYZAH_REFUND_API_URL_PROD;
                }

                if (version_compare(WOOCOMMERCE_VERSION, "2.0.0", ">=")) {
                    /* 2.0.0 */
                    add_action(
                        "woocommerce_update_options_payment_gateways_" .
                            $this->id,
                        [&$this, "process_admin_options"]
                    );
                } else {
                    /* 1.6.6 */
                    add_action("woocommerce_update_options_payment_gateways", [
                        &$this,
                        "process_admin_options",
                    ]);
                }
            }

            function getMfDesWithIcon($desc, $icon = 'dashicons-info') {
                return '<font style="color:#0093c9;"><span class="dashicon dashicons ' . $icon . '"></span>' . $desc . '</font>';
            }

            //fields for bacend form
            function init_form_fields()
            {
                $this->form_fields = [
                    "enabled" => [
                        "title" => __("Enable/Disable", "payzah"),
                        "type" => "checkbox",
                        "label" => __(
                            "Enable Payzah Payment Module.",
                            "payzah"
                        ),
                        "default" => "no",
                        "description" =>
                            "Show in the Payment List as a payment option",
                    ],
                    "title" => [
                        "title" => __("Title:", "payzah"),
                        "type" => "text",
                        "default" => __("Payzah Payment Gateway", "payzah"),
                        "description" => __(
                            "This controls the title which the user sees during checkout.",
                            "payzah"
                        ),
                        "desc_tip" => true,
                        "custom_attributes" => array('readonly' => 'readonly'),
                    ],
                    "description" => [
                        "title" => __("Description:", "payzah"),
                        "type" => "textarea",
                        "default" => __(
                            "Check out securely with Payzah gateway",
                            "payzah"
                        ),
                        "description" => __(
                            "This controls the description which the user sees during checkout.",
                            "payzah"
                        ),
                        "desc_tip" => true,
                        //"custom_attributes" => array('readonly' => 'readonly'),
                    ],
                    "description_in_arabic" => [
                        "title" => __("Description in Arabic:", "payzah"),
                        "type" => "textarea",
                        "default" => __(
                            "اتمم عملية الشراء مع بوابة الدفع من بيزة للمدفوعات",
                            "payzah"
                        ),
                        "description" => __(
                            "This controls the description which the user sees during checkout.",
                            "payzah"
                        ),
                        "desc_tip" => true,
                        //"custom_attributes" => array('readonly' => 'readonly'),
                    ],
                    "private_key" => [
                        "title" => __("Private key", "payzah"),
                        "type" => "text",
                        "value" => "",
                        "description" => __(
                            "Get your Private key from Payzah.",
                            "woocommerce"
                        ),
                        "default" => "",
                        "desc_tip" => true,
                        "required" => true,
                    ],
                    "testmode" => [
                        "title" => __("TEST Mode", "payzah"),
                        "type" => "checkbox",
                        "label" => __(
                            "Enable Payzah TEST Transactions.",
                            "payzah"
                        ),
                        "default" => "no",
                        "description" => __(
                            "Tick to run TEST Transaction on the Payzah platform"
                        ),
                        "desc_tip" => true,
                    ],
                    "payzah_knet_option" => [
                        "title" => __("Enable Knet", "payzah"),
                        "type" => "checkbox",
                        "label" => __("Enable Knet Option.", "payzah"),
                        "default" => "yes",
                        "description" => __("Tick to enable knet option."),
                        "desc_tip" => true,
                    ],
                    "payzah_cc_option" => [
                        "title" => __("Enable Credit Card", "payzah"),
                        "type" => "checkbox",
                        "label" => __("Enable Credit Card Option.", "payzah"),
                        "default" => "yes",
                        "description" => __(
                            "Tick to enable credit card option."
                        ),
                        "desc_tip" => true,
                    ],
                    "ui_language" => [
                        "title" => __("Select Language", "payzah"),
                        "type" => "select",
                        "options" => [
                            "arabic" => __("Arabic", "payzah"),
                            "english" => __("English", "payzah"),
                        ],
                        "label" => __(
                            "Select gateway default language.",
                            "payzah"
                        ),
                        "default" => "english",
                        "description" => $this->getMfDesWithIcon(__("Select gateway default language.")),
                        //"desc_tip" => true,
                    ],
                    // "woocommerce_enable_receipt" => [
                    //     'title'    => __('Enable Receipt Email', 'payzah'),
                    //     'desc'     => __('Send receipt to the client after order', 'payzah'),
                    //     'id'       => 'woocommerce_enable_receipt',
                    //     'default'  => 'yes',
                    //     'type'     => 'checkbox',
                    //     'desc_tip' => true,
                    // ],

                    "payzah_order_redirection_urls" => [
                        "title" => __("Direction URL", "payzah"),
                        "type" => "select",
                        "options" => [
                            "transit_url" => __("Transit URL", "payzah"),
                            "direct_url" => __("Direct URL", "payzah"),
                        ],
                        "label" => __(
                            "Enable to defult Redirection URL.",
                            "payzah"
                        ),
                        "default" => "transit_url",
                        "description" => $this->getMfDesWithIcon(__("
                        Transit URL (Recommended) : Takes customers to all available payment menthods including Apple Pay option to let customers to choose their preferred payment method.<br>
                        Direct URL : Linking checkout directly to the payment page for each payment method (Apple pay not included).")),
                        //"desc_tip" => true,
                    ],

                    "payzah_order_defult_status" => [
                        "title" => __("Order Status", "payzah"),
                        "type" => "select",
                        "options" => [
                            "processing" => __("Processing", "payzah"),
                            "completed" => __("Completed", "payzah"),
                        ],
                        "label" => __(
                            "Enable to defult Order status.",
                            "payzah"
                        ),
                        "default" => "yes",
                        "description" => __("Select to defult Order status."),
                        "desc_tip" => true,
                    ],

                    /*Start the checkout field*/
                    "billing_first_name" => [
                        "title" => __("Enable checkout fields", "payzah"),
                        "description" =>
                            "Above checkbox Enable the First name on the checkout page.",
                        "label" => __("First Name", "payzah"),
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_last_name" => [
                        "label" => __("Last name", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Last name on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_email" => [
                        "label" => __("Email address", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Email address on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_company" => [
                        "label" => __("Company name", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Company name on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_address_1" => [
                        "label" => __("Street address", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Address 1 field on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_address_2" => [
                        "label" => __("Apartment, suite, unit, etc", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Address 2 field on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_city" => [
                        "label" => __("Town / City", "payzah"),
                        "description" =>
                            "Above checkbox Enable the City field on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_postcode" => [
                        "label" => __("Pin code", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Pincode field on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_country" => [
                        "label" => __("Country / Region", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Country / Region field on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_state" => [
                        "label" => __("State", "payzah"),
                        "description" =>
                            "Above checkbox Enable the State field on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    "billing_phone" => [
                        "label" => __("Phone", "payzah"),
                        "description" =>
                            "Above checkbox Enable the Phone field on the checkout page.",
                        "default" => "no",
                        "type" => "checkbox",
                    ],
                    /*End the checkout field*/
                ];
            }

            //output the "payment type" radio buttons fields in checkout.
            public function payment_fields()
            {
                //currency should be  Kuwaiti currency
                if (get_woocommerce_currency() == "KWD") {
                    if ($description = $this->get_description()) {
                        echo wpautop(wptexturize($description));
                    }
                    $option_keys = array_keys($this->options);

                    if (!empty($this->options)) {
                        $defaultChecked = false; // Flag to track if default option is found
                        if($this->settings["payzah_order_redirection_urls"] === "direct_url") {
                            foreach ($this->options as $id => $method) {
                                $paymentImage =
                                    $id == 1
                                        ? PAYZAH_IMAGE_DIR . "KNET.png"
                                        : ($id == 2
                                            ? PAYZAH_IMAGE_DIR . "master-card-visa-2.png"
                                            : "");
                        
                                // Check if the current option is KNET and mark it as default if found
                                if ($id == 1) {
                                    $defaultChecked = true;
                                }
                        
                                echo '<div class="payment-inner payment-div">
                                        <div class="title-div"> 
                                        <input type="radio" class="input-radio" value="' .
                                    $id .
                                    '" name="payzah_transaction_type" id="payzah_transaction_type_' .
                                    $id .
                                    '" ' . ($defaultChecked ? 'checked' : '') . '>
                                        <label class="payment-method-name" style="font-family: Helvetica, Arial, sans-serif;" for="payzah_transaction_type_' .
                                    $id .
                                    '">' .
                                    $method .
                                    '</label> </div><div class="image-div">
                                        <img for="payzah_transaction_type_' .
                                    $id .
                                    '" class="paymentimg" src="' .
                                    $paymentImage .
                                    '" alt=""> </div>
                                    </div>';
                        
                                // Reset the defaultChecked flag after the default option is found
                                if ($defaultChecked) {
                                    $defaultChecked = false;
                                }
                            }
                        }
                    } else {
                        echo "Payzah only supports Kuwaiti currency, default payment type is KNET";
                    }
                }
            }

            //when payment will process from front end
            public function process_payment($order_id)
            {
                $url = $this->apiurl;
                if (!empty($this->private_key)) {
                    $private_key = base64_encode($this->private_key);
                    $order = new WC_Order($order_id);
                    $order_data = $order->get_data();
                    $order_billing_full_name =
                        $order_data["billing"]["first_name"] .
                        " " .
                        $order_data["billing"]["last_name"];
                    $order_billing_postcode =
                        $order_data["billing"]["postcode"];
                    $order_billing_phone = $order_data["billing"]["phone"];
                    $order_billing_city = $order_data["billing"]["city"];
                    $lang = get_bloginfo("language");
                    $lang = "ENG";
                    if ($selected_lang == "ar") {
                        $lang = "ARA";
                    }
            
                    if($this->settings["payzah_order_redirection_urls"] === "transit_url") {
                        $payment_type = 3;
                    }
                    else {
                        if (
                            $_POST["payment_method"] === "payzah" &&
                            isset($_POST["payzah_transaction_type"])
                        ) {
                            $payment_type = sanitize_text_field(
                                $_POST["payzah_transaction_type"]
                            );
                        } else {
                            $payment_type = 1;
                        }    
                    }
                    
                    $data_string = [
                        "trackid" => $order_id,
                        "amount" => $order->order_total,
                        "success_url" => site_url(
                            "/?payzah_callback=payzah_response_handle"
                        ),
                        "error_url" => site_url(
                            "/?payzah_callback=payzah_response_handle"
                        ),
                        "language" => $lang,
                        "currency" => "414",
                        "udf1" => $order_billing_full_name,
                        "udf2" => $order_billing_phone,
                        "udf3" => $order_billing_postcode,
                        "udf4" => $order_billing_city,
                        "payment_type" => $payment_type,
                    ];
                 
                    $data_string = json_encode($data_string);

                    $headers = [
                        "Authorization" => $private_key,
                        "Content-type" => "application/json",
                    ];
                    $post_data = [
                        "method" => "POST",
                        "timeout" => 30,
                        "headers" => $headers,
                        "body" => $data_string,
                    ];
                    $response = wp_remote_post($url, $post_data);
                    $api_response = json_decode($response["body"]);

                    if ($api_response->status == 1) {
                        if($this->settings["payzah_order_redirection_urls"] === "direct_url") {
                            $api_redirect_url = $api_response->data->direct_url;
                        }
                        else {
                            $api_redirect_url = $api_response->data->transit_url;
                        }    
                        $spi_id = $api_response->data->PaymentID;
                        $redirect_url_for_payment =
                            $this->payzah_p_success_page == ""
                                ? $api_redirect_url
                                : $this->payzah_p_success_page;

                        return [
                            "result" => "success",
                            "redirect" => $redirect_url_for_payment,
                        ];
                    }

                    if ($api_response->status != 1) {
                        return [
                            "result" => "error",
                            "redirect" => $this->payzah_p_fail_page,
                        ];
                        wc_add_notice($api_response->message, "error");
                    }
                } else {
                    wc_add_notice(
                        "Technical error has been occured in Pazah, please contact to site owner",
                        "error"
                    );
                }
            }

            //refund process
            public function process_refund(
                $order_id,
                $amount = null,
                $reason = ""
            ) {
                $refundapiurl = $this->refundapiurl;
                $private_key = base64_encode($this->private_key);
                $payzah_response = unserialize(
                    get_post_meta($order_id, "_payzah_response", true)
                );
                $order = new WC_Order($order_id);
                $order_total = $order->get_total();
                $refund_amount = sanitize_text_field($_POST["refund_amount"]);
                $refund_type = $order_total == $refund_amount ? 1 : 2;
                $refund_reason = !empty($_POST["refund_reason"])
                    ? sanitize_text_field($_POST["refund_reason"])
                    : "";
                $refund_data = [
                    "trackid" => $order_id,
                    "refrence_code" => $payzah_response["payzahRefrenceCode"],
                    "amount" => $refund_amount,
                    "refund_type" => $refund_type,
                    "message" => $refund_reason,
                ];
                $refund_data = json_encode($refund_data);
                $headers = [
                    "Authorization" => $private_key,
                    "Content-type" => "application/json",
                ];
                $post_data = [
                    "method" => "POST",
                    "timeout" => 30,
                    "headers" => $headers,
                    "body" => $refund_data,
                ];
                $refund_response = wp_remote_post($refundapiurl, $post_data);
                $refund_api_response = json_decode($refund_response["body"]);
                if ($refund_api_response->status == 1) {
                    update_post_meta(
                        $order_id,
                        "_payzah_refund_response",
                        sanitize_text_field(serialize($_POST)),
                        true
                    );
                    wp_update_post([
                        "ID" => $order_id,
                        "post_status" => "wc-refunded",
                    ]);
                    $order->add_order_note("Refunded Successfully.");
                }
                if ($refund_api_response->status != 1) {
                    return new WP_Error(
                        "error",
                        __($refund_api_response->message, "woocommerce")
                    );
                }
                return true;
            }
        }
    }
}