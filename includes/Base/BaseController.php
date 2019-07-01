<?php
/**
* Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */

namespace MinterStore\Base;


class BaseController
{
    private static $plugin_path;
    private static $plugin_name;
    private static $plugin_url;
    private static $pluginHookName;
    private static $debug ;
    protected static $WpNotices = 0;
    protected static $dbS;

    protected $db;
    protected $managers;
    protected $managerTicket;
    protected $managerText;
    protected $settings_plugin ;

    public function __construct()
    {
        self::setPluginName(plugin_basename(dirname(__FILE__,3)));
        self::setPluginPath(plugin_dir_path(dirname(__FILE__,2)));
        self::setPluginUrl(plugin_dir_url(dirname(__FILE__,2)));
        self::setPluginHookName(self::getPluginName().'/'.self::getPluginName().'.php');
        $this->setDb();
        self::setDbS($this->db);
        $this->setSettingsPlugin(get_option(self::getPluginName()));
        self::setDebug($this->settings_plugin);
        // add some fields to manage that bools here
        $this->setManagers([
             'debug' => 'Turn on debug to php console'
            //'hidden_already_started' => 'Hidden var that check starting script',
        ]);//if specify hidden in $key inside array of $this->>manager it was hidennig out from user
        $this->setManagerTicket([
             'barter_ticket' => 'Ticket which you use for barter',
             'utility_ticket' => 'Ticket which you use for utilities'
            //'hidden_already_started' => 'Hidden var that check starting script',
        ]);//if specify hidden in $key inside array of $this->>manager it was hidennig out from user
        $this->setManagerText([
            'node_url'=>'Node API URL',
            'public_address'=>'Your public Minter address which woocomerce use to get pay',
            'bip_price'=>'Price for 1 BIP in LOCAL currency',
            'unpaid_status'=> 'Status for woocomerce for unpaid orders',
            'paid_status'=> 'Status for woocomerce for paid orders',

        ]);
    }

    /**
     * @return mixed
     */
    public function getManagerText()
    {
        return $this->managerText;
    }

    /**
     * @param mixed $managerText
     */
    public function setManagerText($managerText)
    {
        $this->managerText = $managerText;
    }

    /**
     * @param int $debug
     */
    public static function setDebug($settings)
    {
        if(!empty($settings) && isset($settings['debug'])){
            self::$debug = $settings['debug'];
        }else{
            self::$debug = false;
        }
    }

    /**
     * @param mixed $pluginHookName
     */
    public static function setPluginHookName($pluginHookName)
    {
        self::$pluginHookName = $pluginHookName;
    }

    /**
     * @return mixed
     */
    public static function getPluginHookName()
    {
        return self::$pluginHookName;
    }


    /**
     * @param mixed $dbS
     */
    public static function setDbS($dbS)
    {
        self::$dbS = $dbS;
    }


    /**
     * @return mixed
     */
    public function getSettingsPlugin()
    {
        return $this->settings_plugin;
    }

    /**
     * @param mixed $settings_plugin
     */
    public function setSettingsPlugin($settings_plugin)
    {
        if(empty($this->settings_plugin)){
            $this->settings_plugin = $settings_plugin;
        }
        return $this->settings_plugin;
    }



    /**
     * @return array
     */
    public function getManagers(): array
    {
        return $this->managers;
    }


    /**
     * @return array
     */
    public function getManagerTicket(): array
    {
        return $this->managerTicket;
    }

    /**
     * @param array $managers
     */
    public function setManagers(array $managers)
    {
        $this->managers = $managers;
    }

    /**
     * @param array managerText
     */
    public function setManagerTicket(array $managerTicket)
    {
        $this->managerTicket = $managerTicket;
    }

    /**
     * @return null|\QM_DB|\wpdb
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param null|\QM_DB|\wpdb $db
     */
    public function setDb()
    {
        if(empty($this->db)){
            global $wpdb;
            $this->db = $wpdb;
        }
        return $this->db;

    }

    /**
     * @return string
     */
    public static function getPluginUrl(): string
    {
        return self::$plugin_url;
    }

    /**
     * @param string $plugin_url
     */
    public static function setPluginUrl(string $plugin_url)
    {
        if(empty(self::$plugin_url)) {
            self::$plugin_url = $plugin_url;
        }
        return self::$plugin_url;
    }

    /**
     * @return string
     */
    public static function getPluginName($changeDelimiter = false): string
    {
        if($changeDelimiter){
            return str_replace('-','_',self::$plugin_name);
        }
        return self::$plugin_name;
    }

    /**
     * @param string $plugin_name
     */
    public static function setPluginName(string $plugin_name)
    {
        if(empty(self::$plugin_name)){
            self::$plugin_name = $plugin_name;
        }
        return self::$plugin_name;
    }

    /**
     * @return string
     */
    public static function getPluginPath(): string
    {
        return self::$plugin_path;
    }

    /**
     * @param string $plugin_path
     */
    public static function setPluginPath(string $plugin_path)
    {

        if(empty(self::$plugin_path)) {
            self::$plugin_path = $plugin_path;
        }
        return self::$plugin_path ;
    }

    public function activated( string $key )
    {
        $option = get_option( self::getPluginName() );
        return isset( $option[ $key ] ) ? $option[ $key ] : false;
    }

}
