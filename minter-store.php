<?php

/*
Plugin Name: Minter Store
Plugin URI: http://minterthestore.online
Description: This plugin can provide functions to work with woocomerce to simple integration minter custom asset.
Version: 1.0
Author: gerka23@protonmail.com
Author URI: http://mntstore.ru
License: GPL2
*/

use MinterStore\Base\Activate;
use MinterStore\Base\Deactivate;
use MinterStore\Exceptions\MinterStoreExceptions;

defined('ABSPATH') or die('HEY, what are you doing here!?');
try{
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//Activate Plugin
    function activate_minter_store(){
        Activate::activate();
    }
    register_activation_hook(__FILE__,'activate_minter_store');

//Deactivate Plugin
    function deactivate_minter_store(){
        Deactivate::deactivate();
    }


    register_deactivation_hook(__FILE__,'deactivate_minter_store');

    if (class_exists('MinterStore\\Init')){
        MinterStore\Init::register_services();
    }else {
        throw new MinterStoreExceptions('class Includes\Init is not exist');
    }
}catch (\MinterStore\Exceptions\MinterStoreExceptions $e){
    Logger()->error( $e->getMessage() );
}
