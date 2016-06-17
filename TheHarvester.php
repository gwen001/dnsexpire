<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class TheHarvester
{
	const SE_ENGINE = 'all';
	const SE_LIMIT = 100;

	private $domain = null;
	private $_domain = null;

	//private $tempfile = null;

	private $result = null;

	private $r_email = array();
	private $r_host = array();

	//private $allowed_tags = array( '<theHarvester>', '<email>', '<host>', '<vhost>' );


	public function __construct()
	{
		exec( 'whereis theharvester', $exec );
		$tmp = explode( ' ', $exec[0] );

		if( count($tmp) <= 1 ) {
			throw new Exception( 'TheHarvester not found!' );
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

		echo "TheHarvester is running...\n";
		exec( 'theharvester -b '.self::SE_ENGINE.' -l '.self::SE_LIMIT.' -d '.$this->domain, $this->result );
		//var_dump( $this->result );

		$this->parseTable();
	}


	private function parseTable()
	{
		if( !$this->result ) {
			return false;
		}

		foreach( $this->result as $k=>$l ) {
			$l = str_replace( '<<', '<', $l );
			$l = strip_tags( $l );
			$this->result[$k] = $l;
		}

		$this->parseEmailTable();
		$this->parseHostTable();
	}


	private function parseEmailTable()
	{
		foreach( $this->result as $l ) {
			if( Utils::isEmail($l) && stristr($l,$this->_domain) ) {
				$this->r_email[] = $l;
			}
		}
	}


	private function parseHostTable()
	{
		foreach( $this->result as $l ) {
			if( preg_match('#[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+[:|\s]+(.*)#',$l,$m) && stristr($l,$this->_domain) ) {
				$this->r_host[] = $m[1];
			}
		}

		$this->r_host = array_unique( $this->r_host );
	}


	// TheHarvester seems to meet problems to record xml file sometimes
	/*
		public function runXml()
		{
			if( !$this->domain ) {
				return false;
			}

			if( !($f=Utils::createTempFile()) ) {
				return false;
			}

			//$f = '/tmp/dns56b3354d10366.xml';
			//$f = '/tmp/dns56b3531ce15be.xml';
			$this->tempfile = $f;

			passthru( 'theharvester -b '.self::SE_ENGINE.' -l '.self::SE_LIMIT.' -d '.$this->domain.' -f '.$this->tempfile );

			$buff = file_get_contents( $this->tempfile );
			$buff = str_replace( '<<', '<', $buff );
			$buff = strip_tags( $buff, implode('',$this->allowed_tags) );
			//var_dump( $buff );

			$this->result = simplexml_load_string( $buff );
			$this->parseXml();
		}


		private function parseXml()
		{
			if( !$this->result ) {
				return false;
			}

			$this->parseEmailXml();
			$this->parseHostXml();
		}


		private function parseEmailXml()
		{
			foreach( $this->result->email as $o ) {
				$e = (string)$o;
				if( Utils::isEmail($e) && stristr($e,$this->_domain) ) {
					$this->r_email[] = $e;
				}
			}
		}


		private function parseHostXml()
		{
			foreach( $this->result->host as $o ) {
				$h = (string)$o;
				if( stristr($h,$this->_domain) ) {
					$this->r_host[] = $h;
				}
			}

			foreach( $this->result->vhost as $o ) {
				$tmp = explode( ':', (string)$o );
				if( stristr($tmp[1],$this->_domain) ) {
					$this->r_host[] = $tmp[1];
				}
			}

			$this->r_host = array_unique( $this->r_host );
		}
		*/


	public function rEmail() {
		return $this->r_email;
	}
	public function rHost() {
		return $this->r_host;
	}
}

?>
