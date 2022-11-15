<?php

/**
 * I don't believe in license
 * You can do whatever you want with this program
 */

class DnsExpire
{
	const DEFAULT_ALERT = 60*60*24*30; // 30 days

	private $domain = null;

	private $alert = self::DEFAULT_ALERT;

	private $input_file = null;

	private $r_host = array();

	private $t_expire = array();

	private $t_expire_string = array('Expiry date:', 'Expiration Date:', 'free-date:', 'expires:', 'Expiry :');


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
		} else {
            $t_result = [ $this->domain ];
        }

		echo "\n";

		echo "Looking for aliases...\n";
		foreach( $t_result as $h ) {
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

		exec( 'host -t CNAME '.$host, $tmp );
		usleep( 300000 );
        // var_dump($tmp);
		$tmp = implode( "\n", $tmp );

		$m = preg_match( '#not found: 3(NXDOMAIN)#', $tmp );
		if( $m ) {
			return false;
		}

		//preg_match( '#.* has address (.*)#i', $tmp, $matches );

		$o = new Host();
		$o->setHost( $host );
		//$o->setIp( $matches[1] );

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
        // var_dump($this->r_host);
		foreach( $this->r_host as $host )
		{
			$domain = Utils::extractDomain( $host->getHost() );
            // echo $host->getHost().' -> '.$domain."\n";

			if( $domain !== false && !isset($this->t_expire[$domain]) ) {
				$whois = '';
				$this->t_expire[$domain] = array('date' => '', 'host' => $host);
				echo 'WHOIS ' . $domain . "\n";
				exec( 'whois ' . $domain, $whois );
                // var_dump($whois);
				$str_whois = implode( "\n", $whois );
				usleep( 500000 );

				if( preg_match('#No match for "'.strtoupper($domain).'"\.#',$str_whois) ) {
					$this->t_expire[$domain]['date'] = 'closed';
				} else {
					$k = Utils::_array_search( $whois, $this->t_expire_string );
					if( $k !== false ) {
						$this->t_expire[$domain]['date'] = trim( preg_replace('#\s+#', ' ', $whois[$k]) );
					}
				}
			}
		}

		ksort( $this->t_expire );
	}


	public function printResult()
	{
		$current = time();

		foreach( $this->t_expire as $h=>$d )
		{
			echo 'Domain: '.$h.' ';
			$info = '';
			$time = self::_strtotime( $d['date'] );

			if( (!$d['date'] || $d['date'] == '' || !(int)$time) && $d['date']!='closed' ) {
                $color = 'dark_grey';
                $txt = '*date not found*';
			}
			else {
                $txt = trim( $d['date'] );
                $txt .= ' ('.date('d/m/Y',$time).')';

				if ($current > $time || $d['date']=='closed' ) {
					$color = 'red';
                    $txt .= ' -> BAZINGA !!!';
				} elseif( ($current+$this->alert) > $time ) {
					$color = 'yellow';
                    $txt .= ' -> ACHTUNG !';
				} else {
					$color = 'green';
                    $txt .= ' -> OK';
				}
			}

            Utils::_print( $txt, $color );

			$info = $this->getAliasPath( $d['host']->getHost() );
			if( count($info) ) {
				echo "\n";
				foreach( $info as $i ) {
					echo "\t-> ".$i."\n";
				}
			}
			echo "\n";
		}

		echo "Done.\n\n";
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

