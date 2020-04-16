<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class WOE_Export {
	var $destination;

	public function __construct( $destination ) {
		$this->destination = $destination;
	}

	//must be imlemented
	abstract public function run_export( $filename, $filepath );
}
