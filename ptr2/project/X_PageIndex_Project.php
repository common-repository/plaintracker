<?php
namespace Plainware\PlainTracker;

class X_PageIndex_Project
{
	public $self = __CLASS__;
	public $model = ModelProject::class;

	public function navAdmin( array $ret, array $x )
	{
		$q = [];
		// $q[] = [ 'stateId', '=', 'active' ];
		$count = $this->model->count( $q );
		$ret[ '51-project' ] = [ '.project-index', '<span>__Projects__</span><i>(' . $count . ')</i>' ];
		return $ret;
	}
}