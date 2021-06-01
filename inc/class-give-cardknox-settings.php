<?php
/*
  Add Payment Gateway method in Give WP
*/
class Give_Cardknox_Settings{

  // public class Constructor
  public function __construct(){
    //Init Setting Payemnt gateway settings
    $this->init();
  }
  // Prefix for cardknox
  protected $prefix = 'give_cardknox';
  // Init hooks
  protected function init(){
    add_filter( 'give_payment_gateways', array( $this, 'give_cardknox_payment_gateways') );
    add_filter( 'give_get_sections_gateways', array( $this, 'give_register_cardknox_payment_gateway_sections') );
    add_filter( 'give_get_settings_gateways', array( $this, 'give_register_cardknox_payment_gateway_setting_fields') );
  }
  // CardKnox Payment methods
  public function give_cardknox_payment_gateways( $gateways ){
      // Add cardknox Payment Methods
      $gateways['cardknox_credit_card'] = array(
        'admin_label' => __('Cardknox - Credit Card', 'give-cardknox'),
        'checkout_label' =>  __('Cardknox', 'give-cardknox'),
      );

      return $gateways;
  }

  /**
   * Register Section for Payment Gateway Settings.
   *
   * @param array $sections List of payment gateway sections.
   *
   * @since 1.0.0
   *
   * @return array
   */

  function give_register_cardknox_payment_gateway_sections( $sections ) {

  	$sections['cardknox-settings'] = __( 'Cardknox', 'give-cardknox' );

  	return $sections;
  }

  /**
   * Register Admin Settings.
   *
   * @param array $settings List of admin settings.
   *
   * @since 1.0.0
   *
   * @return array
   */
  function give_register_cardknox_payment_gateway_setting_fields( $settings ) {

  	switch ( give_get_current_setting_section() ) {

  		case 'cardknox-settings':
    			$settings = array(
    				array(
    					'id'   => 'give_title_cardknox',
    					'type' => 'title',
    				),
            array(
              'name' => __( 'Transaction Key', 'give-cardknox' ),
              'desc' => __( 'Enter your Transaction xKey, found in your CardKnox Dashboard in <a href="https://portal.cardknox.com/settings/key-management">key management</a>.', 'give-cardknox' ),
              'id'   => 'give_cardknox_transaction_xkey',
              'type' => 'api_key',
            ),
            array(
              'name' => __( 'Token Key', 'give-cardknox' ),
              'desc' => __( 'Enter your iFields Key (Token Key), found in your CardKnox Dashboard in <a href="https://portal.cardknox.com/settings/key-management">key management</a>.', 'give-cardknox' ),
              'id'   => 'give_cardknox_ifields_key',
              'type' => 'api_key',
            ),
            array(
              'name' => __('Disable billing fields'),
              'id'   => 'give_cardknox_disable_address_fields',
              'type'  => 'checkbox',
              'desc'  => __('when customer will not be able to fill billing details')
            ),
            array(
      				'id'   => 'give_title_cardknox',
      				'type' => 'sectionend',
      			)
    			);
  			break;

  	} // End switch().

  	return $settings;
  }

}
