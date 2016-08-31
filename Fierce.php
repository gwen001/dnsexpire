<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class Fierce
{
	private $domain = null;
	private $_domain = null;

	private $result = null;

	private $r_email = array();
	private $r_host = array();


	public function __construct()
	{
		exec( 'whereis fierce', $exec );
		$tmp = explode( ' ', $exec[0] );

		if( count($tmp) <= 1 ) {
			throw new Exception( 'Fierce not found!' );
		}
	}


	public function setDomain( $v ) {
		$this->domain = trim( $v );
		$tmp = explode( '.', $this->domain );
		$this->_domain = $tmp[0];
	}


	public function run()
	{
		if( !$this->domain ) {
			return false;
		}

		echo "Fierce is running...\n";
		exec( 'fierce -dns '.$this->domain.' -wordlist /tmp/null | grep "'.$this->domain.'\." | awk \'{print $1}\' | sort -fu', $this->result );
		//var_dump( $this->result );

		$this->r_host = array_map( function($str){ return trim($str,'.'); }, $this->result );
		//var_dump($this->r_host);
	}


	public function rEmail() {
		return $this->r_email;
	}
	public function rHost() {
		return $this->r_host;
	}
}

?>
