<?php
/*
Plugin Name: bKash for WooCommerce
Plugin URI: https://wordpress.org/plugins/woocommerce-bkash/
Description: bKash payment gateway integration for WooCommerce
Version: 1.1.1
Author: Tareq Hasan
Author URI: https://tareq.co
License: GPL2
*/

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce - bKash integration
 *
 * @author Tareq Hasan
 */
class WeDevs_bKash {

    private $db_version = '1.0';
    private $version_key = '_bkash_version';

    /**
     * Kick off the plugin
     */
    public function __construct() {
        add_action( 'plugins_loaded', array($this, 'init') );
        add_filter( 'woocommerce_payment_gateways', array($this, 'register_gateway') );

        register_activation_hook( __FILE__, array($this, 'install') );
    }

    /**
     * Load the plugin on `init` hook
     *
     * @return void
     */
    function init() {
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            return;
        }

        require_once dirname( __FILE__ ) . '/includes/class-wc-bkash.php';
        require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-bkash.php';
    }

    /**
     * Register WooCommerce Gateway
     *
     * @param  array  $gateways
     *
     * @return array
     */
    function register_gateway( $gateways ) {
        $gateways[] = 'WC_Gateway_bKash';

        return $gateways;
    }

    /**
     * Create the transaction table
     *
     * @return void
     */
    function install() {
        global $wpdb;

        $query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wc_bkash` (
              `id` int(11) UNSIGNED NOT NULL,
              `trxId` varchar(20) DEFAULT NULL,
              `sender` varchar(15) DEFAULT NULL,
              `ref` varchar(100) DEFAULT NULL,
              `amount` varchar(10) DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $wpdb->query( $query );

        $query = "ALTER TABLE `{$wpdb->prefix}_wc_bkash`
			ADD UNIQUE KEY `trxId_2` (`trxId`),
			ADD KEY `trxId` (`trxId`);";

  		$wpdb->query( $query );

        $this->plugin_upgrades();

        update_option( $this->version_key, $this->db_version );
    }

    /**
     * Do plugin upgrade tasks
     *
     * @return void
     */
    private function plugin_upgrades() {
        global $wpdb;

        $version = get_option( $this->version_key, '0.1' );

        if ( version_compare( $this->db_version, $version, '<=' ) ) {
            return;
        }

        switch ( $version ) {
            case '0.1':
                // $sql = "ALTER TABLE `{$wpdb->prefix}wc_bkash` CHANGE `trxId` `trxId` BIGINT(20) NULL DEFAULT NULL;";
                // $wpdb->query( $sql );
                break;
        }
    }
}

new WeDevs_bKash();
