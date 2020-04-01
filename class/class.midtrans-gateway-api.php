<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Midtrans_API class.
 *
 * Communicates with Midtrans API.
 */
class WC_Midtrans_API {

	/**
	 * Server Key.
	 * @var string
	 */
	private static $server_key = '';

	/**
	 * Midtrans Environment.
	 * @var string
	 */
    private static $environment = '';
    
	/**
	 * Plugin Options.
	 * @var string
	 */
	private static $plugin_options = 'midtrans';
	
	/**
	 * Set Server Key.
	 * @param string $key
	 */
	public static function set_server_key( $server_key ) {
		self::$server_key = $server_key;
    }

	/**
	 * Set Midtrans Environment.
	 * @param string $key
	 */
	public static function set_environment( $environment ) {
		self::$environment = $environment;
    }

	/**
	 * Get Plugin Options
	 * @param string $plugin_id
	 */
	public static function getPluginOptions ( $plugin_id ) {
		self::$plugin_options = get_option( 'woocommerce_' . $plugin_id . '_settings' );
	}

	/**
	 * Get Server Key.
	 * @return string
	 */
	public static function get_server_key() {
		if ( ! self::$server_key ) {
			$plugin_options = self::$plugin_options;
			if ( isset( $plugin_options['server_key_v2_production'], $plugin_options['server_key_v2_sandbox'] ) ) {
				self::set_server_key( self::get_environment() == 'production' ? $plugin_options['server_key_v2_production'] : $plugin_options['server_key_v2_sandbox'] );
			}
		}
		return self::$server_key;
	}

    /**
	 * Get Midtrans Environment.
	 * @return string
	 */
	public static function get_environment() {
		if ( ! self::$environment ) {
			// TO DO the payment method id still harcoded need to improve
			$options = get_option( 'woocommerce_midtrans_settings' );

			if ( isset( $options['select_midtrans_environment'] ) ) {
				self::set_environment( $options['select_midtrans_environment'] );
			}
		}
		return self::$environment;
	}

    /**
     * Midtrans API Configuration.
     * @return void
     */
    public static function midtransConfiguration( $plugin_id ) {
		self::getPluginOptions( $plugin_id );
        Midtrans\Config::$isProduction = (self::get_environment() == 'production') ? true : false;
        Midtrans\Config::$serverKey = self::get_server_key();     
        Midtrans\Config::$isSanitized = true;
    }

    /**
     * Create Snap Token.
     * @param  array $params Payment options.
     * @return object Snap response (token and redirect_url).
     * @throws Exception curl error or midtrans error.
     */
    public static function createSnapTransaction( $params, $plugin_id ) {
        self::midtransConfiguration( $plugin_id );
        return Midtrans\Snap::createTransaction( $params );
	}
	
	/**
     * Create Refund.
	 * 
	 * @param int $order_id.
     * @param  array $params Payment options.
     * @return object Refund response.
     * @throws Exception curl error or midtrans error.
     */
    public static function createRefund( $order_id, $params, $plugin_id ) {
		self::midtransConfiguration( $plugin_id );
		return Midtrans\Transaction::refund($order_id, $params);
    }

    /**
     * Get Midtrans Notification.
     * @return object Midtrans Notification response.
     */
    public static function getMidtransNotif( $plugin_id) {
        self::midtransConfiguration( $plugin_id );
        return new Midtrans\Notification();
    }

    /**
     * Retrieve transaction status.
     * @param string $id Order ID or transaction ID.
     * @return object Midtrans response.
     */
    public static function getMidtransStatus( $id, $plugin_id ) {
        self::midtransConfiguration( $plugin_id );
        return Midtrans\Transaction::status( $id );
    }

}