<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class DnsExpire
{
	const DEFAULT_ALERT = 60*60*24*30; // 30 days

	private $domain = null;

	private $alert = self::DEFAULT_ALERT;

	private $input_file = null;

	private $r_host = array();

	private $r_email = array();

	private $t_expire = array();

	private $t_expire_string = array('Expiry date:', 'Expiration Date:', 'free-date:');


	public function getDomain() {
		return $this->domain;
	}
	public function setDomain( $v ) {
		$this->domain = trim( $v );
		return true;
	}


	public function rEmail() {
		return $this->r_email;
	}


	public function rHost() {
		return $this->r_host;
	}


	public function setAlert( $v ) {
		$v = (int)$v;
		if( $v > 0 ) {
			$this->alert = (int)$v * 60*60*24;
			return true;
		} else {
			return false;
		}
	}


	public function setInputFile( $v ) {
		$v = trim( $v );
		if( is_file($v) ) {
			$this->input_file = $v;
			return true;
		} else {
			return false;
		}
	}


	private function addEmail( $email )
	{
		if( !in_array($email,$this->r_email) ) {
			$this->r_email[] = $email;
		}
	}


	private function addHost( $o ) {
		$this->r_host[ $o->getHost() ] = $o;
	}


	public function run()
	{
		if( $this->input_file ) {
			echo "Loading data file...\n";
			$t_result = array( array(), file($this->input_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) );
			//var_dump($t_result);
		} else {
			$t_result = $this->findHost();
		}

		echo "\n";

		foreach ($t_result[0] as $e) {
			$this->addEmail( $e );
		}
		echo count($this->r_email)." email found.\n";

		foreach ($t_result[1] as $h) {
			$this->computeHost( $h );
		}
		$cnt_host = count( $this->r_host );
		echo $cnt_host." host found.\n";

		if( $cnt_host ) {
			$this->getExpire();
		}

//		var_dump( $this->r_email);
//		var_dump( $this->r_host);
//		exit();

		echo "\n";

		return $cnt_host;
	}


	private function findHost()
	{
		$th = new TheHarvester();
		$th->setDomain( $this->domain );
		$th->setInputFile( $this->input_file );
		$th->run();

		return array( $th->rEmail(), $th->rHost() );
	}


	private function computeHost( $host, $son=null )
	{
		if( isset($this->r_host[$host]) ) {
			return $this->r_host[$host];
		}

		exec( 'host '.$host, $tmp );
		usleep( 100000 );
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


	public function getExpire()
	{
		foreach( $this->r_host as $host )
		{
			$date = null;
			$tmp = explode( '.', $host->getHost() );

			for( $i=count($tmp)-2 ; $i>=0 ; $i-- )
			{
				$h = implode('.', array_slice($tmp, $i));
				//var_dump( $h );

				if (isset($this->t_expire[$h])) {
					// we already performed whois for this host ??
					if ($this->t_expire[$h]['date'] == '') {
						continue;
					} else {
						$date = $this->t_expire[$h]['date'];
						break;
					}
				} else {
					$whois = '';
					$this->t_expire[$h] = array( 'date'=>'', 'host'=>'' );
					exec('whois ' . $h, $whois);
					usleep( 100000 );
					//echo "WHOIS $h\n";
					$k = Utils::_array_search( $whois, $this->t_expire_string );

					if( $k !== false ) {
						$this->t_expire[$h]['host'] = $host;
						$date = $this->t_expire[$h]['date'] = trim( $whois[$k] );
						break;
					}
				}
			}
		}

		ksort( $this->t_expire );
	}


	public function printResult()
	{
		foreach( $this->t_expire as $h=>$d )
		{
			echo 'Domain: '.$h.' ';
			$info = '';
			$time = self::_strtotime( $d['date'] );

			if( !$d['date'] || $d['date'] == '' || !(int)$time ) {
				$info = $d['date'];
				echo "*date not found";
			}
			else {
				$current = time();

				if ($current > $time ) {
					$color = 'red';
					$info = implode( ', ', $d['host']->getAlias() );
				} elseif (($current + $this->alert) > $time) {
					$color = 'yellow';
					$info = implode( ', ', $d['host']->getAlias() );
				} else {
					$color = 'green';
				}

				Utils::_print( trim($d['date']), $color );
			}

			if( strlen($info) ) {
				echo ' ('.$info.')';
			}
			echo "\n";
		}
	}


	private static function _strtotime( $str )
	{
		if( strstr($str,':') ) {
			list($null, $str) = explode(':', $str);
		}
		if( strstr($str,'T') ) {
			list($str,$null) = explode('T', $str);
		}

		$str = trim( $str );

		if( strstr($str,' ') ) {
			list($str,$null) = explode(' ', $str);
		}

		$str = str_replace( '.', '-', $str );
		$str = str_replace( 'UTC', '', $str );

		if( strstr($str,'/') ) {
			list($d,$m,$y) = explode( '/', $str );
			$str = $y.'-'.$m.'-'.$d;
		}

		$time = strtotime( $str );
		return $time;
	}
}

?>
