<?php

beforeEach( function() {
	$this->class = new \com\plugish\discord\sso\app\Settings();
} );

it( 'should add hooks', function() {
	Brain\Monkey\Actions\expectAdded( 'admin_init' )
		->once()
		->with( [ $this->class, 'admin_init' ] );

	Brain\Monkey\Actions\expectAdded( 'admin_menu' )
		->once()
		->with( [ $this->class, 'admin_menu' ] );

	$this->class->hooks();
} );

it( 'should add an admin menu item', function() {
	Brain\Monkey\Functions\expect( 'add_menu_page' )
		->once()
		->with(
			'Discord SSO Options',
			'Discord SSO',
			'manage_options',
			'discord',
			[ $this->class, 'render_settings' ]
		);

	$this->class->admin_menu();
} );

it( 'should register the settings', function() {
	Brain\Monkey\Functions\expect( 'register_setting' )
		->with( 'simple_discord_sso', 'simple_discord_sso_settings', Mockery::type( 'array' ) );

	$this->class->admin_init();
} );

it( 'should strip and sanitize only specific keys', function() {
	Brain\Monkey\Functions\when( 'sanitize_text_field' )
		->justReturn( 'sanitized-text' );

	$input = [
		'key' => 'someKey',
		'secret' => 'someSecret',
		'bgColor' => 'somecolor',
		'logoColor' => 'somecoloragain',
		'junk' => 'another junk key'
	];

	$result = $this->class->sanitize_settings( $input );

	expect( $result )->toBe( [
		'key' => 'sanitized-text',
		'secret' => 'sanitized-text',
		'bgColor' => 'sanitized-text',
		'logoColor' => 'sanitized-text',
	] );
} );
