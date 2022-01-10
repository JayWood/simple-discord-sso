<?php
Hamcrest\Util::registerGlobalFunctions();
uses( \com\plugish\Tests\TestCase::class)->in(__DIR__);

if ( ! function_exists( '__' ) ) {
	function __($value) {
		return $value;
	}
}