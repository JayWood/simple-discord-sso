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
