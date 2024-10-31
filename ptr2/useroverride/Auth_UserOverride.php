<?php
namespace Plainware\PlainTracker;

class Auth_UserOverride
{
	public $self = __CLASS__;
	public $conf = ConfUserOverride::class;

	public $acl = Acl::class;

	public function getCurrentUserId( $originalUserId, array $x )
	{
		$p = $this->conf->paramName();
		if( ! isset($x[$p]) ) return $originalUserId;

		$overridenUserId = $x[$p];
		if( ! strlen($overridenUserId) ) return $x;

	// only allowed for those who can manage users
		$can = $this->acl->isAdmin( $originalUserId ) ? true : false;
		if( ! $can ) return $originalUserId;

		$ret = $overridenUserId;
		return $ret;
	}
}