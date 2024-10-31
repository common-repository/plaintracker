<?php
namespace Plainware\PlainTracker;

class X_Handler_Audit
{
	public $modelAudit = ModelAudit::class;
	public $auth = Auth::class;

	public function x( array $ret, array $x )
	{
		$currentUserId = $this->auth->getCurrentUserId( $x );
		$this->modelAudit->setCurrentUserId( $currentUserId );
		return $ret;
	}
}