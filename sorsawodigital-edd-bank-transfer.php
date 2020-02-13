<?php
/*
Plugin Name: Sorsawo EDD Bank Transfer
Description: Adds a payment gateway for bank transfer to Easy Digital Downloads.
Version: 1.0.8
Author: Sorsawo Team
Author URI: https://sorsawo.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class Sorsawo_Digital_EDD_Bank_Transfer {
    private static $_instance = NULL;
    
    /**
     * retrieve singleton class instance
     * @return instance reference to plugin
     */
    public static function get_instance() {
        if ( NULL === self::$_instance ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * Initialize all variables, filters and actions
     */
    private function __construct() {
        add_filter( 'edd_payment_gateways', array( $this, 'register_gateway' ) );
        add_action( 'edd_bank_transfer_cc_form', '__return_false' );
        add_action( 'edd_gateway_bank_transfer', array( $this, 'process_payment' ) );
    }
    
    public function register_gateway( $gateways ) {
        $gateways['bank_transfer'] = array( 'admin_label' => __( 'Bank Transfer', 'sorsawodigital-edd-bank-transfer' ), 'checkout_label' => __( 'Bank Transfer', 'sorsawodigital-edd-bank-transfer' ) );
        return $gateways;
    }
    
    public function process_payment( $purchase_data ) {

        global $edd_options;

        $errors = edd_get_errors();
        
        if ( ! $errors ) {

            /****************************************
            * setup the payment details to be stored
            ****************************************/

            $payment = array(
                'price'        => $purchase_data['price'],
                'date'         => $purchase_data['date'],
                'user_email'   => $purchase_data['user_email'],
                'purchase_key' => $purchase_data['purchase_key'],
                'currency'     => $edd_options['currency'],
                'downloads'    => $purchase_data['downloads'],
                'cart_details' => $purchase_data['cart_details'],
                'user_info'    => $purchase_data['user_info'],
                'status'       => 'pending'
            );

            // record the pending payment
            $payment = edd_insert_payment( $payment );
            
            // go to the success page
            edd_send_to_success_page();
        }
    }
}

function sorsawodigital_edd_bank_transfer_init() {
    if ( class_exists( 'Easy_Digital_Downloads' ) ) {
        Sorsawo_Digital_EDD_Bank_Transfer::get_instance();
    }
}
add_action( 'plugins_loaded', 'sorsawodigital_edd_bank_transfer_init' );