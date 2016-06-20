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

	private $t_expire = array();

	private $t_expire_string = array('Expiry date:', 'Expiration Date:', 'free-date:', 'expires:');


	public function getDomain() {
		if( $this->input_file ) {
			return $this->input_file;
		} else {
			return $this->domain;
		}
	}
	public function setDomain( $v ) {
		$v = trim( $v );
		if( is_file($v) ) {
			$this->input_file = $v;
		} else {
			$this->domain = $v;
		}
		return true;
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


	private function addHost( $o ) {
		$this->r_host[ $o->getHost() ] = $o;
	}


	public function run()
	{
		if( $this->input_file ) {
			echo "Loading data file...\n";
			$t_result = file( $this->input_file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
		}

		echo "\n";
		
		echo "Looking for aliases...\n";
		foreach ($t_result as $h) {
			$this->computeHost( $h );
		}
		
		$cnt_host = count( $this->r_host );
		echo $cnt_host." host found.\n\n";
		
		echo "Looking for expiration dates...\n";
		if( $cnt_host ) {
			$this->getExpire();
		}
		
		echo "\n";

		return $cnt_host;
	}
	

	private function computeHost( $host, $son=null )
	{
		if( isset($this->r_host[$host]) ) {
			return $this->r_host[$host];
		}

		exec( 'host '.$host, $tmp );
		usleep( 10000 );
		$tmp = implode( "\n", $tmp );

		preg_match( '#.* has address (.*)#i', $tmp, $matches );

		if( !count($matches) ) {
			return false;
		}

		$o = new Host();
		$o->setHost( $host );
		$o->setIp( $matches[1] );

		if( $son ) {
			$o->addAlias( $son );
		}

		preg_match( '#(.*) is an alias for (.*)#i', $tmp, $matches );

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
				
				if( isset($this->t_expire[$h]) ) {
					// we already performed whois for this host ??
					$date = $this->t_expire[$h]['date'];
					break;
				} else {
					$test = '';
					exec('host '.$h, $test);
					
					if( is_array($test) && count($test) )
					{
						$whois = '';
						$this->t_expire[$h] = array( 'date'=>'', 'host'=>$host );
						echo "WHOIS $h\n";
						exec('whois ' . $h, $whois);
						usleep( 10000 );
						$k = Utils::_array_search( $whois, $this->t_expire_string );
						
						if( $k !== false ) {
							$date = $this->t_expire[$h]['date'] = trim( preg_replace('#\s+#',' ',$whois[$k]) );
						}

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
				Utils::_print( "*date not found*", 'dark_grey' );
			}
			else {
				$current = time();
				
				if ($current > $time ) {
					$color = 'red';
				} elseif (($current + $this->alert) > $time) {
					$color = 'yellow';
				} else {
					$color = 'green';
				}

				Utils::_print( trim($d['date']), $color );
			}
			
			$info = $this->getAliasPath( $d['host']->getHost() );
			if( count($info) ) {
				echo "\n";
				foreach( $info as $i ) {
					echo "\t-> ".$i."\n";
				}
			}
			echo "\n";
		}
	}


	private function getAliasPath( $host, $path=array() )
	{
		$path[] = $host;
		
		foreach( $this->r_host[$host]->getAlias() as $a ) {
			$path = $this->getAliasPath( $a, $path );
		}
		
		return $path;
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
