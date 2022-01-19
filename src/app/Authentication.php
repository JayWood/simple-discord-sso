<?php
namespace com\plugish\discord\sso\app;

use com\plugish\discord\sso\lib\Discord;

class Authentication {
	private Discord $discord;

	/**
	 * Used for updating permalinks.
	 */
	const REWRITE_VERSION = '1.2';

	/**
	 * Instance of the user object.
	 *
	 * @var Authentication
	 */
	private static $instance;

	/**
	 * Authentication constructor.
	 *
	 * @param Discord $discord Instance of the discord class.
	 */
	public function __construct( Discord $discord ) {
		$this->discord = $discord;
	}

	/**
	 * Singleton instance getter.
	 *
	 * @param Discord $discord Instance of the discord class.
	 * @return Authentication
	 */
	public static function get_instance( Discord $discord ): Authentication {
		if ( ! self::$instance ) {
			self::$instance = new self( $discord );
		}

		return self::$instance;
	}

	/**
	 * Handles all the hooks.
	 */
	public function hooks(): void {
		add_action( 'template_redirect', [ $this, 'watch_for_discord' ], 11 );
		add_action( 'init', [ $this, 'rewrite' ], 11 );
		add_filter( 'query_vars', [ $this, 'add_discord_var' ], 10 );
		add_action( 'template_redirect', [ $this, 'watch_authorize_link' ] );
	}

	/**
	 * Watches for an incoming query variable to forcefully redirect the user.
	 */
	public function watch_authorize_link(): void {
		if ( ! empty( get_query_var( 'discord' ) ) ) {
			wp_redirect( $this->discord->get_auth_url() );
			exit();
		}
	}

	/**
	 * Adds the query variable which is required for the rewrite rule to work.
	 *
	 * @param array $vars The array of variables.
	 *
	 * @return mixed
	 */
	public function add_discord_var( array $vars ): array {
		$vars[] = 'discord';
		return $vars;
	}

	/**
	 * Adds a rewrite rule for discord logins.
	 */
	public function rewrite(): void {
		add_rewrite_rule( 'discord-login/?$', 'index.php?discord=1', 'top' );

		$permalink_version = get_option( 'simple_discord_permalinks', '1.0' );
		if ( self::REWRITE_VERSION !== $permalink_version ) {
			flush_rewrite_rules( false );
			update_option( 'simple_discord_permalinks', self::REWRITE_VERSION );
		}
	}

	/**
	 * Watches for incoming discord stuffs. Specific, I know right?
	 */
	public function watch_for_discord(): void {
		$referrer = wp_get_raw_referer();
		// This is not a discord request, bail.
		if ( 'https://discord.com/' !== $referrer ) {
			return;
		}

		if ( isset( $_GET['error'] ) ) {
			// Do nothing for now;
			do_action( 'simple_discord_sso/error' );
			return;
		}

		if ( empty( $_GET['code'] ) ) {
			return;
		}

		$this->login_user();
	}

	/**
	 * Creates a user based on discord data.
	 *
	 * @param array $discord_user An array of discord user data.
	 *
	 * @link https://discord.com/developers/docs/resources/user
	 *
	 * @return \WP_User|\WP_Error WP user on success, WP_Error otherwise.
	 */
	public function create_user( array $discord_user ) {
		$args = [
			'user_login' => sanitize_text_field( $discord_user['username'] . $discord_user['discriminator'] ),
			'user_email' => sanitize_email( $discord_user['email'] ),
			'user_pass'  => wp_generate_password(),
			'role'       => apply_filters( 'simple_discord_sso/default_role', 'subscriber', $discord_user ),
		];

		$inserted_user_id = wp_insert_user( $args );
		if ( is_wp_error( $inserted_user_id ) ) {
			return $inserted_user_id;
		}

		$meta = $this->create_meta_array_for_user( $discord_user );

		update_user_meta( $inserted_user_id, 'simple_discord_sso', $meta );

		return get_userdata( $inserted_user_id );
	}


	/**
	 * Creates a discord user array for storing in meta.
	 *
	 * @param array $discord_user The discord user array.
	 *
	 * @return array
	 */
	private function create_meta_array_for_user( array $discord_user ): array {
		$meta = [
			'id'            => sanitize_text_field( $discord_user['id'] ?? '' ),
			'avatar'        => sanitize_text_field( $discord_user['avatar'] ?? '' ),
			'discriminator' => sanitize_text_field( $discord_user['discriminator'] ?? '' ),
			'public_flags'  => sanitize_text_field( $discord_user['public_flags'] ?? '' ),
			'flags'         => sanitize_text_field( $discord_user['flags'] ?? '' ),
			'banner'        => sanitize_text_field( $discord_user['banner'] ?? '' ),
			'accent_color'  => sanitize_text_field( $discord_user['accent_color'] ?? '' ),
			'locale'        => sanitize_text_field( $discord_user['locale'] ?? '' ),
			'mfa_enabled'   => boolval( $discord_user['mfa_enabled'] ?? false ),
			'premium_type'  => sanitize_text_field( $discord_user['premium_type'] ?? '' ),
			'verified'      => boolval( $discord_user['verified'] ?? '' ),
		];

		// Store a hash of the meta for later, just in case it changes on user login we can
		// then import the new values.
		$meta['hash'] = md5( json_encode( $meta ) );
		return $meta;
	}

	/**
	 * Logs the user in.
	 */
	private function login_user(): void {
		$response = $this->discord->authorize( $_GET['code'], $_GET['state'] );
		if ( ! $response ) {
			do_action( 'simple_discord_sso/auth_error' );
			return;
		}

		$discord_user = $this->discord->get_user_data( $response['access_token'] );
		if ( empty( $discord_user ) ) {
			do_action( 'simple_discord_sso/user_error', $response );
			return; // Do nothing for now.
		}

		do_action( 'simple_discord_sso/pre_login_user', $response, $discord_user, $this );

		$user = get_user_by( 'email', $discord_user['email'] );
		if ( ! $user ) {
			// Create the user
			do_action( 'simple_discord_sso/pre_login_create_user', $response, $discord_user, $this );
			$user = $this->create_user( $discord_user );
			do_action( 'simple_discord_sso/post_login_create_user', $user, $response, $discord_user, $this );
		} else {
			$new_meta = $this->create_meta_array_for_user( $discord_user );
			$old_meta = get_user_meta( $user->ID, 'simple_discord_sso', true );
			if ( empty( $old_meta['hash'] ) || $new_meta['hash'] !== $old_meta['hash'] ) {
				update_user_meta( $user->ID, 'simple_discord_sso', $new_meta );
			}
		}

		if ( is_wp_error( $user ) ) {
			do_action( 'simple_discord_sso/user_creation_error', $user, $response, $discord_user, $this );
			return;
		}

		wp_clear_auth_cookie();
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID );

		$redirect_url = apply_filters( 'simple_discord_sso/login_redirect', home_url(), $user );

		// A hook just before the redirect, in case anyone wants to do anything here.
		do_action( 'simple_discord_sso/post_login_user', $user, $response, $discord_user, $this );

		wp_safe_redirect( $redirect_url );
		exit();
	}

}
