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

// For testing only until settings page is made, will be removed.
const CLIENT_ID = '927005861921050624';
const CLIENT_SECRET = 'n9ewa8r7TlQ0eBOAE0HNndEhhQ7LNeR9';

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

add_action( 'enqueue_block_editor_assets', function() {
    $assets = include_once plugin_dir_path( __FILE__ ) . '/build/index.asset.php';
    wp_enqueue_script( 'jw-discord-sso', plugins_url( 'build/index.js', __FILE__ ), $assets['dependencies'], $assets['version'] );
} );

//add_action( 'plugins_loaded', function() {
	User::get_instance()->hooks();
//} );