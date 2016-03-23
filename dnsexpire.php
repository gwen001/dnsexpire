<?php

set_time_limit( 0 );

function usage( $error='' ) {
    echo "Usage: ".$_SERVER['argv'][0]." <domain>\n";
    if( $error ) {
        echo "Error: ".$error."!\n";
    }
    exit();
}


if( $_SERVER['argc'] < 2 ) {
    usage();
}

define( 'DATE_ALERT', 60*60*24*30 ); // 30 days


include( 'Core.php' );
include( 'Host.php' );
include( 'TheHarvester.php' );
include( 'Utils.php' );


$domain = $_SERVER['argv'][1];

$core = new Core();
$core->setDomain( $domain );
$core->proceed();


//var_dump( $core->rEmail() );
//var_dump( $core->rHost() );

//exit();
$t_host = $core->rHost();
//var_dump( $t_host );
$t_check = array();

foreach( $t_host as $h ) {
	$date = null;
	$tmp = explode('.', $h->getHost());

	for( $i=count($tmp)-2 ; $i>=0 ; $i-- )
	{
		$host = implode('.', array_slice($tmp, $i));
		//var_dump( $host );

		if (isset($t_check[$host])) {
			if ($t_check[$host] == '') {
				continue;
			} else {
				$date = $t_check[$host];
				break;
			}
		} else {
			$t_check[$host] = '';
			exec('whois ' . $host, $whois);
			//echo "WHOIS $host\n";
			$k = Utils::_array_search($whois, array('Expiry date:', 'Expiration Date:', 'free-date:'));

			if( $k !== false ) {
				$date = $t_check[$host] = $whois[$k];
				unset( $whois );
				break;
			}
			unset( $whois );
		}
	}
}

foreach( $t_check as $h=>$d )
{
	$time = Utils::_strtotime( $d );
	$current = time();

	if( $current > $time ) {
		$color = 'red';
	} elseif( ($current+DATE_ALERT) > $time ) {
		$color = 'yellow';
	} else {
		$color = 'green';
	}

	if( $d && $d != '' ) {
		echo 'Domain: '.$h."\n";
		Utils::_print( trim($d), $color );
		echo "\n";
	//} else {
	//	echo $h->getHost()."\n";
	//	echo "Nothing found!\n";
	}
}


exit();

?>
