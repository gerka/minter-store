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
    }
    public function admin_enqueue(){
        wp_enqueue_style(self::getPluginName().'-style',self::getPluginUrl().'assets/mystyle.css');
    }
    public function public_enqueue(){

    }
}