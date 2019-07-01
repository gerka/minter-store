<?php
/**
 * Created by PhpStorm.
 * User: gerk
 * Date: 18.09.18
 * Time: 14:05
 */
/**
 * Avoid construct method this method reserved in BaseController for declare plugin vars, Simply extend that. if you need construct use register() instead of construct it call when init
 *
 */
namespace MinterStore\API\Callbacks;
use Minter\SDK\MinterWallet;use MinterStore\Base\BaseController;
use MinterStore\Exceptions\MinterStoreExceptions;

class ManagerCallbacks extends BaseController
{

    public function checkboxSanitize( $input )
    {
        MinterStoreExceptions::Log($input);
        $output = array();
        foreach ( $this->managers as $key => $value ) {
            $output[$key] = isset( $input[$key] ) ? true : false;
        }
        return $output;
    }
    public function textTicketSanitize( $input )
    {
        MinterStoreExceptions::Log($input);
        $output = [];
        foreach ( $this->managerTicket as $key => $value ) {

            $output[$key] = (isset( $input[$key] )) ?  $input[$key] : false;
        }
        return $output;
    }
    public function textSanitize( $input )
    {
        MinterStoreExceptions::Log($input);
        $output = [];
        foreach ( $this->managerText as $key => $value ) {
            if($key == 'public_address'){
                $output[$key] = (isset( $input[$key] ) && MinterWallet::validateAddress($input[$key])) ?  $input[$key] : false;
            }else{
                $output[$key] = (isset( $input[$key] )) ?  $input[$key] : false;
            }

        }
        return $output;
    }

    public function adminSectionManager()
    {
        return;

    }
    public function checkboxField( $args )
    {
        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $checkbox = get_option( $option_name );
        $checked = isset($checkbox[$name]) ? ($checkbox[$name] ? true : false) : false;
        echo '<div class="' . $classes . '"><input type="checkbox" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="1" class="" ' . ( $checked ? 'checked' : '') . '><label for="' . $name . '"><div></div></label></div>';
    }
    public function textTicketField( $args )
    {
        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $textField = get_option( $option_name );
        $textFieldValue = isset($textField[$name]) ? ($textField[$name] ? $textField[$name] : '') : '';
        echo '<div class="' . $classes . '"><input type="text" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="'.$textFieldValue.'" class=""><label for="' . $name . '"><div></div></label></div>';
    }
    public function textField( $args )
    {
        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $textField = get_option( $option_name );
        $textFieldValue = isset($textField[$name]) ? ($textField[$name] ? $textField[$name] : '') : '';
        echo '<div class="' . $classes . '"><input type="text" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="'.$textFieldValue.'" class=""><label for="' . $name . '"><div></div></label></div>';
    }
}