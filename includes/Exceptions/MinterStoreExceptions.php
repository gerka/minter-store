<?php
/**
 * Created by PhpStorm.
 * User: devacc
 * Date: 09/01/2019
 * Time: 12:43
 */
namespace MinterStore\Exceptions;
use Throwable;

class MinterStoreExceptions extends \Exception
{
    const MSG_PREFIX = '[Minter Store Plugin] ';
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = self::MSG_PREFIX . $message;
        parent::__construct($message, $code, $previous);
    }
    public static function Log($text=null,$place=null){
            if(is_array($text)){
                $text = print_r($text,1);
            }
            $arr = debug_backtrace(false,2);
            error_log('[ MinterStore '.$arr[1]['function'].'] '.$text);

        return;
    }
}