<?php
namespace com\plugish\discord\sso\lib;

class Discord {
	const API_USER  = 'https://discord.com/api/users/@me';
	const API_TOKEN = 'https://discord.com/api/oauth2/token';
	const API_AUTH  = 'https://discord.com/api/oauth2/authorize';
	const STATE     = 'discord-auth';

	/**
	 * Discord keys.
	 *
	 * @var string
	 */
	private string $client_id, $client_secret;

	/**
	 * Discord constructor.
	 *
	 * @param string $client_id The client ID for your app.
	 * @param string $client_secret The client Secret for your app.
	 */
	public function __construct( string $client_id, string $client_secret ) {
		$this->client_id     = $client_id;
		$this->client_secret = $client_secret;
	}

	/**
	 * Gets the redirect URL.
	 *
	 * @return string
	 */
	public function get_redirect_url(): string {
		return apply_filters( 'simple_discord_sso/redirect_url', get_home_url() );
	}

	/**
	 * Gets the authorization URL for logging in with Discord.
	 *
	 * @return string|null
	 */
	public function get_auth_url(): ?string {
		$args = [
			'client_id'     => $this->client_id,
			'redirect_uri'  => $this->get_redirect_url(),
			'response_type' => 'code',
			'scope'         => apply_filters( 'simple_discord_sso/scopes', 'identify email' ),
			'state'         => wp_create_nonce( self::STATE ),
			'prompt'        => 'none',
		];

		return add_query_arg( $args, self::API_AUTH );
	}

	/**
	 * Authorizes the user through the discord API.
	 *
	 * @param string $authorization_code The authorization code from the initial discord handshake.
	 * @param string $state The state string to verify against.
	 *
	 * @return array|null
	 */
	public function authorize( string $authorization_code, string $state ): ?array {
		if ( ! wp_verify_nonce( $state, self::STATE ) ) {
			return null;
		}

		$args = [
			'grant_type'    => 'authorization_code',
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'redirect_uri'  => $this->get_redirect_url(),
			'code'          => $authorization_code,
		];

		$result = wp_remote_post(
			'https://discord.com/api/oauth2/token',
			[
				'headers' => [
					'Content-type' => 'application/x-www-form-urlencoded',
				],
				'body'    => $args,
			]
		);

		$response = json_decode( wp_remote_retrieve_body( $result ), true );
		if ( ! $response || ! $response['access_token'] ) {
			return null;
		}

		return $response;
	}

	/**
	 * Gets the user data including email from discord.
	 *
	 * @param string $access_token The access token for the user.
	 *
	 * @return array|null
	 */
	public function get_user_data( string $access_token ): ?array {
		$user_request = wp_remote_get(
			self::API_USER,
			[
				'headers' => [
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				],
			]
		);

		$user_data = wp_remote_retrieve_body( $user_request );
		if ( empty( $user_data ) ) {
			return null;
		}

		$user_data = json_decode( $user_data, true );
		if ( empty( $user_data['username'] ) || empty( $user_data['discriminator'] ) || empty( $user_data['email'] ) ) {
			return null;
		}

		return $user_data;
	}

}
