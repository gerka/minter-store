<?php
/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\Base;


/**
 * Class Activate
 *
 * @package MinterStore\Base
 * Use that class method to do something when plugin activates
 */
class Activate extends BaseController
{
    public static function activate()
    {
        flush_rewrite_rules();
        KamaCron::activate('PaymentJobs');
        if(empty(get_option(self::getPluginName().'_text')) || empty(get_option(self::getPluginName().'_ticket'))){
            self::addDefaultSettings();
        }
    }
    //add default settings
    public static function addDefaultSettings(){
        $ManagerText = [
                'node_url'=>'https://api.minter.one',
                'public_address'=>'Your public Minter address which woocomerce use to get pay',
                'bip_price'=>20.15,
                'unpaid_status'=> 'on-hold',
                'paid_status'=> 'processing',
        ];
        $ManagerTicket =[
            'barter_ticket' => 'VALIDATOR',
            'utility_ticket' => 'MNTSHOP'
            //'hidden_already_started' => 'Hidden var that check starting script',
        ] ;
        update_option(self::getPluginName().'_text',$ManagerText);
        update_option(self::getPluginName().'_ticket',$ManagerTicket);

    }
}
