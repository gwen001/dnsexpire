<?php

class Utils
{
	const TMP_DIR = '/tmp/';
	const T_SHELL_COLORS = array(
		'black' => '30',
		'blue' => '34',
		'green' => '32',
		'cyan' => '36',
		'red' => '31',
		'purple' => '35',
		'brown' => '33',
		'light_gray' => '37',
		'dark_gray' => '30',
		'light_blue' => '34',
		'light_green' => '32',
		'light_cyan' => '36',
		'light_red' => '31',
		'light_purple' => '35',
		'yellow' => '33',
		'white' => '37',
	);


	public static function isEmail( $str )
	{
		return filter_var( $str, FILTER_VALIDATE_EMAIL );
	}


	public static function _print( $str, $color )
	{
		echo "\033[".self::T_SHELL_COLORS[$color]."m".$str." \033[0m";
	}


	public static function createTempFile()
	{
		$f = self::TMP_DIR . uniqid('dns') . '.xml';

		if( ($fp=fopen($f,'w+')) ) {
			fclose( $fp );
			return $f;
		}

		return false;
	}


	public static function _array_search( $array, $search, $ignore_case=true )
	{
		if( $ignore_case ) {
			$f = 'stristr';
		} else {
			$f = 'strstr';
		}

		if( !is_array($search) ) {
			$search = array( $search );
		}

		foreach( $array as $k=>$v ) {
			foreach( $search as $str ) {
				if( $f($v, $str) ) {
					return $k;
				}
			}
		}

		return false;
	}
}

?>
