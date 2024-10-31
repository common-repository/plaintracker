<?php
namespace Plainware;

class Translate
{
	public $self = __CLASS__;
	protected $_ret = [];

	public function translateString( $in, $lang )
	{
		return $in;
	}

	public function translate( $ret, $lang = 'en' )
	{
		if( isset($this->_ret[$lang][$ret]) ){
			return $this->_ret[$lang][$ret];
		}

// return $ret;
		$parts = [];

		$start = '__';
		$startLen = strlen( $start );
		$end = '__';
		$endLen = strlen( $end );

	// find strings to translate
		$replace = [];
		$pos1 = strpos( $ret, $start );
		while( false !== $pos1 ){
			$pos2 = strpos( $ret, $end, $pos1 + $startLen + 1 );
			if( false === $pos2 ) break;

			$pos2 = $pos2 + $endLen;
			$part = substr( $ret, $pos1, $pos2 - $pos1 );
			$toString = substr( $part, $startLen, -$endLen );

			$replace[ $part ] = $toString;

			if( ($pos2 + 1) > strlen($ret) ){
				break;
			}

			$pos1 = strpos( $ret, $start, $pos2 + 1 );
		}

	// do translate
		if( 'en' != $lang ){
			foreach( array_keys($replace) as $k ){
				$replace[$k] = $this->self->translateString( $replace[$k], $lang );
			}
		}

// _print_r( $replace );
// exit;

	// replace in output
		$ret = strtr( $ret, $replace );
		// $ret = str_replace( array_keys($replace), array_values($replace), $ret );

		$this->_ret[$lang][$ret] = $ret;

		return $ret;
	}
}