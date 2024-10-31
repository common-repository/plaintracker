<?php
namespace Plainware\PlainTracker;

class Auth
{
	public $self = __CLASS__;

	public function getCurrentUserId( array $x )
	{
		$ret = false;

		if( defined('PW_USER_ID') && PW_USER_ID ){
			$ret = PW_USER_ID;
			return $ret;
		}

		return $ret;
	}
}