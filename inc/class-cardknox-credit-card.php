<?php
/**
* @package Give
* @subpackage Cardknox Payment Gateway
*/
// Prevent to Direct Access
if( !defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Give_CardKnox_Credit_Card' ) ){

    class Give_CardKnox_Credit_Card extends Give_Cardknox_API{
        /**
        * @param string
        * Cardknox Ifields Version
        */

        protected $ifields_versions = '2.6.2006.0102';

        /**
        * Call all action method on class call
        * @access public
        */
        public function __construct(){
        // Add form on cardknox form
        add_action( 'give_cardknox_credit_card_cc_form', [ $this, 'credit_card_form' ], 10, 2 );
        add_action( 'wp_enqueue_scripts', [$this, 'add_cardknox_script'] );
        // process payemnt
        add_action( 'give_gateway_cardknox_credit_card', [$this, 'process_payment' ] );
        }
        /**
        * check if Cardknox details are available
        * @return boolean
        */
        public function isConfigured(){
        $xkey = trim( give_get_option( 'give_cardknox_transaction_xkey' ) );
        $xifield = trim( give_get_option( 'give_cardknox_ifields_key' ) );
        $status = true;

        if( !$xkey || !$xifield ){
            Give_Notices::print_frontend_notice(sprintf(
            '<strong>%1$s</strong> %2$s',
            esc_html__('Notice:'),
            __('CardKnox is not configurd, missing Transaction key or Token details', 'give-cardknox')
            ));

            $status = false;
        }
        return $status;
        }
        // Enqueue Cardknox Ifields Script
        public function add_cardknox_script(){
        // wp_enqueue_script('jquery');
        // wp_enqueue_script('cardknox-ifields', "https://cdn.cardknox.com/ifields/{$this->ifields_versions}/ifields.min.js", array(), '2.6', true );
        // wp_enqueue_script( 'give-cardknox', GIVE_CARDKNOX_GATEWAY_URL . 'assets/js/cardknox.js',array(), time(), true );
        // Add param for cadknox

        $give_cardknox_params = array(
            'token_key' =>  give_get_option( 'give_cardknox_ifields_key' ),
        );

        wp_localize_script( 'give-cardknox', 'give_cardknox_params', apply_filters( 'give_cardknox_params', $give_cardknox_params ) );
        }
        /**
        * @param int $form_id
        * @param array $args
        * @return string $form
        * @since 0.0.1
        */
        public function credit_card_form( $form_id, $args ){

            // Start Buffering html output
            ob_start();

            do_action( 'give_before_cc_fields', $form_id, $args );
            ?>

            <fieldset id="give_cc_fields" class="give-do-validate">
            <legend>
                <?php _e('Credit Card Details', 'give' ); ?>
            </legend>
            <?php
                if( $this->isConfigured() ){

                echo $this->cardknox_credit_card_fields( $form_id, $args );

                }
            ?>
            </fieldset>
            <?php
            // Remove billing details fields
            $enable_billing_fields = give_get_option('give_cardknox_disable_address_fields');
           if( $enable_billing_fields == 'on' ){
            remove_action( 'give_after_cc_fields', 'give_default_cc_address_fields' );
            }

            do_action( 'give_after_cc_fields', $form_id, $args );

            // get and clean the buffering
            $form = ob_get_clean();

            echo $form;
        }

        /**
        * Credit Card Form fields
        */

        private function cardknox_credit_card_fields( $form_id, $args ){

        ob_start();
        ?>
        <?php if ( is_ssl() ) : ?>
            <div id="give_secure_site_wrapper-<?php echo $form_id; ?>">
            <span class="give-icon padlock"></span>
            <span><?php _e( 'This is a secure SSL encrypted payment.', 'give' ); ?></span>
            </div>
        <?php endif; ?>
        <p id="give-card-number-wrap-<?php echo $form_id; ?>" class="form-row form-row-two-thirds form-row-responsive">
			<label for="card_number-<?php echo $form_id; ?>" class="give-label">
				<?php _e( 'Card Number', 'give' ); ?>
				<span class="give-required-indicator">*</span>
				<?php echo Give()->tooltips->render_help( __( 'The (typically) 16 digits on the front of your credit card.', 'give' ) ); ?>
				<span class="card-type"></span>
			</label>

			<input type="tel" autocomplete="off" name="card_number" id="card_number-<?php echo $form_id; ?>"
				   class="card-number give-input required" placeholder="<?php _e( 'Card Number', 'give' ); ?>"
				   required aria-required="true"/>
		</p>

		<p id="give-card-cvc-wrap-<?php echo $form_id; ?>" class="form-row form-row-one-third form-row-responsive">
			<label for="card_cvc-<?php echo $form_id; ?>" class="give-label">
				<?php _e( 'CVC', 'give' ); ?>
				<span class="give-required-indicator">*</span>
				<?php echo Give()->tooltips->render_help( __( 'The 3 digit (back) or 4 digit (front) value on your card.', 'give' ) ); ?>
			</label>

			<input type="tel" size="4" autocomplete="off" name="card_cvc" id="card_cvc-<?php echo $form_id; ?>"
				   class="card-cvc give-input required" placeholder="<?php _e( 'CVC', 'give' ); ?>"
				   required aria-required="true"/>
		</p>

		<p id="give-card-name-wrap-<?php echo $form_id; ?>" class="form-row form-row-two-thirds form-row-responsive">
			<label for="card_name-<?php echo $form_id; ?>" class="give-label">
				<?php _e( 'Cardholder Name', 'give' ); ?>
				<span class="give-required-indicator">*</span>
				<?php echo Give()->tooltips->render_help( __( 'The name of the credit card account holder.', 'give' ) ); ?>
			</label>

			<input type="text" autocomplete="off" name="card_name" id="card_name-<?php echo $form_id; ?>"
				   class="card-name give-input required" placeholder="<?php esc_attr_e( 'Cardholder Name', 'give-cardknox' ); ?>"
				   required aria-required="true"/>
		</p>
        <?php 
            do_action( 'give_before_cc_expiration' );
        ?>
        <p id="give-card-expiration-wrap-<?php echo $form_id; ?>" class="card-expiration form-row form-row-one-third form-row-responsive">
            <label for="card_expiry-<?php echo $form_id; ?>" class="give-label">
            <?php _e( 'Expiration', 'give-cardknox' ); ?>
            <span class="give-required-indicator">*</span>
            <?php echo Give()->tooltips->render_help( __( 'The date your credit card expires, typically on the front of the card.', 'give' ) ); ?>
            </label>

            <input type="hidden" id="card_exp_month-<?php echo $form_id; ?>" name="card_exp_month"
                class="card-expiry-month"/>
            <input type="hidden" id="card_exp_year-<?php echo $form_id; ?>" name="card_exp_year"
                class="card-expiry-year"/>

            <input type="tel" autocomplete="off" name="card_expiry" id="card_expiry-<?php echo $form_id; ?>"
                class="card-expiry give-input required" placeholder="<?php esc_attr_e( 'MM / YY', 'give' ); ?>"
                required aria-required="true"/>
        </p>

        <!--And a field for all errors from the iFields-->
        <label id="transaction-status"></label>
        <label data-ifields-id="card-data-error" style="color: red;"></label>
        <?php

        do_action( 'after_cardknox_fields', $form_id, $args );
        return ob_get_clean();
        }
        // Process Payment
        public function process_payment( $posted_data ){
            // Make sure we don't have any left over errors present.
            give_clear_errors();

            // Any errors?
            $errors = give_get_errors();

            // No errors, proceed.
            if ( ! $errors ) {

                $form_id         = intval( $posted_data['post_data']['give-form-id'] );
                $price_id        = ! empty( $posted_data['post_data']['give-price-id'] ) ? $posted_data['post_data']['give-price-id'] : 0;
                $donation_amount = ! empty( $posted_data['price'] ) ? $posted_data['price'] : 0;

                // Setup the payment details.
                $donation_data = array(
                    'price'           => $donation_amount,
                    'give_form_title' => $posted_data['post_data']['give-form-title'],
                    'give_form_id'    => $form_id,
                    'give_price_id'   => $price_id,
                    'date'            => $posted_data['date'],
                    'user_email'      => $posted_data['user_email'],
                    'purchase_key'    => $posted_data['purchase_key'],
                    'currency'        => give_get_currency( $form_id ),
                    'user_info'       => $posted_data['user_info'],
                    'status'          => 'pending',
                    'gateway'         => 'Cardknox',
                );

                // Record the pending donation.
                $donation_id = give_insert_payment( $donation_data );

                if ( ! $donation_id ) {

                    // Record Gateway Error as Pending Donation in Give is not created.
                    give_record_gateway_error(
                        __( 'Cardknox Error', 'give-cardknox' ),
                        sprintf(
                        /* translators: %s Exception error message. */
                        __( 'Unable to create a pending donation with Give.', 'give-cardknox' )
                        )
                    );

                    // Send user back to checkout.
                    give_send_back_to_checkout( '?payment-mode=cardknox' );
                    return;
                }

                $expiry = $posted_data['post_data']['card_expiry'];

                $payment_request = array(
                    'xCommand' => 'cc:Sale',
                    'xName'    => $posted_data['card_info']['card_name'],
                    'xCardNum' => $posted_data['card_info']['card_number'],
                    'xExp'     => preg_replace('/[^0-9]/', '', $expiry ),
                    'xCVV'     => $posted_data['card_info']['card_cvc'],
                    'xAmount'  => $donation_amount,
                    'xPONum'   =>  $posted_data['purchase_key'],
                    'xOrderID'   =>  $posted_data['purchase_key'],
                    'xInvoice'   =>  $posted_data['purchase_key'],
                    'xCurrency' => give_get_currency( $form_id ),
                    'xIP'   => give_get_payment_user_ip( $donation_id ),
                    'xEmail' => $posted_data['post_data']['give_email'],
                    'xBillFirstName' =>  $posted_data['post_data']['give_first'],
                    'xBillLastName' =>  $posted_data['post_data']['give_last'],
                    'xBillCompany' =>  $posted_data['post_data']['give_company_name'],
                    'xComments' =>  $posted_data['post_data']['give_comment'],
                    'xExistingCustomer' => 'yes',
                    'xCustReceipt'  => true,
                );

                $response = $this->request( $payment_request );

                // Update donation status to `completed`.
                give_update_payment_status( $donation_id, 'completed' );

                // Success. Send user to success page.
                give_send_to_success_page();

            }
            else {// Send user back to checkout.
                give_send_back_to_checkout( '?payment-mode=cardknox' );
            }
        }
    }
}
