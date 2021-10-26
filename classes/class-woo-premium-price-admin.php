<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @class 		Woo_Premium_Price_Admin
 * @version		1.0.0
 */
class Woo_Premium_Price_Admin {

	/**
	 * Singleton self object
	 *
	 * @var self obj
	 */
	public static $instance;

	/**
	 * Constructor method
	 */
	public function __construct() {

		if ( ! defined( 'WOO_IS_PREMIUM_PRODUCT' ) ) {
			define( 'WOO_IS_PREMIUM_PRODUCT', 'woo_is_premium_product' );
		}

		if ( ! defined( 'WOO_PREMIUM_PERCENTAGE' ) ) {
			define( 'WOO_PREMIUM_PERCENTAGE', 'woo_premium_percentage' );
		}

		if ( is_admin() ) {
			add_action( 'add_meta_boxes', [ $this, 'wpp_premium_price_meta_boxes' ] );
		}

		add_action( 'save_post', [ $this, 'wpp_premium_save_meta_box' ] );

	}

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			return new self();
		} else {
			return self::$instance;
		}
	}

	/**
	 * Register meta box
	 */
	function wpp_premium_price_meta_boxes() {
		add_meta_box( 'wpp-woo-premium-price', __( 'Premium Product', 'woo-premium-product' ), [ $this, 'wpp_premium_price_meta_boxes_render' ], 'product', 'side' );
	}


	/**
	 * Meta box display callback.
	 *
	 * @param WP_Post $post Current post object.
	 */
	function wpp_premium_price_meta_boxes_render( $post ) {
		wp_nonce_field( 'admin_save_woo_premium_rate', 'woo_premium_rate' );
		?>
		<style>
		#woo-premium-rate{
			display: none;
		}
		</style>

		<label class="selectit">
			<input type="checkbox" <?php echo ( $this->wpp_is_premium_product( $post->ID )? "checked='checked'":'' ) ?> name="<?php echo WOO_IS_PREMIUM_PRODUCT ?>" id="woo-is-premium" >
			Is premium product
		</label>

		<div id="woo-premium-rate">
			<h4>Premium price percentage</h4>
				<input type="number" min="0" max="100" value="<?php echo ( ! empty( $this->wpp_get_premium_percent_rate( $post->ID ) ) ?$this->wpp_get_premium_percent_rate( $post->ID ): ''); ?>" name="<?php echo WOO_PREMIUM_PERCENTAGE ?>">%
		</div>

		<script>

			toggleRateField( jQuery( '#woo-is-premium' ).is(':checked') );

			jQuery('#woo-is-premium').on( 'click', function() {
				toggleRateField( this.checked );
			});

			// takes bool arg to toggle display
			function toggleRateField( checked ) {
				( checked === true )? jQuery( '#woo-premium-rate' ).show() : jQuery( '#woo-premium-rate' ).hide();
			}
		</script>

		<?php
	}

	/**
	 * Save meta box content.
	 *
	 * @param int $post_id Post ID
	 */
	function wpp_premium_save_meta_box() {
		if (
			isset( $_POST[ WOO_PREMIUM_PERCENTAGE ] )
			|| wp_verify_nonce( $_POST['woo_premium_rate'], 'admin_save_woo_premium_rate' )
		) {
			if ( ! empty( $_POST[ WOO_IS_PREMIUM_PRODUCT ] ) && ( ! empty( $_POST[ WOO_PREMIUM_PERCENTAGE ] ) && $_POST[ WOO_PREMIUM_PERCENTAGE ] > 0 ) ) {
				update_post_meta( $_POST['post_ID'], WOO_IS_PREMIUM_PRODUCT, $_POST[ WOO_IS_PREMIUM_PRODUCT ] );
				update_post_meta( $_POST['post_ID'], WOO_PREMIUM_PERCENTAGE, $_POST[ WOO_PREMIUM_PERCENTAGE ] );
			} else {
				delete_post_meta( $_POST['post_ID'], WOO_IS_PREMIUM_PRODUCT );
				delete_post_meta( $_POST['post_ID'], WOO_PREMIUM_PERCENTAGE );
			}
		}
	}

	/**
	 * Gets all premium product ids
	 *
	 * @return void
	 */
	function wpp_get_premium_product_ids() {

		$args = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'fields' => 'ids',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => WOO_IS_PREMIUM_PRODUCT,
					'value' => 'on',
				],
			],
		];

		return get_posts( $args );
	}

	/**
	 * getter function to check if a product is premium
	 *
	 * @param int $post_id
	 * @return boolean
	 */
	function wpp_is_premium_product( $post_id ) {
		return ( get_post_meta( $post_id, WOO_IS_PREMIUM_PRODUCT, true ) ) ? true : false;
	}

	/**
	 * Function for permium_percent_rate
	 *
	 * @param int $post_id
	 * @return boolean
	 */
	function wpp_get_premium_percent_rate( $post_id ) {
		$percent_rate = get_post_meta( $post_id, WOO_PREMIUM_PERCENTAGE, true );
		return ( ! empty( $percent_rate ) ) ? intval( $percent_rate ) : false;
	}
}
