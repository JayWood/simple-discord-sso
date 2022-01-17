<?php

use com\plugish\discord\sso\app\Authentication;

beforeEach( function() {
	$this->discord = Mockery::mock( '\com\plugish\discord\sso\lib\Discord', [ 'key', 'secret' ] );
	$this->class   = new Authentication( $this->discord );
} );

it( 'should add the required hooks', function () {
	Brain\Monkey\Actions\expectAdded( 'template_redirect' )
		->once()
		->with( [ $this->class, 'watch_for_discord' ], 11 );

	Brain\Monkey\Actions\expectAdded( 'init' )
		->once()
		->with( [ $this->class, 'rewrite' ], 11 );

	Brain\Monkey\Filters\expectAdded( 'query_vars' )
		->once()
		->with( [ $this->class, 'add_discord_var' ], 10 );

	Brain\Monkey\Actions\expectAdded( 'template_redirect' )
		->once()
		->with( [ $this->class, 'watch_authorize_link' ] );

	$this->class->hooks();
} );

it( 'should redirect the user to the auth URL if the query var is present', function() {
	Brain\Monkey\Functions\expect( 'get_query_var' )
		->once()
		->with( 'discord' )
		->andReturn( true );

	$this->discord->shouldReceive( 'get_auth_url' )
		->andReturn( 'someauthurl' );

	// Throwing an exception allows us to check that the redirect would have fired.
	Brain\Monkey\Functions\expect( 'wp_redirect' )
		->once()
		->with( 'someauthurl' )
		->andThrow( new Exception( 'redirect completed' ) );

	$this->class->watch_authorize_link();
} )->throws( 'Exception', 'redirect completed' );

it( 'should not redirect the user if the query var is not present', function() {

	Brain\Monkey\Functions\expect( 'get_query_var' )
		->once()
		->with( 'discord' )
		->andReturn( false );

	$this->discord->shouldReceive( 'get_auth_url' )
	              ->andReturn( 'someauthurl' );

	// Throwing an exception allows us to check that the redirect would have fired.
	Brain\Monkey\Functions\expect( 'wp_redirect' )
		->never();

	$this->class->watch_authorize_link();
} );

it( 'adds the discord variable', function() {
	$result = $this->class->add_discord_var( [ 'old' ] );
	expect( $result )->toBe( [ 'old', 'discord' ] );
} );

it( 'should add the rewrite rule', function() {
	Brain\Monkey\Functions\expect( 'add_rewrite_rule' )
		->once()
		->with( 'discord-login/?$', 'index.php?discord=1', 'top' );

	$this->class->rewrite();
} );

it( 'should not do anything if the referrer is not discord', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'junk' );

	$this->discord->shouldNotReceive( 'authorize' );

	$this->class->watch_for_discord();
} );

it( 'should fire an action if the referrer is discord and gets an error', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'https://discord.com/' );

	$_GET['error'] = true;

	$this->discord->shouldNotReceive( 'authorize' );
	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/error' );

	$this->class->watch_for_discord();

	unset( $_GET['error'] );
} );

it( 'should fire an action if authorization is not possible ', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'https://discord.com/' );

	$_GET['state'] = 'state';
	$_GET['code'] = 'code';

	$this->discord->shouldReceive( 'authorize' )
	              ->with( 'code', 'state' )
	              ->andReturnNull();

	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/auth_error' );

	$this->discord->shouldNotReceive( 'get_user_data' );

	$this->class->watch_for_discord();
} );

it( 'should fire an action if data is empty', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'https://discord.com/' );

	$_GET['state'] = 'state';
	$_GET['code'] = 'code';

	$this->discord->shouldReceive( 'authorize' )
      ->with( 'code', 'state' )
      ->andReturn(
        [
            'access_token' => 'token',
        ]
      );

	$this->discord->shouldReceive( 'get_user_data' )
		->once()
		->with( 'token' )
		->andReturnNull();

	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/user_error', [ 'access_token' => 'token' ] );

	$this->class->watch_for_discord();
} );

it( 'should throw an action if the user object is a wp_error', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'https://discord.com/' );

	$instance = Mockery::mock( 'com\plugish\discord\sso\app\Authentication[create_user]', [$this->discord] );

	$_GET['state'] = 'state';
	$_GET['code'] = 'code';

	$response = [
		'access_token' => 'token',
	];

	$user = [
		'email'         => 'email@example.com',
		'id'            => 'id',
		'avatar'        => 'avatar',
		'discriminator' => 'discriminator',
		'public_flags'  => 'public_flags',
		'flags'         => 'flags',
		'banner'        => 'banner',
		'accent_color'  => 'accent_color',
		'locale'        => 'locale',
		'mfa_enabled'   => false,
		'premium_type'  => 'premium_type',
		'verified'      => true,
	];

	$this->discord->shouldReceive( 'authorize' )
	              ->with( 'code', 'state' )
	              ->andReturn(
		              $response
	              );

	$this->discord->shouldReceive( 'get_user_data' )
	              ->once()
	              ->with( 'token' )
	              ->andReturn( $user );

	Brain\Monkey\Functions\expect( 'get_user_by' )
		->once()
		->with( 'email', 'email@example.com' )
		->andReturnNull();

	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/pre_login_create_user', $response, $user, $instance );

	$instance->shouldReceive( 'create_user' )
	         ->with( $user )
	         ->andReturn( 'nailed it' ); // Return isn't too strict. Method is tested elsewhere.

	Brain\Monkey\Functions\expect( 'is_wp_error' )
		->once()
		->with( 'nailed it' ) // Value isn't important.
		->andReturn( true );

	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/post_login_create_user', 'nailed it', $response, $user, $instance );

	Brain\Monkey\Functions\expect( 'do_action' )
		->with( 'simple_discord_sso/user_creation_error', 'nailed it', $response, $user, $instance );

	$instance->watch_for_discord();
} );

