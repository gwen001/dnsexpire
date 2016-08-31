#!/usr/bin/php
<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

function __autoload( $c ) {
	include( $c.'.php' );
}


set_time_limit( 0 );


// parse command line
{
	$dnsexpire = new DnsExpire();

	$argc = $_SERVER['argc'] - 1;

	for ($i = 1; $i <= $argc; $i++) {
		switch ($_SERVER['argv'][$i]) {
			case '-a':
				$dnsexpire->setAlert( (int)$_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '-f':
				$dnsexpire->setDomain( $_SERVER['argv'][$i + 1] );
				$i++;
				break;

			case '-h':
				Utils::help();
				break;

			default:
				Utils::help('Unknown option: '.$_SERVER['argv'][$i]);
		}
	}

	if( !$dnsexpire->getDomain() ) {
		Utils::help('Domain not found!');
	}
}
// ---


// main loop
{
	$cnt_host = $dnsexpire->run();

	if( $cnt_host ) {
		$dnsexpire->printResult();
	}
}
// ---


exit();

?>
