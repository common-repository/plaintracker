<?php
namespace Plainware\PlainTracker;

class PageUserId_UserOverride
{
	public $self = __CLASS__;

	public $conf = ConfUserOverride::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function nav( array $ret, array $x )
	{
	// only admin can override
		$currentUserId = $this->auth->getCurrentUserId( $x );
		if( ! $currentUserId ) return;

		$can = $this->acl->isAdmin( $currentUserId );
		if( ! $can ) return;

	// this user in question
		$userId = $x['id'] ?? null;
		if( ! $userId ) return;
		if( $currentUserId == $userId ) return;

		$p = $this->conf->paramName();
		$ret[ '81-override' ] = [ '?' . $p . '=' . $userId, '__Access as if this user__' ];

		return $ret;
	}
}