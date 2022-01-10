<?php
/**
 * Plugin Name: Discord SSO
 * Plugin URI: https://plugish.com
 * Description: Sell products and services with recurring payments in your WooCommerce Store.
 * Author: JayWood
 * Author URI: https://plugish.com/
 * Version: 1.0
 */
namespace com\plugish\discord\sso;

use com\plugish\discord\sso\app\Authentication;
use com\plugish\discord\sso\app\Settings;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

function get_view( $file ) {

	return include 'views/' . $file;
}

add_action( 'enqueue_block_editor_assets', function() {
	$assets = include_once plugin_dir_path( __FILE__ ) . '/build/blocks.asset.php';
	wp_enqueue_script( 'jw-discord-sso-blocks', plugins_url( 'build/blocks.js', __FILE__ ), $assets['dependencies'], $assets['version'] );
} );

Authentication::get_instance()->hooks();
Settings::get_instance()->hooks();