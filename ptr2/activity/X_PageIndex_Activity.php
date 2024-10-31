<?php
namespace Plainware\PlainTracker;

class X_PageIndex_Activity
{
	public $self = __CLASS__;
	public $modelActivity = ModelActivity::class;

	public function navAdmin( array $ret, array $x )
	{
		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$count = $this->modelActivity->count( $q );
		$ret[ '52-activity' ] = [ '.activity-index', '<span>__Activities__</span><i>(' . $count . ')</i>' ];

		return $ret;
	}
}