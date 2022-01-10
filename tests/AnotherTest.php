<?php

it( 'should include brainmonkey', function() {

	Brain\Monkey\Functions\expect( 'something' );
	echo 'failed';
	\PHPUnit\Framework\assertTrue( true );

} );