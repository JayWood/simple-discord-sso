<?php
namespace com\plugish\discord\sso\app;

use const com\plugish\discord\sso\PLUGIN_FILE;

class Settings {
	/**
	 * Instance of the settings object.
	 *
	 * @var Settings
	 */
	private static $instance;

	const GROUP = 'jw_discord';

	/**
	 * Singleton instance getter.
	 *
	 * @return Settings
	 */
	public static function get_instance(): Settings {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Runs the hooks for the class.
	 */
	public function hooks(): void {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu'] );
	}

	/**
	 * Creates the menu.
	 */
	public function admin_menu(): void {
		add_menu_page( __( 'Discord SSO Options', 'jw-discord-sso' ), __( 'Discord SSO', 'jw-discord-sso' ), 'manage_options', 'discord', [ $this, 'render_settings' ] );
	}

	/**
	 * Renders the settings page.
	 */
	public function render_settings() {

	}

	/**
	 * Runs hooks for admin_init action.
	 */
	public function admin_init(): void {
		register_setting( self::GROUP, 'jw_discord_settings', [
			'type'              => 'array',
			'description'       => __( 'Settings for Discord Single Sign-On', 'jw-discord-sso' ),
			'show_in_rest'      => [
				'schema' => [
					'items' => [
						'type' => 'string',
					]
				]
			],
			'sanitize_callback' => [ $this, 'sanitize_settings' ]
		] );
	}

	/**
	 * Sanitizes the settings array.
	 *
	 * @param array $settings The settings array.
	 *
	 * @return array
	 */
	public function sanitize_settings( array $settings ): array {
		// Force specific keys only.
		$settings = array_intersect_key( $settings, array_flip( [ 'key', 'secret' ] ) );
		array_walk( $settings, 'sanitize_text_field' );
		return $settings;
	}
}