<?php
/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\Base;


/**
 * Class Deactivate
 * @package MinterStore\Base
 * Use this class to hook deactivate plugin maybe for flush database
 */
class Deactivate extends BaseController
{
    public static function deactivate(){
        flush_rewrite_rules();
    }
}
