<?php

/* Authorize.net AIM Payment Gateway Class */
class WC_Cheetahpay extends WC_Payment_Gateway {

	private $endpoint = DEV ? 'http://localhost/dummy/api/' : 'https://cheetahpay.com/api/v2/';
	
	// Setup our Gateway's id, description and other values
	function __construct() 
	{
		// The global ID for this Payment method
		$this->id = "wc_cheetahpay";

		// The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		$this->method_title = 'Cheetahpay';

		// The description for this Payment Gateway, shown on the actual Payment options page on the backend
		$this->method_description = "Cheetahpay: A Payment Gateway Plug-in for WooCommerce";

		// The title to be used for the vertical tabs that can be ordered top to bottom
		$this->title = "Cheetahpay";

		// If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		$this->icon = 'http://cheetahpay.com/favicon.ico';

		// Bool. Can be set to true if you want payment fields to show on the checkout 
		// if doing a direct integration, which we are doing in this case
		$this->has_fields = true;

		// Supports the default credit card form
		$this->supports = array( 'default_credit_card_form' );

		// This basically defines your settings which are then loaded with init_settings()
		$this->init_form_fields();

		// After init_settings() is called, you can get the settings and load them into variables, e.g:
		// $this->title = $this->get_option( 'title' );
		$this->init_settings();
		
		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		// Lets check for SSL
		add_action( 'admin_notices', array( $this,	'do_ssl_check' ) );
		
		// Cheetahpay Callback Handler
		add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'cheetahpayCallbackHandler'));
		
		// Save settings
		if ( is_admin() ) {
			// Versions over 2.0
			// Save our administration options. Since we are not going to be doing anything special
			// we have not defined 'process_admin_options' in this class so the method in the parent
			// class will be used instead
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}		
	} // End __construct()

	// Build the administration fields for this specific Gateway
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Enable / Disable', 'spyr-authorizenet-aim' ),
				'label'		=> __( 'Enable this payment gateway', 'spyr-authorizenet-aim' ),
				'type'		=> 'checkbox',
				'default'	=> 'No',
			),
			'title' => array(
				'title'		=> __( 'Pay with Airtime: powered by Cheetahpay', 'spyr-authorizenet-aim' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Payment title the customer will see during the checkout process.', 'spyr-authorizenet-aim' ),
				'default'	=> __( 'Pay with Airtime', 'spyr-authorizenet-aim' ),
			),
			'description' => array(
				'title'		=> __( 'Description', 'spyr-authorizenet-aim' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Payment description the customer will see during the checkout process.', 'spyr-authorizenet-aim' ),
				'default'	=> __( 'You can pay by entering your airtime pin or via share & sell', 'spyr-authorizenet-aim' ),
				'css'		=> 'max-width:350px;'
			),
			'public_key' => array(
				'title'		=> __( 'Your Cheetahpay public key', 'spyr-authorizenet-aim' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Login to your Cheetahpay account to get your Public key', 'spyr-authorizenet-aim' ),
			),
			'private_key' => array(
				'title'		=> __( 'Your Cheetahpay private key', 'spyr-authorizenet-aim' ),
				'type'		=> 'password',
				'desc_tip'	=> __( 'Also get your private key from your Cheetahpay account. Keep it secret', 'spyr-authorizenet-aim' ),
			),
			'phoneNo' => array(
				'title'		=> 'My Phone No',
				'type'		=> 'text',
				'desc_tip'	=> 'Reachable mobile number',
			),
			/*
			'environment' => array(
				'title'		=> __( 'Authorize.net Test Mode', 'spyr-authorizenet-aim' ),
				'label'		=> __( 'Enable Test Mode', 'spyr-authorizenet-aim' ),
				'type'		=> 'checkbox',
				'description' => __( 'Place the payment gateway in test mode.', 'spyr-authorizenet-aim' ),
				'default'	=> 'no',
			)
			*/
		);		
	}
	
	// Submit payment and handle response
	public function process_payment( $order_id ) {
		global $woocommerce;
// 		wc_add_notice('Payment Error: We are still testing', 'error');
// 		dlog($_POST);
// 		return;
		// Get this Order's information so that we know
		// who to charge and how much
		$customer_order = new WC_Order( $order_id );
				
		// Are we testing right now or is it a real transaction
		$environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';

		// Decide which URL to post to
// 		$environment_url = $this->endpoint;
		


		// This is where the fun stuff begins
		$payload = array(
			
			// Cheetahpay Credentials 
		    "public_key"           	=> $this->publicKey, 
		    "private_key"             => $this->privateKey, 
			"version"             => "1.0",
		    
			// Order total
			"order_price"       => $customer_order->get_total(),
			"order_id"            => $order_id,

		    
			"invoice_num"        	=> str_replace( "#", "", $customer_order->get_order_number() ),
			"test_request"       	=> $environment,
			
			// Billing Information
			"first_name"         	=> $customer_order->billing_first_name,
			"last_name"          	=> $customer_order->billing_last_name,
			"address"            	=> $customer_order->billing_address_1,
			"city"              	=> $customer_order->billing_city,
			"state"              	=> $customer_order->billing_state,
			"zip"                	=> $customer_order->billing_postcode,
			"country"            	=> $customer_order->billing_country,
			"phone"              	=> $customer_order->billing_phone,
			"email"              	=> $customer_order->billing_email,
			
			// Shipping Information
			"ship_to_first_name" 	=> $customer_order->shipping_first_name,
			"ship_to_last_name"  	=> $customer_order->shipping_last_name,
			"ship_to_company"    	=> $customer_order->shipping_company,
			"ship_to_address"    	=> $customer_order->shipping_address_1,
			"ship_to_city"       	=> $customer_order->shipping_city,
			"ship_to_country"    	=> $customer_order->shipping_country,
			"ship_to_state"      	=> $customer_order->shipping_state,
			"ship_to_zip"        	=> $customer_order->shipping_postcode,
			
			// Some Customer Information
			"cust_id"            	=> $customer_order->user_id,
			"customer_ip"        	=> $_SERVER['REMOTE_ADDR'],
		);
	   
		// init Zoranga api request file
		include_once __DIR__ . '/inc/CheetahPay.php';
		
		$z = new CheetahPay($this->privateKey, $this->publicKey, $this->endpoint);
		
		$ret = [];
		
		$phone = empty($customer_order->billing_phone) ? $this->phoneNo : $customer_order->billing_phone;
		
		if(array_get($_POST, 'cheetah_object') == 'pinDeposit')
		{
		    $ret = $z->pinDeposit($_POST['pin'],$_POST['pin_amount'], $_POST['pin_network'], $order_id, $phone);
		}
		elseif (array_get($_POST, 'cheetah_object') == 'airtimeTransfer')
		{
		    $ret = $z->airtimeTransfer($_POST['transfer_amount'], $_POST['transfer_network'], $_POST['transfer_phone'], $order_id);
		}
		else
		{
		    wc_add_notice('Payment Error: ', 'Invalid object supplied' );
		    
		    return;
		}
		
		// Test the code to know if the transaction went through or not.
		if(!array_get($ret, 'success'))
		{
		    $networkErrorMsg = 'Possible error from the network, Please try again later';
		    
		    $networkErrorMsg = 'Error: '. array_get($ret, 'message', $networkErrorMsg);
		    
		    // Transaction was not succesful
		    // Add notice to the cart
		    wc_add_notice($networkErrorMsg, 'error');
		    
		    // Add note to the order for your reference
// 		    $customer_order->add_order_note($networkErrorMsg);
		    
		    return;
		}
		
		// Airtime received, awaiting confirmation
		$customer_order->update_status('on-hold', 'Payment received, awaiting confirmation');
				
		// Empty the cart (Very important step)
		$woocommerce->cart->empty_cart();
		
		// Redirect to thank you page
		return array(
		    'result'   => 'success',
		    'redirect' => $this->get_return_url($customer_order),
		);
	}
	
	public function payment_fields()
	{
	    global $woocommerce;
	    
		if ( $description = $this->get_description() ) {
			echo wpautop( wptexturize( $description ) );
		}
		
		$actualPrice = $woocommerce->cart->total;
		
        include_once 'inc/payment-field.php';
	}
		
	// Validate fields
	public function validate_fields() {
	    
	    $object = array_get($_POST, 'cheetah_object');
	    
	    $error = false;
	    if($object == 'pinDeposit')
	    {
	        $pin = array_get($_POST, 'pin');
	        $amount = array_get($_POST, 'pin_amount');
	        $network = array_get($_POST, 'pin_network');
	        
	        if(!$pin || empty($pin)){
	            wc_add_notice('Pin is required', 'error');
	            $error = true;
	        }
	        if(!$amount || empty($amount)){
	            wc_add_notice('Enter amount of the airtime pin', 'error');
	            $error = true;
	        }
	        if(!$network || empty($network)){
	            wc_add_notice('Select a network', 'error');
	            $error = true;
	        }
	        return false;
	    }
	    elseif($object == 'airtimeTransfer')
	    {
	        $phone = array_get($_POST, 'transfer_phone');
	        $amount = array_get($_POST, 'transfer_amount');
	        $network = array_get($_POST, 'transfer_network');
	        
	        if(!$phone || empty($phone)){
	            wc_add_notice('Enter the transferring phone number', 'error');
	            $error = true;
	        }
	        if(!$amount || empty($amount)){
	            wc_add_notice('Enter the amount you are transferring', 'error');
	            $error = true;
	        }
	        if(!$network || empty($network)){
	            wc_add_notice('Select a network', 'error');
	            $error = true;
	        }
	    }
	    else 
	    {
	        wc_add_notice('Object not specified', 'error');
	        
	        return false;
	    }
	    
	    if($error) return false;
	    
		return true;
	}
	
	// Check if we are forcing SSL on checkout pages
	// Custom function not required by the Gateway
	public function do_ssl_check() {
		if( $this->enabled == "yes" ) {
			if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
				echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";	
			}
		}		
	}
	
	public function cheetahpayCallbackHandler() 
	{
	    // http://localhost/wp/?wc-api=wc_cheetah
	    global $woocommerce;
	    
	    $ret = (is_array($_REQUEST)) ? $_REQUEST : json_decode($_REQUEST, true);
	    
	    $privateKey = array_get($ret, 'private_key'); 
	    $publicKey = array_get($ret, 'public_key'); 
	    
	    if($this->privateKey != $privateKey || $this->publicKey != $publicKey)
	    {
	        die(json_encode(['status' => 'error', 'message' => 'Invalid credentials']));
	        // The IP address that made this call should be blocked
	        return;
	    }
	    
	    $status = array_get($ret, 'status');
	    
	    $order_id = array_get($ret, 'order_id');
	    
        try {
    	    $customer_order = new WC_Order($order_id);
        } 
        catch (Exception $e) 
        { 
            die(json_encode(['status' => 'error', 'message' => $e->getMessage()])); 
        }
	    
        $amount = array_get($ret, 'amount', 0);
        
        $orderAmount = $customer_order->get_total();
        
        $orderStatus = $customer_order->get_status();
        
        if($orderStatus != 'on-hold')
        {
            die(json_encode(['status' => 'error', 'message' => 'Order status not "on-hold"']));
            
            return;
        }
        
	    if($status == 'credited')
	    {
	        if($orderAmount > $amount)
	        {
                // Add note to the order for your reference
                $customer_order->add_order_note("User made a payment of {$customer_order->get_currency()}$amount "
                    . " instead of {$customer_order->get_currency()}$orderAmount");
                
                die(json_encode(['status' => 'success', 'message' => 'Received but payment incomplete']));
                
                return;
	        }
	        
	        // Payment has been successful
	        $customer_order->add_order_note('Payment completed.');
	        
	        // Mark order as Paid
	        $customer_order->payment_complete();
	        
	        die(json_encode(['status' => 'success', 'message' => 'Payment received and order processed successfully']));
	        
	        return;
	    }
        // For $status == 'DELETED_USED_INVALID' or DEPOSIT_INVALID
	    elseif ($status != 'pending')
	    {
	        $customer_order->add_order_note('Payment invalid, therefore order cancelled');
	        
	        $customer_order->cancel_order();
	        
	        die(json_encode(['status' => 'error', 'message' => 'Order cancelled']));
	        
	        return;
	    }
	    
	    die(json_encode(['status' => 'error', 'message' => 'Invalid message status supplied']));
	}

} // End of SPYR_AuthorizeNet_AIM

function dlog($msg) {
    $str = '';
    
    if (is_array($msg)) $str = json_encode($msg, JSON_PRETTY_PRINT);
    
    else $str = $msg;
    
    error_log(
        '*************************************' . PHP_EOL .
        '     Date Time: ' . date('Y-m-d h:m:s') . PHP_EOL .
        '------------------------------------' . PHP_EOL .
        $str . PHP_EOL . PHP_EOL .
        '*************************************' . PHP_EOL,
        
        3, __DIR__ . '/errorlog.txt');
}
function array_get($post, $key, $default = NULL) 
{
    return (isset($post[$key]) ? $post[$key] : $default);
}