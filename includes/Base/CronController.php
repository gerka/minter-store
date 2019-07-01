<?php
/**
 * Created by PhpStorm.
 * User: devacc
 * Date: 28/06/2019
 * Time: 16:02
 */

namespace MinterStore\Base;
use MinterStore\Exceptions\MinterStoreExceptions;
use Minter\MinterAPI;
use Minter\SDK\MinterConverter;
class CronController extends BaseController
{
    public function register(){
        new KamaCron([
            'id'     => 'PaymentJobs',
            'auto_activate' => true, // false чтобы повесить активацию задач на register_activation_hook()
            'events' => array(
                // первая задача
                'getPayForOrders' => array(
                    'callback'      => [$this,'getPayForOrders'], // название функции крон-задачи
                    'interval_name' => 'every_5_min',
                    'interval_sec'  => MINUTE_IN_SECONDS*5,
                    'interval_desc' => 'Каждые 5 минут',
                ),
            ),
        ]);

    }
    public function getPayForOrders(){
        MinterStoreExceptions::Log(MinterController::getUnpaidStatus());
        $timeToReject = DAY_IN_SECONDS;
        //so get all On-Hold Orders with barter payments
        $args = array(
            'status' => MinterController::getUnpaidStatus(),
            //'payment_method' => MinterController::getPaymentGateName(),
        );
        $orders = wc_get_orders( $args );
        // So now if we don't have pay for 24 Hours from order cancell it
        foreach ($orders as $order){
            MinterStoreExceptions::Log($order->get_date_created()->getTimestamp());
            if(($order->get_date_created()->getTimestamp()+$timeToReject)>time()){
                MinterStoreExceptions::Log('Seems to need check pay '.$order->get_id());
                $UserMinterAddress = substr(get_user_meta($order->get_user_id(),UserControl::getKeyExternalMinterAddress(),1), 2);
                $siteAddress = MinterController::getPublicAddress();
                //search for transaction from user
                $api = new MinterAPI(MinterController::getNodeURL());
                $queryString = '"tx.from=\''.$UserMinterAddress.'\' AND tx.type=\'01\' AND tx.to=\''.$siteAddress.'\' AND tx.coin=\''.MinterController::getBarterTokenName().'\'"';
                MinterStoreExceptions::Log($queryString);
                $EstimateTrans = $api->getTransactions($queryString);
                $transactionArr = $EstimateTrans->result;
                foreach ($transactionArr as $trans){
                    //so check caption
                    MinterStoreExceptions::Log(base64_decode($trans->payload).' Payload',1);
                    if(base64_decode($trans->payload) == $order->get_id()){
                        MinterStoreExceptions::Log('payload OK ',1);
                        //and summ
                        if( MinterConverter::convertValue($trans->data->value,'bip') == $order->get_meta('_barter_price',1)){
                            $order->update_meta_data( '_barter_payment_hash', $trans->hash);
                            $order->update_status(  MinterController::getPaidStatus(), __( 'Barter Complete waiting for seller ship order', 'wc-gateway-barter' ) );
                            MinterStoreExceptions::Log('Need to change status of order'.$order->get_id());
                        }
                    }
                    MinterStoreExceptions::Log(print_r(MinterConverter::convertValue($trans->data->value,'bip').'FROM ',1));
                }
            }else{
                $order->update_status( 'cancelled', __( 'Order cancelled by Time', 'minter-store' ) );
                do_action( 'woocommerce_cancelled_order', $order->get_id() );
                MinterStoreExceptions::Log('Seems to need to reject'.$order->get_id());
            }
        }

    }


}