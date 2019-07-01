<?php
/**
 * Created by PhpStorm.
 * User: devacc
 * Date: 06/06/2019
 * Time: 18:09
 */

namespace MinterStore\Base;
use Minter\SDK\MinterPrefix;
use Minter\SDK\MinterConverter;
use Minter\MinterAPI;
use MinterStore\Exceptions\MinterStoreExceptions;

class MinterController extends BaseController
{
    public $PaymentGate;

    public static $BipPrice; //in rubles
    public static $nodeURL;
    public static $UnpaidStatus;
    public static $PaidStatus;
    public static $PublicAddress;
    public $api;
    public function register(){
        // Hooking up our function to theme setup
        self::setNodeURL();
        self::setBipPrice(20.15);
        self::setUnpaidStatus('on-hold');
        self::setPaidStatus('processing');
        self::setPublicAddress();
        add_action( 'init', [$this,'create_minter_post_type'] );
       // add_action('admin_footer-'.self::getPluginName(),[$this,'printStats'] );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [$this,'barter_price_checkout_field_display_admin_order_meta'], 10, 1 );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [$this,'barter_token_checkout_field_display_admin_order_meta'], 10, 1 );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [$this,'barter_payment_hash_checkout_field_display_admin_order_meta'], 10, 1 );

        // Hooking up our function to theme setup
    }
    public function barter_price_checkout_field_display_admin_order_meta(\WC_Order $order ){
        echo '<p><strong>'.__('Barter price').':</strong> ' .$order->get_meta('_barter_price',true) . '</p>';
    }

    public function barter_token_checkout_field_display_admin_order_meta(\WC_Order $order ){
        echo '<p><strong>'.__('Barter token').':</strong> ' .$order->get_meta('_barter_token',true) . '</p>';
    }
    public function barter_payment_hash_checkout_field_display_admin_order_meta(\WC_Order $order ){
        echo '<p><strong>'.__(' barter payment hash').':</strong> ' .$order->get_meta('_barter_payment_hash',true) . '</p>';
    }

    /**
     *  @param bool $withPrefix
     * @return mixed
     */
    public static function getPublicAddress($withPrefix = false)
    {
        if($withPrefix){
            return self::$PublicAddress;
        }
        $pieces = explode(MinterPrefix::ADDRESS, self::$PublicAddress);

        return $pieces[1];
    }

    /**
     * @param mixed $PublicAddress
     */
    public static function setPublicAddress($PublicAddress = false)
    {
        $TextFieldsFromBase = get_option(self::getPluginName().'_text');
        if(isset($TextFieldsFromBase['public_address'])&& !empty($TextFieldsFromBase['public_address'])){
            $PublicAddress = $TextFieldsFromBase['public_address'];
        }
        self::$PublicAddress = $PublicAddress;
    }


    /**
     * @return mixed
     */
    public static function getUnpaidStatus()
    {
        return self::$UnpaidStatus;
    }

    /**
     * @param mixed $UnpaidStatus
     */
    public static function setUnpaidStatus($UnpaidStatus)
    {
        $TextFieldsFromBase = get_option(self::getPluginName().'_text');
        if(isset($TextFieldsFromBase['unpaid_status'])&& !empty($TextFieldsFromBase['unpaid_status'])){
            $UnpaidStatus = $TextFieldsFromBase['unpaid_status'];
        }
        else{
            $UnpaidStatus = 'pending';
        }
        self::$UnpaidStatus = $UnpaidStatus;
    }

    /**
     * @return mixed
     */
    public static function getPaidStatus()
    {
        return self::$PaidStatus;
    }

    /**
     * @param mixed $PaidStatus
     */
    public static function setPaidStatus($PaidStatus)
    {
        $TextFieldsFromBase = get_option(self::getPluginName().'_text');
        if(isset($TextFieldsFromBase['paid_status'])&& !empty($TextFieldsFromBase['paid_status'])){
            $PaidStatus = $TextFieldsFromBase['paid_status'];
        }
        else{
            $PaidStatus = 'processing';
        }
        self::$PaidStatus = $PaidStatus;
    }


