<?php
/**
 * Created by PhpStorm.
 * User: gerk
 * Date: 17.09.18
 * Time: 15:43
 */
/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\Base;
use \MinterStore\Base\BaseController;

/**
 * Class Enqueue
 * Use this class to connect scripts js css scripts
 * @package MinterStore\Base
 */
class Enqueue extends BaseController
{
    public function register(){
        add_action('admin_enqueue_scripts', [$this,'admin_enqueue']);
        add_action( 'template_redirect', [$this,'public_enqueue'] );
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_enqueue' ) );

    }
    public function admin_enqueue(){
        wp_enqueue_style(self::getPluginName().'-style',self::getPluginUrl().'assets/mystyle.css');
        wp_enqueue_script(self::getPluginName().'-script-admin',self::getPluginUrl().'assets/minter-store-admin.js');

    }
    public function public_enqueue(){

    }
    public function payment_enqueue(){
// We need custom JavaScript to obtain a token

        // we need JavaScript to process a token only on cart/checkout pages, right?
        if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
            return;
        }

        // if our payment gateway is disabled, we do not have to enqueue JS too
        if ( 'no' === $this->enabled ) {
            return;
        }

        // no reason to enqueue JavaScript if API keys are not set
        if ( empty( $this->private_key ) || empty( $this->publishable_key ) ) {
            return;
        }

        // do not work with card detailes without SSL unless your website is in a test mode
        if ( ! $this->testmode && ! is_ssl() ) {
            return;
        }

//        // and this is our custom JS in your plugin directory that works with token.js
//        wp_register_script( 'woocommerce_minter-store', plugins_url( 'minter-store.js', __FILE__ ), array( 'jquery', 'minter-store_js' ) );
//
//        wp_localize_script( 'woocommerce_minter-store', 'minter-store_params', array(
//        ) );
//
//        wp_enqueue_script( 'woocommerce_minter-store' );
    }
}