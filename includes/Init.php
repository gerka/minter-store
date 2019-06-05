<?php

namespace MinterStore;

/**
 * Class Init
 * Automated Initialization Classes
 * @package MinterStore
 */
final class Init

{

    /**
     * Store all the classes inside in an array
     * @return array full list of classes
     */
    public static function get_services(){
        return [
           // Base\RoleControl::class,
            Pages\Dashboard::class,
            Base\Enqueue::class,
            Base\SettingsLinks::class
        ];
    }


    /**
     * Loop through the classes , initialize them, and call the register method if exist
     */
    public static function register_services()
    {
        foreach (self::get_services() as $serviceClass) {
            $service = self::instantiate($serviceClass);
            if (method_exists($service, 'register')) {
                $service->register();
            }

        }
    }

    /**
     * Initialize Class
     * @param $class class from services array
     * @return class instance of the called class
     */
    private static function instantiate($class){
            return new $class();
    }

}

