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
    }
}