it( 'should log the user in after they were created', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'https://discord.com/' );

	$instance = Mockery::mock( 'com\plugish\discord\sso\app\Authentication[create_user]', [$this->discord] );

	$_GET['state'] = 'state';
	$_GET['code'] = 'code';

	$response = [
		'access_token' => 'token',
	];

	$user = [
		'email'         => 'email@example.com',
		'id'            => 'id',
		'avatar'        => 'avatar',
		'discriminator' => 'discriminator',
		'public_flags'  => 'public_flags',
		'flags'         => 'flags',
		'banner'        => 'banner',
		'accent_color'  => 'accent_color',
		'locale'        => 'locale',
		'mfa_enabled'   => false,
		'premium_type'  => 'premium_type',
		'verified'      => true,
	];

	$this->discord->shouldReceive( 'authorize' )
	              ->with( 'code', 'state' )
	              ->andReturn(
		              $response
	              );

	$this->discord->shouldReceive( 'get_user_data' )
	              ->once()
	              ->with( 'token' )
	              ->andReturn( $user );

	Brain\Monkey\Functions\expect( 'do_action' )
		->with( 'simple_discord_sso/pre_login_user', $response, $user, $instance );

	Brain\Monkey\Functions\expect( 'get_user_by' )
		->once()
		->with( 'email', 'email@example.com' )
		->andReturnNull();

	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/pre_login_create_user', $response, $user, $instance );

	$wp_user = Mockery::mock( 'WP_User' );
	$wp_user->ID = 1;

	$instance->shouldReceive( 'create_user' )
	         ->with( $user )
	         ->andReturn( $wp_user ); // Return isn't too strict. Method is tested elsewhere.

	Brain\Monkey\Functions\expect( 'is_wp_error' )
		->once()
		->with( $wp_user ) // Value isn't important.
		->andReturn( false );

	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/post_login_create_user', $wp_user, $response, $user, $instance );

	Brain\Monkey\Functions\expect( 'wp_clear_auth_cookie' )->once();
	Brain\Monkey\Functions\expect( 'wp_set_current_user' )
		->with( 1 );
	Brain\Monkey\Functions\expect( 'wp_set_auth_cookie' )
		->with( 1 );
	Brain\Monkey\Functions\expect( 'home_url' )
		->once()->andReturn( 'homeurl' );
	Brain\Monkey\Filters\expectApplied( 'simple_discord_sso/login_redirect')
		->with( 'homeurl', $wp_user );
	Brain\Monkey\Functions\expect( 'do_action' )
		->with( 'simple_discord_sso/post_login_user', $wp_user, $response, $user, $instance );

	Brain\Monkey\Functions\expect( 'wp_safe_redirect' )
		->with( 'homeurl' )
		->andThrow( new Exception( 'Redirect fired' ) );

	$instance->watch_for_discord();
} )->throws( 'Exception', 'Redirect fired' );

