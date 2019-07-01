<?php
/**
 * Created by PhpStorm.
 * User: gerk
 * Date: 17.09.18
 * Time: 17:31
 */

/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\API;
use MinterStore\Base\BaseController;
use MinterStore\Exceptions\MinterStoreExceptions;
/**
 * Class SettingsApi
 * Class for methods which work with WP objects. work with basic settings plugin
 * @package MinterStore\API
 */
class SettingsApi
{
    public $admin_pages = [];

    public $admin_subpages = [];

    public $tool_pages = [];

    public $settings = array();

    public $sections = array();

    public $fields = array();

    public $dashboardPageHook = '';


    public function register(){
        // сохранение опции экрана per_page. Нужно вызывать рано до события 'admin_menu'
//        add_filter( 'set-screen-option', function( $status, $option, $value ){
//            return ( $option == 'listense_table_per_page' ) ? (int) $value : $status;
//        }, 10, 3 );
        if(!empty($this->admin_pages)){
            add_action('admin_menu',array($this,'addAdminMenu'));
        }
        if(!empty($this->tool_pages)){
             add_action('admin_menu',array($this,'addToolMenu'));

        }
        if(!empty($this->settings)){
            add_action('admin_init',array($this,'registerCustomFields'));
        }
    }

    /**
     * @return string
     */
    public function getDashboardPageHook(): string
    {
        return $this->dashboardPageHook;
    }

    public function withSubPage(string $title = null){
        if(empty($this->admin_pages)){
            return $this;
        }
        $admin_page = $this->admin_pages[0];

        $subpage = [[
            'parent_slug'=>  $admin_page['menu_slug'],
            'page_title'=>  $admin_page['page_title'],
            'menu_title'=> $admin_page['menu_title'],
            'capability'=> $admin_page['capability'],
            'menu_slug' => $admin_page['menu_slug'],
            'callback'=>  $admin_page['callback']
            ]
        ];
        $this->admin_subpages = $subpage;
    }

    public function addSubPage(array $pages){
        $this->admin_subpages = array_merge($this->admin_subpages, $pages);
        return $this;
    }

    public function addAdminMenu(){
        foreach ($this->admin_pages as $page){
            add_menu_page($page['page_title'],$page['menu_title'],$page['capability'],$page['menu_slug'],$page['callback'],$page['icon_url'],$page['position']);
        }
        foreach ($this->admin_subpages as $page){
            add_submenu_page($page['parent_slug'],$page['page_title'],$page['menu_title'],$page['capability'],$page['menu_slug'],$page['callback']);
        }
    }

    public function addToolMenu(){
        foreach ($this->tool_pages as $tools){
            $this->dashboardPageHook = add_management_page($tools['page_title'],$tools['menu_title'],$tools['capability'],$tools['menu_slug'],$tools['callback']);
        }

    }

    public function addTools(array $tools)
    {
        $this->tool_pages = $tools;

        return $this;
    }
    public function addPages(array $pages)
    {
        $this->admin_pages = $pages;

        return $this;
    }
    public function setSettings( array $settings )
    {
        if(!empty($this->settings)){
            $settings = array_merge($this->settings,$settings);
        }
        $this->settings = $settings;
        return $this;
    }
    public function setSections( array $sections )
    {
        $this->sections = $sections;
        return $this;
    }
    public function setFields( array $fields )
    {
        if(!empty($this->fields)){
            $fields = array_merge($this->fields,$fields);
        }
        $this->fields = $fields;
        return $this;
    }
    public function registerCustomFields()
    {
        // register setting
        foreach ( $this->settings as $setting ) {
            register_setting( $setting["option_group"], $setting["option_name"], ( isset( $setting["callback"] ) ? $setting["callback"] : '' ) );
        }
        // add settings section
        foreach ( $this->sections as $section ) {
            add_settings_section( $section["id"], $section["title"], ( isset( $section["callback"] ) ? $section["callback"] : '' ), $section["page"] );
        }
        // add settings field
        foreach ( $this->fields as $field ) {
            add_settings_field( $field["id"], $field["title"], ( isset( $field["callback"] ) ? $field["callback"] : '' ), $field["page"], $field["section"], ( isset( $field["args"] ) ? $field["args"] : '' ) );
        }
    }


}