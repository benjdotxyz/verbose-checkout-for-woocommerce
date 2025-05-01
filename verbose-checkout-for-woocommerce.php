<?php
/**
 * Plugin Name: Verbose Checkout for WooCommerce
 * Requires Plugins: woocommerce
 * Description: Adds some extra logging to WooCommerce checkouts. Inspired by --verbose flags
 * Author: Benjamin Green
 * Author URI: https://benj.xyz
 * Version: 1.0.0
 * Text Domain: verbose-checkout-for-wc
 *
 * GitHub Plugin URI: benjdotxyz/verbose-checkout-for-woocommerce/
 *
 * Copyright: (c) 2025 Benjamin Green
 *
 * License: GNU General Public License v2.0 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package   Verbose-Checkout-for-WC
 * @author    Benjamin Green
 * @category  Admin
 * @copyright Copyright (c) 2025 Benjamin Green
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 (or later)
 *
 */

defined( 'ABSPATH' ) or exit;

add_action( 'woocommerce_init', 'verbose_checkout_for_wc' );

class WC_Verbose_Checkout {

    protected $logger;
    protected $log_file;

    public function __construct() {
        $this->logger = wc_get_logger();
        $this->log_file = ['source' => 'verbose-checkout-' . gmdate( 'Y-m-d' )];
        $this->init_hooks();
    }

    public function vcwc_log_hook() {
        $this->logger->info( 'Firing ' . current_filter(), $this->log_file );
    }
    
    public function vcwc_log_conclusion() {
        $this->logger->info( '-----End ' . current_filter() . ' -----', $this->log_file );
    }

    public function init_hooks() {
        $hooks = array(
            'woocommerce_checkout_create_order', 
            'woocommerce_new_order', 
            'woocommerce_checkout_order_created', 
            'woocommerce_checkout_order_processed', 
            'woocommerce_update_order', 
            'woocommerce_order_status_changed', 
            'woocommerce_pre_payment_complete', 
            'woocommerce_payment_complete', 
            'woocommerce_payment_complete_order_status_processing', 
            'woocommerce_payment_complete_order_status_completed', 
            'woocommerce_store_api_checkout_order_processed', 
            'woocommerce_order_status_draft_to_pending', 
            'woocommerce_order_status_pending_to_processing', 
            'woocommerce_thankyou'
        );

        foreach ( $hooks as $hook ) {
            // hooks on the action to log when the hook is fired
            add_action( $hook, array( $this, 'vcwc_log_hook' ) );
            
            //in a few instances, hook on an second action to help visualize the end of a stage of the order process. 
            //doesn't actually give more info, just makes logs a bit easier to understand. 
            if ( $hook === 'woocommerce_thankyou' ||
                 $hook === 'woocommerce_checkout_order_processed' ||
                 $hook === 'woocommerce_store_api_checkout_order_processed') {
                add_action( $hook, array( $this, 'vcwc_log_conclusion' ) );
            }
        }
    }
}

function verbose_checkout_for_wc() {
    return new WC_Verbose_Checkout();
}