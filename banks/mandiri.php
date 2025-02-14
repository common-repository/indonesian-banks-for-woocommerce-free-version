<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

/**
 * @package   Indonesian Banks
 * @author    Walter Pinem
 * @category  Admin | Checkout Page
 * @copyright Copyright (c) 2018, Walter Pinem, Seni Berpikir
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// Begin creating the bank function
class WC_Gateway_Mandiri extends WC_Gateway_BACS {
	function __construct() {
		$this->id = "bank_mandiri";
		$this->method_title = __( "Bank Mandiri", 'woocommerce' );
		$this->method_description = __( "Here you can setup offline payment method using Bank Mandiri transfer", 'woocommerce' );
		$this->title = __( "Transfer Bank Mandiri", 'woocommerce' );
		$this->icon = plugins_url( 'assets/logo-mandiri.png',__FILE__);
		$this->has_fields = false;
		$this->init_form_fields();
		$this->init_settings();

		foreach ( $this->settings as $setting_key => $value ) {
          $this->$setting_key = $value;
      }
      if ( is_admin() ) {
       add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
       add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_account_details' ) );
       $this -> generate_account_details_html();
   }
   add_action( 'woocommerce_thankyou_bank_mandiri', array( $this, 'thankyou_page' ) );
   add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

 	// BACS account fields shown on the thanks page and in emails
   $this->account_details = get_option( 'woocommerce_bank_mandiri_accounts',
    array(
        array(
            'account_name'   => $this->get_option( 'account_name' ),
            'account_number' => $this->get_option( 'account_number' ),
            'sort_code'      => $this->get_option( 'sort_code' ),
            'bank_name'      => $this->get_option( 'bank_name' ),
            'iban'           => $this->get_option( 'iban' ),
            'bic'            => $this->get_option( 'bic' )
        )
    )
);

}

// Build the setting fields
public function init_form_fields() {
    $this->form_fields = array(
        'enabled' => array(
            'title'     => __( 'Enable / Disable', 'woocommerce' ),
            'label'     => __( 'Enable <strong>Bank Mandiri</strong> Transfer Payment Method', 'woocommerce' ),
            'type'      => 'checkbox',
            'default'   => 'yes',
        ),
        'title' => array(
            'title'     => __( 'Title', 'woocommerce' ),
            'type'      => 'text',
            'desc_tip'  => __( 'Payment Method Title You Would Like to Display to Your Customer', 'woocommerce' ),
            'default'   => __( 'Transfer Bank Mandiri', 'woocommerce' ),
        ),
        'description' => array(
            'title'     => __( 'Description', 'woocommerce' ),
            'type'      => 'textarea',
            'desc_tip'  => __( 'Payment Method Description You Would Like to Display to Your Customer', 'woocommerce' ),
            'default'   => __( 'Use this form to describe how this payment method would work. e.g. Gunakan metode pembayaran transfer Bank Mandiri.', 'woocommerce' ),
            // 'css'       => 'max-width:350px;'
        ),
        'instructions' => array(
            'title'       => __( 'Instructions', 'woocommerce' ),
            'type'        => 'textarea',
            'description' => __( 'Write up the instructions to pay using this payment method. This Payment Instruction will be displayed to your customer on Thank You page and Order Notification Email.', 'woocommerce' ),
            'default'     => 'e.g. Go to nearby ATM center or use your Internet / m-Banking and transfer the amount of money to the following bank account number(s). Do not forget to include the order ID upon transferring the money',
            'desc_tip'    => true,
        ),
        'account_details' => array(
            'type'        => 'account_details'
        ),

    );      
}	

// Saving the account data
public function save_account_details() {

    $accounts = array();

    if ( isset( $_POST['bacs_account_name'] ) ) {

        $account_names   = array_map( 'wc_clean', $_POST['bacs_account_name'] );
        $account_numbers = array_map( 'wc_clean', $_POST['bacs_account_number'] );
        $bank_names      = array_map( 'wc_clean', $_POST['bacs_bank_name'] );
        $sort_codes      = array_map( 'wc_clean', $_POST['bacs_sort_code'] );
        $ibans           = array_map( 'wc_clean', $_POST['bacs_iban'] );
        $bics            = array_map( 'wc_clean', $_POST['bacs_bic'] );

        foreach ( $account_names as $i => $name ) {
            if ( ! isset( $account_names[ $i ] ) ) {
                continue;
            }

            $accounts[] = array(
                'account_name'   => $account_names[ $i ],
                'account_number' => $account_numbers[ $i ],
                'bank_name'      => $bank_names[ $i ],
                'sort_code'      => $sort_codes[ $i ],
                'iban'           => $ibans[ $i ],
                'bic'            => $bics[ $i ]
            );
        }
    }

    update_option( 'woocommerce_bank_mandiri_accounts', $accounts );

}

private function bank_details( $order_id = '' ) {

    if ( empty( $this->account_details ) ) {
        return;
    }

    // Get order and store in $order
    $order          = wc_get_order( $order_id );

    // Get the order country and country $locale
    $country        = $order->billing_country;
    $locale         = $this->get_country_locale();

    // Get sortcode label in the $locale array and use appropriate one
    $sortcode = isset( $locale[ $country ]['sortcode']['label'] ) ? $locale[ $country ]['sortcode']['label'] : __( 'Sort Code', 'woocommerce' );

    echo '<center><strong><h3>' . __( 'Berikut Detail Rekening Bank Untuk Memproses Pembayaran:', 'woocommerce' ) . '</h3></strong></center>' . PHP_EOL;

    $bacs_accounts = apply_filters( 'woocommerce_bacs_accounts', $this->account_details );

    if ( ! empty( $bacs_accounts ) ) {
        foreach ( $bacs_accounts as $bacs_account ) {

            $bacs_account = (object) $bacs_account;

            if ( $bacs_account->account_name || $bacs_account->bank_name ) {
                echo '<center><h5>' . wp_unslash( implode( ' - ', array_filter( array( $bacs_account->account_name, $bacs_account->bank_name ) ) ) ) . '</h5></center>' . PHP_EOL;
            }

            echo '<ul class="order_details bacs_details">' . PHP_EOL;

            // BACS account fields shown on the thanks page and in emails
            $account_fields = apply_filters( 'woocommerce_bacs_account_fields', array(
                'account_number'=> array(
                    'label' => __( 'Nomor Rekening', 'woocommerce' ),
                    'value' => $bacs_account->account_number
                ),
                'sort_code'     => array(
                    'label' => $sortcode,
                    'value' => $bacs_account->sort_code
                ),
                'iban'          => array(
                    'label' => __( 'IBAN', 'woocommerce' ),
                    'value' => $bacs_account->iban
                ),
                'bic'           => array(
                    'label' => __( 'BIC', 'woocommerce' ),
                    'value' => $bacs_account->bic
                )
            ), $order_id );

            foreach ( $account_fields as $field_key => $field ) {
                if ( ! empty( $field['value'] ) ) {
                    echo '<center><li class="' . esc_attr( $field_key ) . '">' . esc_attr( $field['label'] ) . ': <strong>' . wptexturize( $field['value'] ) . '</strong></li></center>' . PHP_EOL;
                }
            }

            echo '</ul>';
        }
    }

}

// Instruction on Thank You page   
public function thankyou_page( $order_id ) {
    if ( $this->instructions ) {
        echo '<h3>Payment Instruction</h3>';
        echo wpautop( wptexturize( wp_kses_post( $this->instructions ) ) );
    }
    $this->bank_details( $order_id );
}
public function process_payment( $order_id ) {

    $order = wc_get_order( $order_id );

    // Mark as on-hold (we're awaiting the payment)
    $order->update_status( 'on-hold', __( 'Waiting for Payment through Bank Mandiri', 'woocommerce' ) );

    // Reduce stock levels
    $order->reduce_order_stock();

    // Remove cart
    WC()->cart->empty_cart();

    // Return thankyou redirect
    return array(
        'result'    => 'success',
        'redirect'  => $this->get_return_url( $order )
    );

}

// Email Instructions
public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

    if ( ! $sent_to_admin && 'bank_mandiri' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
        if ( $this->instructions ) {
            echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
        }
        $this->bank_details( $order->id );
    }

}


}


?>