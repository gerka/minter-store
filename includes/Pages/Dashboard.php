<?php
/**
 * Created by PhpStorm.
 * User: gerk
 * Date: 17.09.18
 * Time: 14:55
 */
/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\Pages;

use \MinterStore\Base\BaseController;
use \MinterStore\API\SettingsApi;
use \MinterStore\API\Callbacks\AdminCallbacks;
use \MinterStore\API\Callbacks\ManagerCallbacks;

class Dashboard extends BaseController
{
    public $settings;
    public $callbacks;
    public $callbacks_mngr;
    public $callbacks_table;
    public $pages = [];
    public $subPages = [];
    public $tools = [];
    public $nameParentMenu = 'minter_store_plugin_page';
    public $nameMenuDashboard = 'MinterStore';
    public $namePageDashboard = 'minter_store_dashboard';

    public function register(){
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->callbacks_mngr = new ManagerCallbacks();
        $this->setPages();
        $this->setSettings( $this->settings);
        $this->setSections($this->settings);
        $this->setFields($this->settings);
        $this->settings->addPages( $this->pages )->register();

    }

    /**
     * @param array $tools
     */
    public function setTools(array $tools)
    {
        $this->tools = [[
            'page_title'=>  $this->nameMenuDashboard,
            'menu_title'=> $this->nameMenuDashboard,
            'capability'=>'manage_options',
            'menu_slug' => self::getPluginName(),
            'callback'=> array($this->callbacks,'adminDashboard'),
        ]];

        return $this->tools;
    }
    public function setSubPageMenu(){
        $this->subPages = array(
                            array(
                                'parent_slug'=> $this->nameParentMenu,
                                'page_title'=>  $this->nameMenuDashboard,
                                'menu_title'=> $this->nameMenuDashboard,
                                'capability'=> 'manage_options',
                                'menu_slug' => self::getPluginName(),
                                'callback' => array( $this->callbacks, 'adminDashboard' )
                            ));
        $this->settings->addSubPage($this->subPages)->register();
        return $this->subPages;
    }
    public function setPages()
    {
        $this->pages = array(
            array(
                'page_title' => $this->nameMenuDashboard,
                'menu_title' => $this->nameMenuDashboard,
                'capability' => 'manage_options',
                'menu_slug' => self::getPluginName(),
                'callback' => array( $this->callbacks, 'adminDashboard' ),
                'icon_url' => 'dashicons-clipboard',
                'position' => 110
            )
        );
        return $this->pages;
    }
    public function setSettings(SettingsApi $settings)
    {
        $args = array(
            array(
                'option_group' => self::getPluginName().'_settings',
                'option_name' => self::getPluginName(),
                'callback' => array( $this->callbacks_mngr, 'checkboxSanitize' )
            )
        );
        $settings->setSettings( $args );
    }
    public function setSections(SettingsApi $settings)
    {
        $args = array(
            array(
                'id' => self::getPluginName().'_admin_index',
                'title' => 'Table Manager',
                'callback' => array( $this->callbacks_mngr, 'adminSectionManager' ),
                'page' => self::getPluginName()
            )
        );
        $settings->setSections( $args );
    }
    public function setFields( SettingsApi $settings)
    {
        $args = array();
        $hidden = "";
        foreach ( $this->managers as $key => $value ) {

            if(strstr($key,"hidden"))
            {$hidden = " hidden";}
            $args[] = array(
                'id' => $key,
                'title' => $value,
                'callback' => array( $this->callbacks_mngr, 'checkboxField' ),
                'page' => self::getPluginName(),
                'section' => self::getPluginName().'_admin_index',
                'args' => array(
                    'option_name' => self::getPluginName(),
                    'label_for' => $key,
                    'class' => 'ui-toggle'.$hidden
                )
            );
        }
        $settings->setFields( $args );
    }




}