<?php
/**
* @package Give
* @subpackage Cardknox Gateway
* @since 0.0.1
* Cardknox Transaction api request
*/
// Deny From Direct access
if( !defined( 'ABSPATH' ) ) exit;

// create cardknox api class if not exists
if( !class_exists( 'Give_Cardknox_API') ){
  class Give_Cardknox_API{
    /**
  	* Cardknox API Endpoint
  	*/
    const ENDPOINT = "https://x1.cardknox.com/gateway";

    /**
    * @return string
    * Transction Key
    */
    private static $transaction_key = '';

    /**
    * Set Transction Key
    * @return void
    */
    public static function set_transaction_key( $key ){
      self::$transaction_key = $key;
    }
    /**
    * Get transaction key.
    * @return string
    */
    public static function get_transaction_key() {
		    if ( ! self::$transaction_key ) {
  			     $key = give_get_option( 'give_cardknox_transaction_xkey' );
  			        self::set_transaction_key( $key );
        }
		    return self::$transaction_key;
  	}
    /**
    * Send Request to CardKnox API
    * @param request array
    * @param Cardknox API version 4.5.9
    */
    public static function request( array $request, $method = "POST" ){
        $request['xKey'] = self::get_transaction_key();
        $request['xVersion'] = "4.5.9";
        $request['xSoftwareVersion'] = GIVE_CARDKNOX_GATEWAY_VERSION;
	      $request['xSoftwareName'] =  'Give CardKnox Gateway';

        $response = wp_safe_remote_post(
    			self::ENDPOINT,
    			array(
    				'method'        => $method,
    				'body'       => apply_filters( 'give_cardknox_request_body', $request ),
    				'timeout'    => apply_filters( 'give_cardknox_api_request_timeout', 120 )
    			)
    		);
        // return if error in Response
        if ( is_wp_error( $response ) || empty( $response['body'] ) ) {
    			// self::log( 'Error Response: ' . print_r( $response, true ) );
         	return new WP_Error( 'cardknox_error', __( 'There was a problem connecting to the payment gateway.', 'give-cardknox' ) );
    		}
        // Parse Response
        $parsed_response = [];
		    parse_str($response['body'], $parsed_response);

        if (! empty($parsed_response['xResult'] )) {
		        if ($parsed_response['xResult'] != "A" ){
	              return new WP_Error( "cardknox_error", "{$parsed_response['xStatus']}: {$parsed_response['xError']}({$parsed_response['xRefNum']})", 'give-cardknox' );
            }
            else {
		            return $parsed_response;
            }
        }
        else {
    			   return new WP_Error( 'cardknox_error', __( 'There was a problem connecting to the payment gateway.', 'give-cardknox' ) );
    		}
    }

  }
}
