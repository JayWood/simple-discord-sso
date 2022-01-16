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

it( 'should create a user if one does not exist', function() {
	Brain\Monkey\Functions\expect( 'wp_get_raw_referer' )
		->andReturn( 'https://discord.com/' );

	$_GET['state'] = 'state';
	$_GET['code'] = 'code';

	$user = [
		'id'            => 'id',
		'avatar'        => 'avatar',
		'discriminator' => 'discriminator',
		'public_flags'  => 'public_flags',
		'flags'         => 'flags',
		'banner'        => 'banner',
		'banner_color'  => 'banner_color',
		'accent_color'  => 'accent_color',
		'locale'        => 'locale',
		'mfa_enabled'   => false,
		'premium_type'  => 'premium_type',
		'verified'      => true,
	]

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
	              ->andReturn( $user );

	Brain\Monkey\Functions\expect( 'do_action' )
		->once()
		->with( 'simple_discord_sso/user_error', [ 'access_token' => 'token' ] );

	$this->class->watch_for_discord();
} );
