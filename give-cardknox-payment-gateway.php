<?php
/*
  Plugin Name: Give - Cardknox Payment Gateway Add-on
  Author: Vishal Tanwar
  Author URI: https://github.com/code-QL
  Version: 1.0.0
  Description: Cardknox Payment Gateway add-on for Give Donation
  Text Domain: give-cardknox
*/

/* Prevent File from direct open */
if( !defined( 'ABSPATH' ) ) die;

if( ! class_exists('Give_Cardknox_Gateway') ){

  final class Give_Cardknox_Gateway{

    /**
    * Hold Instance of current class object
    * @var object
    * @access private
    * @static
    */
    private static $instance;

    /**
		 * Notices (array)
		 *
		 * @since 0.0.1
		 *
		 * @var array
		 */
		public $notices = [];

    /**
    * Cardknox admin settings
    * @return object
    */
    public $admin_settings;

    // Initialize Give CardKnox Gateway
    public static function init() {
      if( !isset( self::$instance ) ){
        self::$instance = new Give_Cardknox_Gateway();
      }
      self::$instance->setup();
    }

    /**
    * Setup Give CardKnox Gateway
    * @access private
    * @since 0.0.1
    */
    private function setup(){
      // Init constants
      self::$instance->constants();
      // Registration Hooks

      // Add Hooks
      add_action( 'give_init', array( $this, 'give_init' ), 10 );
      add_action( 'admin_init', [ $this, 'check_environment' ], 999 );
			add_action( 'admin_notices', [ $this, 'admin_notices' ], 15 );
    }

    /**
		 * Check plugin environment.
		 *
		 * @since  0.0.1
		 * @access public
		 *
		 * @return bool
		 */
		public function check_environment() {
			// Flag to check whether plugin file is loaded or not.
			$is_loaded = true;

			// Check for if give plugin activate or not.
			$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

			if ( empty( $is_give_active ) ) {
				// Show admin notice.
				$this->add_admin_notice( 'is_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for the Give - Cardknox Payment Gateway Add-on to activate.', 'give-cardknox' ), 'https://givewp.com' ) );
				$is_loaded = false;
			}

			return $is_loaded;
		}

    /**
    *  Setup Constants
    * @access private
    * @since 0.0.1
    */
    private function constants(){
      if( !defined('GIVE_CARDKNOX_GATEWAY_VERSION') ){
        define( 'GIVE_CARDKNOX_GATEWAY_VERSION', '0.0.1' );
      }
      if ( ! defined( 'GIVE_CARDKNOX_GATEWAY_SLUG' ) ) {
				define( 'GIVE_CARDKNOX_GATEWAY_SLUG', 'give-cardknox-payment-gateway' );
			}
      if( !defined('GIVE_CARDKNOX_GATEWAY_FILE') ){
        define( 'GIVE_CARDKNOX_GATEWAY_FILE', __FILE__ );
      }
      if ( ! defined( 'GIVE_CARDKNOX_GATEWAY_DIR' ) ) {
				define( 'GIVE_CARDKNOX_GATEWAY_DIR', dirname( GIVE_CARDKNOX_GATEWAY_FILE ) );
			}
			if ( ! defined( 'GIVE_CARDKNOX_GATEWAY_URL' ) ) {
				define( 'GIVE_CARDKNOX_GATEWAY_URL', plugin_dir_url( GIVE_CARDKNOX_GATEWAY_FILE ) );
			}
			if ( ! defined( 'GIVE_CARDKNOX_GATEWAY_BASENAME' ) ) {
				define( 'GIVE_CARDKNOX_GATEWAY_BASENAME', plugin_basename( GIVE_CARDKNOX_GATEWAY_FILE ) );
			}
    }



    /**
    *  Give Cardknox Payment Includes
    * @access protected
    * @since 0.0.1
    */
    protected function includes(){
      // Cradknox Api
      include_once GIVE_CARDKNOX_GATEWAY_DIR . '/inc/class-cardknox-api.php';
      // Admin Settings Class
      include_once GIVE_CARDKNOX_GATEWAY_DIR . '/inc/class-give-cardknox-settings.php';
      // Call Admin  Class
      self::$instance->admin_settings = new Give_Cardknox_Settings();

      // Frontend Cardknox Credit Form Class
      include_once GIVE_CARDKNOX_GATEWAY_DIR . '/inc/class-cardknox-credit-card.php';
      // Call Form Class
      new Give_CardKnox_Credit_Card();
    }

    /**
    * Call on give init action
    * @return void
    * @access public
    * @method give_init
    * @since 0.0.1
    */
    public function give_init() {
      self::$instance->includes();
    }

		/**
		 * Allow this class and other classes to add notices.
		 *
		 * @since 0.0.1
		 *
		 * @param $slug
		 * @param $class
		 * @param $message
		 */
  		public function add_admin_notice( $slug, $class, $message ) {
  			$this->notices[ $slug ] = [
  				'class'   => $class,
  				'message' => $message,
  			];
  		}

  		/**
  		 * Display admin notices.
  		 *
  		 * @since 0.0.1
  		 */
  		public function admin_notices() {

  			$allowed_tags = [
  				'a'      => [
  					'href'  => [],
  					'title' => [],
  					'class' => [],
  					'id'    => [],
  				],
  				'br'     => [],
  				'em'     => [],
  				'span'   => [
  					'class' => [],
  				],
  				'strong' => [],
  			];

  			foreach ( (array) $this->notices as $notice_key => $notice ) {
  				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
  				echo wp_kses( $notice['message'], $allowed_tags );
  				echo '</p></div>';
  			}
		}
  }
}

// Init Cardknox Plugin
Give_Cardknox_Gateway::init();
