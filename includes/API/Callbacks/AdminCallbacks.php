<?php
/**
 * Created by PhpStorm.
 * User: gerk
 * Date: 18.09.18
 * Time: 12:42
 */
/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\API\Callbacks;
use MinterStore\Base\BaseController;

class AdminCallbacks extends BaseController
{

    public function adminDashboard(){
        require_once (self::getPluginPath().'/templates/admin.php');
    }

    public static function set_screen( $status, $option, $value ) {
        return $value;
    }
}