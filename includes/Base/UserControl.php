<?php
/**
 * Created by PhpStorm.
 * User: devacc
 * Date: 27/02/2019
 * Time: 12:36
 */

namespace MinterStore\Base;
use Minter\SDK\MinterWallet;
use MinterStore\Exceptions\MinterStoreExceptions;


class UserControl extends BaseController
{
    private static $keyExternalMinterAddress = 'external_minter_address';
    private static $keyVerificationExternalMinterAddress = 'verification_external_minter_address';
    private static $keyVerificationKYC = 'verification_kyc';

    protected $externalMinterAddress = '';
    protected $verificationExternalMinterAddress = false;
    protected $verificationKYC = false;

    public function register(){
        add_action( 'show_user_profile', [$this,'extra_user_profile_fields' ]);
        add_action( 'edit_user_profile', [$this,'extra_user_profile_fields' ]);
        add_action( 'personal_options_update', [$this,'save_extra_user_profile_fields'] );
        add_action( 'edit_user_profile_update', [$this,'save_extra_user_profile_fields'] );
        add_action( 'woocommerce_edit_account_form', [$this,'extra_user_profile_fields_woo'] );
        add_action( 'woocommerce_save_account_details', [$this,'save_extra_user_profile_fields_woo'] );
        add_action( 'woocommerce_account_content', [$this,'render_notification_user'] );
        add_action('init',[$this,'settings_fields_user']);
    }
    public function settings_fields_user(){
        $this->setExternalMinterAddress(get_user_meta(get_current_user_id(),self::getKeyExternalMinterAddress(),true),get_current_user_id());
        $this->setVerificationExternalMinterAddress(get_user_meta(get_current_user_id(),self::getKeyVerificationExternalMinterAddress(),true),get_current_user_id());
        $this->setVerificationKYC(get_user_meta(get_current_user_id(),self::getKeyVerificationKYC(),true),get_current_user_id());
        //
    }

    public function render_notification_user()
    {
        if(!$this->getVerificationExternalMinterAddress() && !empty($this->getExternalMinterAddress())){
            wc_add_notice( 'Вам необходимо подтвердить адрес в сети Minter для этого следуйте инструкции ', 'error' );
        }

    }

// Add the custom field "favorite_color"
    function extra_user_profile_fields_woo($user_id) {
        ?>
        <fieldset>
            <legend><?php _e("Minter store information", "blank"); ?></legend>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="<?php echo self::getKeyExternalMinterAddress();?>"><?php _e( 'Ваш личный адрес в Minter', 'minter-store' ); ?>
                    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="<?php echo self::getKeyExternalMinterAddress();?>" id="<?php echo self::getKeyExternalMinterAddress();?>" value="<?php echo esc_attr( $this->getExternalMinterAddress()); ?>" />
            </p>
        </fieldset>

        <?php
    }
//
// Save the custom field 'favorite_color'
    function save_extra_user_profile_fields_woo( $user_id ) {
        // For Favorite color
        if( isset( $_POST[self::getKeyExternalMinterAddress()] ) )
            update_user_meta( $user_id, self::getKeyExternalMinterAddress(), sanitize_text_field( $_POST[self::getKeyExternalMinterAddress()] ) );
    }

    /**
     * @return string
     */
    public static function getKeyExternalMinterAddress(): string
    {
        return self::$keyExternalMinterAddress;
    }


    /**
     * @return string
     */
    public static function getKeyVerificationExternalMinterAddress(): string
    {
        return self::$keyVerificationExternalMinterAddress;
    }

    /**
     * @return string
     */
    public static function getKeyVerificationKYC(): string
    {
        return self::$keyVerificationKYC;
    }

    /**
     * @return bool
     */
    public function isVerificationKYC(): bool
    {
        return $this->verificationKYC;
    }

    /**
     * @param bool $verificationKYC
     * @param int $user_id
     */
    public function setVerificationKYC(bool $verificationKYC,int $user_id)
    {
        $this->verificationKYC = $verificationKYC;
        update_user_meta( $user_id, self::getKeyVerificationKYC(), $verificationKYC );

    }

    /**
     * @return string
     */
    public function getVerificationExternalMinterAddress(): string
    {
        return $this->verificationExternalMinterAddress;
    }

    /**
     * @param bool $verificationExternalMinterAddress
     * @param int $user_id
     */
    public function setVerificationExternalMinterAddress(bool $verificationExternalMinterAddress,int $user_id)
    {
        $this->verificationExternalMinterAddress = $verificationExternalMinterAddress;
        update_user_meta( $user_id, self::getKeyVerificationExternalMinterAddress(), $verificationExternalMinterAddress );

    }


    /**
     * @return string
     */
    public function getExternalMinterAddress(): string
    {
        return $this->externalMinterAddress;
    }

    /**
     * @param string $externalMinterAddress
     * @param int $user_id
     */
    public function setExternalMinterAddress(string $externalMinterAddress,int $user_id)
    {
        if( MinterWallet::validateAddress($externalMinterAddress)) {
            $this->externalMinterAddress = $externalMinterAddress;
            update_user_meta($user_id, self::getKeyExternalMinterAddress(), sanitize_text_field($externalMinterAddress));
        }
        else{
            return false;
        }
    }

    public function save_extra_user_profile_fields( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
        $this->setExternalMinterAddress($_POST[self::getKeyExternalMinterAddress()],$user_id);
        $this->setVerificationExternalMinterAddress($_POST[self::getKeyVerificationExternalMinterAddress()],$user_id);
        $this->setVerificationKYC($_POST[self::getKeyVerificationKYC()],$user_id);
    }

    public function extra_user_profile_fields( $user ) { ?>
        <h3><?php _e("Minter store information", "blank"); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="<?php echo self::getKeyExternalMinterAddress();?>"><?php _e("Public minter address"); ?></label></th>
                <td>
                    <input type="text" name="<?php echo self::getKeyExternalMinterAddress();?>" id="<?php echo self::getKeyExternalMinterAddress();?>" value="<?php echo esc_attr( get_the_author_meta( self::getKeyExternalMinterAddress(), $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php _e("Please enter your public minter address."); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="<?php self::getKeyVerificationExternalMinterAddress(); ?>"><?php _e("Verificated Public minter address"); ?></label></th>
                <td>
                    <input type="checkbox" name="<?php echo self::getKeyVerificationExternalMinterAddress(); ?>" <?php if(esc_attr( get_the_author_meta( self::getKeyVerificationExternalMinterAddress(), $user->ID ) )){echo 'checked=checked';} ?> id="<?php echo self::getKeyVerificationExternalMinterAddress(); ?>" value="<?php echo esc_attr( get_the_author_meta( self::getKeyVerificationExternalMinterAddress(), $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php _e("Verificated Public minter address."); ?></span>
                </td>
            </tr>

            <tr>
                <th><label for="verification_kyc"><?php _e("KYC"); ?></label></th>
                <td>
                    <input type="checkbox" name="<?php echo self::getKeyVerificationKYC(); ?>" <?php if(esc_attr( get_the_author_meta( self::getKeyVerificationKYC(), $user->ID ) )){echo 'checked=checked';} ?> id="<?php echo self::getKeyVerificationKYC(); ?>" value="<?php echo esc_attr( get_the_author_meta( self::getKeyVerificationKYC(), $user->ID ) ); ?>" class="regular-text" /><br />
                    <span class="description"><?php _e("Verificated KYC."); ?></span>
                </td>
            </tr>
        </table>
    <?php }


}