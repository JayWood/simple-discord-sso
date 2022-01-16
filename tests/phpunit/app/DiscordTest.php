<?php

beforeEach( function() {
	$this->class = new \com\plugish\discord\sso\lib\Discord( 'id', 'key' );
} );

it( 'should get the authorization URL', function() {

	$expected_args = [
		'client_id'     => 'id',
		'redirect_uri'  => 'homeurl',
		'response_type' => 'code',
		'scope'         => 'identify email',
		'state'         => 'nonce_value',
		'prompt'        => 'none',
	];

	Brain\Monkey\Functions\when( 'get_home_url' )
		->justReturn( 'homeurl' );

	Brain\Monkey\Functions\expect( 'apply_filters' )
		->with( 'simple_discord_sso/redirect_url', 'homeurl' )
		->andReturn( 'homeurl' );

	Brain\Monkey\Functions\expect( 'apply_filters' )
		->with( 'simple_discord_sso/scopes', 'identify email' )
		->andReturn( 'identify email' );

	Brain\Monkey\Functions\expect( 'add_query_arg' )
		->with( $expected_args, 'https://discord.com/api/oauth2/authorize' )
		->andReturn( 'altered-url' );

	Brain\Monkey\Functions\expect( 'wp_create_nonce' )
		->with( 'discord-auth' )
		->andReturn( 'nonce_value' );

	$result = $this->class->get_auth_url();
	expect( $result )->toBe( 'altered-url' );
} );

it( 'should not authorize if nonce is not verified', function() {
	Brain\Monkey\Functions\expect( 'wp_verify_nonce' )
		->with( 'state', 'code' )
		->andReturnNull();

	$result = $this->class->authorize( 'code', 'state' );
	expect( $result )->toBeNull();
} );

it( 'should return null if there is no access token available', function() {
	Brain\Monkey\Functions\expect( 'wp_verify_nonce' )
		->with( 'state', 'code' )
		->andReturn(true);

	Brain\Monkey\Functions\when( 'get_home_url' )
		->justReturn( 'homeurl' );

	Brain\Monkey\Functions\expect( 'apply_filters' )
		->with( 'simple_discord_sso/redirect_url', 'homeurl' )
		->andReturn( 'homeurl' );

	Brain\Monkey\Functions\expect( 'wp_remote_post' )
		->with( [
			'https://discord.com/api/oauth2/token',
			[
				'headers' => [
					'Content-type' => 'application/x-www-form-urlencoded',
				],
				'body'    => [
					'grant_type'    => 'authorization_code',
					'client_id'     => 'id',
					'client_secret' => 'secret',
					'redirect_uri'  => 'homeurl',
					'code'          => 'code',
				],
			]
		] )
		->andReturn( 'post-result' );

	Brain\Monkey\Functions\expect( 'wp_remote_retrieve_body' )
		->with( 'post-result' )
		->andReturn( json_encode([]) );

	$result = $this->class->authorize( 'code', 'state' );
	expect( $result )->toBeNull();
} );


it( 'should return the response to be able to use the token', function() {
	Brain\Monkey\Functions\expect( 'wp_verify_nonce' )
		->with( 'state', 'code' )
		->andReturn(true);

	Brain\Monkey\Functions\when( 'get_home_url' )
		->justReturn( 'homeurl' );

	Brain\Monkey\Functions\expect( 'apply_filters' )
		->with( 'simple_discord_sso/redirect_url', 'homeurl' )
		->andReturn( 'homeurl' );

	Brain\Monkey\Functions\expect( 'wp_remote_post' )
		->with( [
			'https://discord.com/api/oauth2/token',
			[
				'headers' => [
					'Content-type' => 'application/x-www-form-urlencoded',
				],
				'body'    => [
					'grant_type'    => 'authorization_code',
					'client_id'     => 'id',
					'client_secret' => 'secret',
					'redirect_uri'  => 'homeurl',
					'code'          => 'code',
				],
			]
		] )
		->andReturn( 'post-result' );

	Brain\Monkey\Functions\expect( 'wp_remote_retrieve_body' )
		->with( 'post-result' )
		->andReturn( json_encode(['access_token'=>'token']) );

	$result = $this->class->authorize( 'code', 'state' );
	expect( $result )->toBe( ['access_token'=>'token'] );
} );

it( 'should return null if user data is not available using the token', function() {
	Brain\Monkey\Functions\expect( 'wp_remote_get' )
		->with(
			'https://discord.com/api/users/@me',
			[
				'headers' => [
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . 'suppliedToken',
				],
			]
		)
		->andReturn( 'empty_result' );

	Brain\Monkey\Functions\expect( 'wp_remote_retrieve_body' )
		->with( 'empty_result' )
		->andReturnNull();

	$result = $this->class->get_user_data( 'suppliedToken' );
	expect( $result )->toBeNull();
} );

it( 'should return null if user data is missing the required keys for data processing', function() {
	Brain\Monkey\Functions\expect( 'wp_remote_get' )
		->with(
			'https://discord.com/api/users/@me',
			[
				'headers' => [
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . 'suppliedToken',
				],
			]
		)
		->andReturn( 'empty_result' );

	Brain\Monkey\Functions\expect( 'wp_remote_retrieve_body' )
		->with( 'empty_result' )
		->andReturn( json_encode( [ 'some-keys' => 'some-value' ] ) );

	$result = $this->class->get_user_data( 'suppliedToken' );
	expect( $result )->toBeNull();
} );

it( 'should return the user data', function() {
	Brain\Monkey\Functions\expect( 'wp_remote_get' )
		->with(
			'https://discord.com/api/users/@me',
			[
				'headers' => [
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . 'suppliedToken',
				],
			]
		)
		->andReturn( 'empty_result' );

	$expected = [
		'username'      => 'user',
		'discriminator' => '1234',
		'email'         => 'something',
	];

	Brain\Monkey\Functions\expect( 'wp_remote_retrieve_body' )
		->with( 'empty_result' )
		->andReturn( json_encode( $expected ) );

	$result = $this->class->get_user_data( 'suppliedToken' );
	expect( $result )->toBe( $expected );
} );
