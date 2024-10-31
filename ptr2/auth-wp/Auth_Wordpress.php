<?php
namespace Plainware\PlainTracker;

class Auth_Wordpress
{
	public $self = __CLASS__;

	public function getCurrentUserId( $ret, array $x )
	{
		if( false !== $ret ) return $ret;
		$ret = get_current_user_id();
		return $ret;
	}
}