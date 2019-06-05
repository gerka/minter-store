<?php
/**
 * Created by PhpStorm.
 * User: gerk
 * Date: 17.09.18
 * Time: 16:32
 */
/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\Base;

/**
 * Class SettingsLinks
 * Use this class to manage links in the wordpress plugin directory
 * @package MinterStore\Base
 */
class SettingsLinks extends BaseController
{
    public function register(){
        add_filter("plugin_action_links_". self::getPluginHookName(),array($this,'settings_link'));
    }
    public function settings_link($links){
        $settings_link = '<a href="admin.php?page='.self::getPluginName().'">Open plugin</a>';
        array_push($links,$settings_link);
        self::Log($links);
        return $links;
    }
}