it( 'should update the user hash if the user already exists', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'https://discord.com/' );

	$instance = Mockery::mock( 'com\plugish\discord\sso\app\Authentication[create_user]', [$this->discord] );

	$_GET['state'] = 'state';
	$_GET['code'] = 'code';

	$response = [
		'access_token' => 'token',
	];

	$user = [
		'email'         => 'email@example.com',
		'id'            => 'id',
		'avatar'        => 'avatar',
		'discriminator' => 'discriminator',
		'public_flags'  => 'public_flags',
		'flags'         => 'flags',
		'banner'        => 'banner',
		'accent_color'  => 'accent_color',
		'locale'        => 'locale',
		'mfa_enabled'   => false,
		'premium_type'  => 'premium_type',
		'verified'      => true,
	];

	$this->discord->shouldReceive( 'authorize' )
	              ->with( 'code', 'state' )
	              ->andReturn(
		              $response
	              );

	$this->discord->shouldReceive( 'get_user_data' )
	              ->once()
	              ->with( 'token' )
	              ->andReturn( $user );

	$wp_user = Mockery::mock( 'WP_User' );
	$wp_user->ID = 1;

	Brain\Monkey\Functions\expect( 'do_action' )
		->with( 'simple_discord_sso/pre_login_user', $response, $user, $instance );

	Brain\Monkey\Functions\expect( 'get_user_by' )
		->once()
		->with( 'email', 'email@example.com' )
		->andReturn( $wp_user );

	Brain\Monkey\Functions\when( 'sanitize_text_field' )->returnArg();

	Brain\Monkey\Functions\expect( 'is_wp_error' )
		->once()
		->with( $wp_user ) // Value isn't important.
		->andReturn( false );

	Brain\Monkey\Functions\expect( 'get_user_meta' )
		->once()
		->with( 1, 'simple_discord_sso', true )
		->andReturn( $user );

	$expected_meta = $user;
	$expected_meta['hash'] = md5( json_encode( $user ) );

	// Since the data does not have a hash, it should run an update user meta call.
	Brain\Monkey\Functions\expect( 'update_user_meta' )
		->with( 1, 'simple_discord_sso', true )
		->andReturn( $expected_meta );

	Brain\Monkey\Functions\expect( 'wp_clear_auth_cookie' )->once();

	Brain\Monkey\Functions\expect( 'wp_set_current_user' )
		->with( 1 );
	Brain\Monkey\Functions\expect( 'wp_set_auth_cookie' )
		->with( 1 );
	Brain\Monkey\Functions\expect( 'home_url' )
		->once()->andReturn( 'homeurl' );
	Brain\Monkey\Filters\expectApplied( 'simple_discord_sso/login_redirect')
		->with( 'homeurl', $wp_user );
	Brain\Monkey\Functions\expect( 'do_action' )
		->with( 'simple_discord_sso/post_login_user', $wp_user, $response, $user, $instance );

	Brain\Monkey\Functions\expect( 'wp_safe_redirect' )
		->with( 'homeurl' )
		->andThrow( new Exception( 'Redirect fired' ) );

	$instance->watch_for_discord();
} )->throws( 'Exception', 'Redirect fired' );

it( 'should return a WP_Error if one is acquired during user creation', function() {
	$user = [
		'email'         => 'email@example.com',
		'username'      => 'something',
		'id'            => 'id',
		'avatar'        => 'avatar',
		'discriminator' => '123',
		'public_flags'  => 'public_flags',
		'flags'         => 'flags',
		'banner'        => 'banner',
		'accent_color'  => 'accent_color',
		'locale'        => 'locale',
		'mfa_enabled'   => false,
		'premium_type'  => 'premium_type',
		'verified'      => true,
	];

	Brain\Monkey\Functions\when( 'sanitize_text_field' )
		->returnArg();

	Brain\Monkey\Functions\when( 'sanitize_email' )
		->returnArg();

	Brain\Monkey\Functions\when( 'wp_generate_password' )
		->justReturn( 'password' );

	Brain\Monkey\Functions\expect( 'apply_filters' )
		->with( 'simple_discord_sso', 'subscriber', $user )
		->andReturn( 'subscriber' );

	$wp_error = Mockery::mock( 'WP_Error' );
	Brain\Monkey\Functions\expect( 'wp_insert_user' )
		->with( [
			'user_login' => 'something123',
			'user_email' => 'email@example.com',
			'user_pass'  => 'password',
			'role'       => 'subscriber',
		] )
		->andReturn( $wp_error );

	Brain\Monkey\Functions\expect( 'is_wp_error' )
		->with( $wp_error )
		->andReturn( true );

	$user = $this->class->create_user( $user );
	expect( $user )->toBe( $wp_error );
} );

it( 'should create the user and set the meta', function() {
	$user = [
		'email'         => 'email@example.com',
		'username'      => 'something',
		'id'            => 'id',
		'avatar'        => 'avatar',
		'discriminator' => '123',
		'public_flags'  => 'public_flags',
		'flags'         => 'flags',
		'banner'        => 'banner',
		'accent_color'  => 'accent_color',
		'locale'        => 'locale',
		'mfa_enabled'   => false,
		'premium_type'  => 'premium_type',
		'verified'      => true,
	];

	Brain\Monkey\Functions\when( 'sanitize_text_field' )
		->returnArg();

	Brain\Monkey\Functions\when( 'sanitize_email' )
		->returnArg();

	Brain\Monkey\Functions\when( 'wp_generate_password' )
		->justReturn( 'password' );

	Brain\Monkey\Functions\expect( 'apply_filters' )
		->with( 'simple_discord_sso', 'subscriber', $user )
		->andReturn( 'subscriber' );

	$wp_user = Mockery::mock( 'WP_User' );
	Brain\Monkey\Functions\expect( 'wp_insert_user' )
		->with( [
			'user_login' => 'something123',
			'user_email' => 'email@example.com',
			'user_pass'  => 'password',
			'role'       => 'subscriber',
		] )
		->andReturn( 1 );

	Brain\Monkey\Functions\expect( 'is_wp_error' )
		->with( $wp_user )
		->andReturn( false );

	$meta = $user;
	unset( $meta['username'], $meta['email'] );
	$meta['hash'] = md5( json_encode( $meta ) );

	Brain\Monkey\Functions\expect( 'update_user_meta' )
		->with( 1, 'simple_discord_sso', $meta );

	Brain\Monkey\Functions\expect( 'get_userdata' )
		->with( 1 )
		->andReturn( $wp_user );

	$user = $this->class->create_user( $user );
	expect( $user )->toBe( $wp_user );
} );
