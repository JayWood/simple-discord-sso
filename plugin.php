<?php
/**
 * Plugin Name: Simple Discord SSO
 * Plugin URI: https://github.com/JayWood/simple-discord-sso
 * Description: Allow discord users to sign in to your website using discord.
 * Author: JayWood
 * Author URI: https://plugish.com/
 * Version: 1.0.2
 */

namespace com\plugish\discord\sso;

use com\plugish\discord\sso\app\Authentication;
use com\plugish\discord\sso\app\Settings;
use com\plugish\discord\sso\lib\Discord;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Includes a file from /src/views if it is available.
 *
 * @param string $file The view file.
 */
function get_view( string $file ): void {
	$file = __DIR__ . '/src/views/' . $file;
	if ( file_exists( $file ) ) {
		include $file;
	}
}

/**
 * Enqueues up the scripts required for front-end operations.
 */
function enqueue_scripts(): void {
	$assets   = include_once plugin_dir_path( __FILE__ ) . '/build/frontend.asset.php';
	$settings = get_option( 'simple_discord_sso_settings' );
	wp_register_script( 'simple-discord-sso-front-end', plugins_url( 'build/frontend.js', __FILE__ ), $assets['dependencies'], $assets['version'], true );
	wp_localize_script(
		'simple-discord-sso-front-end',
		'simpleDiscordSettings',
		array(
			'button' => array(
				'bgColor'         => $settings['bgColor'] ?? 'blurple',
				'logoColor'       => $settings['logoColor'] ?? 'white',
				'discordAuthLink' => home_url( '/discord-login/' ),
				'logoBaseUrl'     => plugins_url( '/src/assets/Discord/img/', __FILE__ ),
				'AltText'         => __( 'Discord Login', 'simple-discord-sso' ),
			),
		)
	);

	wp_enqueue_script( 'simple-discord-sso-front-end' );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );
add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );


// Setup Discord.
$settings = get_option( 'simple_discord_sso_settings' );
$discord  = new Discord(
	esc_html( $settings['key'] ?? '' ),
	esc_html( $settings['secret'] ?? '' )
);

Authentication::get_instance( $discord )->hooks();
Settings::get_instance()->hooks();
