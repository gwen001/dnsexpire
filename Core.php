<?php

class Core
{
	private $domain = null;
	private $r_email = array();
	private $r_host = array();


	public function setDomain( $v ) {
		$this->domain = trim( $v );
	}


	public function proceed()
	{
		$th = new TheHarvester();
		$th->setDomain( $this->domain );
		// TheHarvester seems to meet problems to record xml file sometimes
		//$th->runXml();
		$th->runTable();

		$r_email = array_merge( $this->r_email, $th->rEmail() );
		$r_host = $th->rHost();

		foreach( $r_email as $e ) {
			$this->computeEmail( $e );
		}

		foreach( $r_host as $h ) {
			$this->computeHost( $h );
		}
	}


	private function computeEmail( $email )
	{
		$this->r_email[] = $email;
	}


	private function computeHost( $host, $son=null )
	{
		if( isset($this->r_host[$host]) ) {
			return $this->r_host[$host];
		}

		exec( 'host '.$host, $tmp );
		$tmp = implode( "\n", $tmp );

		preg_match( '#.* has address (.*)#i', $tmp, $matches );
		//var_dump($matches);

		if( !count($matches) ) {
			return false;
		}

		$o = new Host();
		$o->setHost( $host );
		$o->setIp( $matches[1] );

		if( $son ) {
			//$son->setParent( $o );
			//$o->setParent( $son );
			$o->addAlias( $son );
		}

		preg_match( '#(.*) is an alias for (.*)#i', $tmp, $matches );
		//var_dump($matches);

		if( count($matches) )  {
			// this host is an alias
			$o->setAlias( true );
			$gg = $this->computeHost( trim($matches[2],'.'), $o );
			$o->setParent( $gg );
		}

		$this->addHost( $o );

		return $o;
	}


	private function addHost( $o ) {
		$this->r_host[ $o->getHost() ] = $o;
	}


	public function rEmail() {
		return $this->r_email;
	}
	public function rHost() {
		return $this->r_host;
	}
}

?>
