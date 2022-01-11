<?php
/**
 * Plugin Name: Discord SSO
 * Plugin URI: https://plugish.com
 * Description: Allow discord users to sign in to your website using discord.
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

/**
 * Enqueues up the scripts required for front-end operations.
 */
function enqueue_scripts(): void {
	$assets = include_once plugin_dir_path( __FILE__ ) . '/build/frontend.asset.php';
	wp_register_script( 'jw-discord-front-end', plugins_url( 'build/frontend.js',  __FILE__ ), $assets['dependencies'], $assets['version'], true );
	wp_localize_script( 'jw-discord-front-end', 'jwDiscord', [
		'button' => [
			'bgColor'         => 'blurple',
			'logoColor'       => 'white',
			'discordAuthLink' => home_url( '/discord-login/' ),
			'logoBaseUrl'     => plugins_url( '/src/assets/Discord/img/', __FILE__ ),
			'AltText'         => __( 'Discord Login', 'jw-discord-sso' ),
		]
	] );

	wp_enqueue_script( 'jw-discord-front-end' );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );
add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

Authentication::get_instance()->hooks();
Settings::get_instance()->hooks();