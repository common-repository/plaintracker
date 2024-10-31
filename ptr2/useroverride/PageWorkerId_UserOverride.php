<?php
namespace Plainware\PlainTracker;

class PageWorkerId_UserOverride
{
	public $self = __CLASS__;

	public $conf = ConfUserOverride::class;
	public $modelWorker = ModelWorker::class;

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
		$workerId = $x['id'] ?? null;
		if( ! $workerId ) return;

		$worker = $this->modelWorker->findById( $workerId );
		if( ! $worker ) return;

		$userId = $worker->userId;
		if( ! $userId ) return;

		if( $currentUserId == $userId ) return;

		$p = $this->conf->paramName();
		$ret[ '81-override' ] = [ '?' . $p . '=' . $userId, '__Access as if this worker__' ];

		return $ret;
	}
}