    /**
     * @return string
     */
    public static function getNodeURL(): string
    {

        return self::$nodeURL;
    }

    /**
     * @param string $nodeURL
     */
    public static function setNodeURL(string $nodeURL = '')
    {
        $TextFieldsFromBase = get_option(self::getPluginName().'_text');
        if(isset($TextFieldsFromBase['node_url'])&& !empty($TextFieldsFromBase['node_url'])){
            $nodeURL = $TextFieldsFromBase['node_url'];
        }
        else{
            $nodeURL = 'http://95.216.193.161:8841';
        }

        self::$nodeURL = $nodeURL;
    }


    public function printStats(){

        $publicAddress = self::getPublicAddress();
            MinterStoreExceptions::Log($publicAddress);

        return $publicAddress;


    }
    public static function getPublicAddressSiteWallet(){

        $publicAddress =self::getPublicAddress(1);
            MinterStoreExceptions::Log($publicAddress);
        return $publicAddress;

    }


    // Our custom post type function
    function create_minter_post_type() {
// Set UI labels for Custom Post Type
        $labels = array(
            'name'                => _x( 'Minter Transactions', 'MinterTransactions', 'MinterTransactions' ),
            'singular_name'       => _x( 'Minter Transaction', 'Minter Transaction', 'MinterTransactions' ),
            'menu_name'           => __( 'Minter Transactions', 'MinterTransactions' ),
            'all_items'           => __( 'All Transactions', 'MinterTransactions' ),
            'view_item'           => __( 'View Transaction', 'MinterTransactions' ),
            'add_new_item'        => __( 'Add New Transaction', 'MinterTransactions' ),
            'add_new'             => __( 'Add New', 'MinterTransactions' ),
            'edit_item'           => __( 'Edit Transaction', 'MinterTransactions' ),
            'update_item'         => __( 'Update Transaction', 'MinterTransactions' ),
            'search_items'        => __( 'Search Transaction', 'MinterTransactions' ),
            'not_found'           => __( 'Not Found', 'MinterTransactions' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'MinterTransactions' ),
        );

// Set other options for Custom Post Type

        $args = array(
            'label'               => __( 'minter_transaction' ),
            'description'         => __( 'Alll minter transactions here', 'MinterTransactions' ),
            'labels'              => $labels,
            // Features this CPT supports in Post Editor
            'supports'            => array( 'title', 'excerpt', 'author', 'custom-fields', ),
            // You can associate this CPT with a taxonomy or custom taxonomy.
            // 'taxonomies'          => array( 'genres' ),
            /* A hierarchical CPT is like Pages and can have
            * Parent and child items. A non-hierarchical CPT
            * is like Posts.
            */
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
        );

        // Registering your Custom Post Type
        register_post_type( 'minter_transaction', $args );
    }
    public static function getBarterTokenName(){
        $tokens = get_option(self::getPluginName().'_ticket');
        return $tokens['barter_ticket'];
    }
    public static function getUtilityTokenName(){
        $tokens = get_option(self::getPluginName().'_ticket');
        return $tokens['utility_ticket'];
    }
    public static function getBipPrice($priceInRubles){
      return   $priceInRubles/self::$BipPrice;
    }
    public static function setBipPrice(float $bipPrice){
        $TextFieldsFromBase = get_option(self::getPluginName().'_text');
        if(isset($TextFieldsFromBase['bip_price'])&& !empty($TextFieldsFromBase['bip_price'])){
            $bipPrice = $TextFieldsFromBase['bip_price'];
        }

        self::$BipPrice = $bipPrice;
    }
    public function getBarterPrice($priceInRubles){
        $bipPrice = (float) self::getBipPrice($priceInRubles);
        $bipPrice =  MinterConverter::convertValue($bipPrice,'pip');
        $api = new MinterAPI(self::getNodeURL());
        $EstimateTrans = $api->estimateCoinSell('BIP',$bipPrice,self::getBarterTokenName());
        MinterStoreExceptions::Log( (array)$EstimateTrans->result->will_get);
        $BarterPrice =  MinterConverter::convertValue($EstimateTrans->result->will_get,'bip');
        return round($BarterPrice, 2);

    }

}