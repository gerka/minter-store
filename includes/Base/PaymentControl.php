<?php
use MinterStore\Exceptions\MinterStoreExceptions;
use MinterStore\Base\MinterController;
defined( 'ABSPATH' ) or exit;
// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}
/**
 * Add the gateway to WC Available Gateways
 *
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function wc_barter_add_to_gateways( $gateways ) {
    $gateways[] = 'WC_Gateway_Barter';
    return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_barter_add_to_gateways' );

/**
 * Adds plugin page links
 *
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
//function wc_offline_gateway_plugin_links( $links ) {
//    $plugin_links = array(
//        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=offline_gateway' ) . '">' . __( 'Configure', 'wc-gateway-barter' ) . '</a>'
//    );
//    return array_merge( $plugin_links, $links );
//}
//add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_offline_gateway_plugin_links' );
/**
 * Offline Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 */
add_action( 'plugins_loaded', 'wc_barter_gateway_init', 11 );

function wc_barter_gateway_init() {
    class WC_Gateway_Barter extends \WC_Payment_Gateway {
        public $minterControll;
        /**
         * Constructor for the gateway.
         */
        public function __construct() {
            $this->minterControll = new MinterController();
            $this->id                 = 'barter_gateway';
            $this->icon               = apply_filters('woocommerce_barter_icon', '');
            $this->has_fields         = false;
            $this->method_title       = __( 'Direct barter transfer', 'wc-gateway-barter' );
            $this->method_description = __( 'Take product in person via ' . \MinterStore\Base\MinterController::getBarterTokenName() . '. More commonly known as direct barter transfer', 'wc-gateway-barter' );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title        = $this->get_option( 'title' );
            $this->description  = $this->get_option( 'description' );
            $this->instructions = $this->get_option( 'instructions', $this->description );

            // Actions
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

            // Customer Emails
            add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
            add_action( 'woocommerce_order_details_before_order_table',[$this,'generateFormToPay']);
            add_filter('woocommerce_thankyou_order_received_text', [$this,'woo_change_order_received_text'], 10, 2 );


        }


        /**
         * Initialize Gateway Settings Form Fields
         */
        public function init_form_fields() {

            $this->form_fields = apply_filters( 'wc_barter_form_fields', array(

                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'wc-gateway-barter' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable Offline Payment', 'wc-gateway-barter' ),
                    'default' => 'yes'
                ),

                'title' => array(
                    'title'       => __( 'Title', 'wc-gateway-barter' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-barter' ),
                    'default'     => __( 'Offline Payment', 'wc-gateway-barter' ),
                    'desc_tip'    => true,
                ),

                'description' => array(
                    'title'       => __( 'Description', 'wc-gateway-barter' ),
                    'type'        => 'textarea',
                    'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-gateway-barter' ),
                    'default'     => __( 'Please remit payment to Store Name upon pickup or delivery.', 'wc-gateway-barter' ),
                    'desc_tip'    => true,
                ),

                'instructions' => array(
                    'title'       => __( 'Instructions', 'wc-gateway-barter' ),
                    'type'        => 'textarea',
                    'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-gateway-barter' ),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
            ) );
        }


        /**
         * Output for the order received page.
         */
        public function thankyou_page() {
            MinterStoreExceptions::Log($this->instructions);
//            if ( $this->instructions ) {
//                echo wpautop( wptexturize( $this->instructions ) );
//            }
        }


        /**
         * Add content to the WC emails.
         *
         * @access public
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @param bool $plain_text
         */
        public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

            if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
                echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
            }
        }


        /**
         * Process the payment and return the result
         *
         * @param int $order_id
         * @return array
         */
        public function process_payment( $order_id ) {

            $order = wc_get_order( $order_id );
            $order->update_meta_data( '_barter_price', $this->minterControll->getBarterPrice($order->get_total()) );
            $order->update_meta_data( '_barter_token', MinterController::getBarterTokenName());
            // Mark as on-hold (we're awaiting the payment)
            $order->update_status( MinterController::getUnpaidStatus(), __( 'Awaiting Barter', 'wc-gateway-barter' ) );
            //$order->add_meta_data('MinterPaySum',);
            // Reduce stock levels
            wc_reduce_stock_levels($order_id);

            // Remove cart
            WC()->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result' 	=> 'success',
                'redirect'	=> $this->get_return_url( $order )
            );
        }
        public function generateFormToPay(\WC_Order $order){
            $BarterPrice =  (!empty($order->get_meta('_barter_price',1)))?$order->get_meta('_barter_price',1):$this->minterControll->getBarterPrice($order->get_total());
            return '<table class="">
		<thead>
			<tr>
				<th class="">Резервирование бартерной сделкой</th>
			</tr>
		</thead>
		<tbody>
			<tr class="">
	<td class="" style="word-wrap: break-word;">
		 '.MinterController::getPublicAddressSiteWallet().'	</td>
	
</tr>
			<tr class="">
	<td class="" style="">
		 <img id="qr_img" src="http://chart.apis.google.com/chart?choe=UTF-8&amp;chld=H&amp;cht=qr&amp;chs=200x200&amp;chl='.MinterController::getPublicAddressSiteWallet().'" alt="">	</td>
	
</tr>
<tr><td class="">
		ИТОГО <span class="">'.$this->minterControll->getBarterPrice($order->get_total()).' '.MinterController::getBarterTokenName().'	</span></td></tr>
<tr><td class="">
		Статус <span id="status_order_pay" class="">'.$order->get_status().' </span></td></tr>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
	<scrypt>
	
</scrypt>
	';
        }
        public function woo_change_order_received_text($str, WC_Order $order ){
            if($order->get_payment_method() == $this->id   ){
                $new_str = $str .'Для оплаты вам необходимо перевести указанную сумму '.MinterController::getBarterTokenName().' на адрес магазина а в сообщении указать номер заказа. Заказ необходимо оплатить в течении 24 часов с момента создания в противном случае он отменяется'.$this->generateFormToPay($order);
            }else{$new_str = $str;}
            return $new_str;

        }

    } // end \WC_Gateway_Offline class
}
