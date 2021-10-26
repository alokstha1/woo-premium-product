<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/** 
 * Woo_Premium_Price manages all frontend attributes of the plugin
 */
class Woo_Premium_Price {

	/**
	 * Singleton self object
	 *
	 * @var self obj
	 */
	public static $instance;

	/**
	 * premium product ids
	 *
	 * @var array
	 */
	protected $nominated_premium_product_ids;

	/**
	 * premium product ids in current cart
	 *
	 * @var array
	 */
	protected $cart_premium_product_ids;

	/**
	 * Current cart object
	 *
	 * @var WC_Cart object
	 */
	public $cart;

	/**
	 * The constructor method
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_loaded', [ $this, 'init' ] );
	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			return new self();
		} else {
			return self::$instance;
		}
	}

	public function init() {

		// bail early, if cart doesnt exists or if empy.
		if ( ! WC()->cart instanceof WC_Cart || WC()->cart->is_empty() || is_admin() ) {
			return;
		}

		// assign current cart to local var
		$this->cart = WC()->cart;

		$this->nominated_premium_product_ids = Woo_Premium_Price_Admin::instance()->wpp_get_premium_product_ids();

		$this->wpp_cart_premium_product_ids();

		if ( empty( $this->cart_premium_product_ids ) ) {
			return;
		}

		$this->wpp_do_hooks_interceptions();
	}

	/**
	 * Gets permium product ids from current cart,
	 * and assigns class attribute $cart_premium_product_ids var
	 *
	 * @return void
	 */
	protected function wpp_cart_premium_product_ids() {

		$product_ids_in_cart = wp_list_pluck( $this->cart->cart_contents, 'product_id' );

		if ( empty( $product_ids_in_cart ) ) {
			$this->cart_premium_product_ids = [];
		} else {
			$this->cart_premium_product_ids = array_intersect( $product_ids_in_cart, $this->nominated_premium_product_ids );
		}
	}

	/**
	 * Hooks that is responsible
	 * to maintain/calculate product pricing.
	 *
	 * @return void
	 */
	protected function wpp_do_hooks_interceptions() {
		add_action( 'woocommerce_before_calculate_totals', [ $this, 'wpp_maybe_add_premium_price' ], 10 );
	}

	/**
	 * Add premium price before calculating items in the cart.
	 *
	 * @param WC_CART object $wc_cart
	 * @return void
	 */
	public function wpp_maybe_add_premium_price( $wc_cart ) {

		if ( empty( $this->cart_premium_product_ids ) ) {
			return $wc_cart;
		}

		foreach ( $wc_cart->get_cart() as $key => $value ) {
			$premium_price = $value['data']->get_price() + $this->wpp_calculate_premium( $value['data']->get_id(), $value['data']->get_price() );
			$value['data']->set_price( $premium_price );
		}
	}

	/**
	 * Calculate PREMIUM_PERCENTAGE amount from the price.
	 *
	 * @param float $price
	 * @return float
	 */
	public function wpp_calculate_premium( $product_id, $price ) {

		$premium_percent_rate = Woo_Premium_Price_Admin::instance()->wpp_get_premium_percent_rate( $product_id );

		if ( ! empty( $premium_percent_rate ) ) {
			$premium_price = ( $premium_percent_rate / 100 ) * floatval( $price );
			return floatval( number_format( $premium_price, 2 ) );
		}
	}

}