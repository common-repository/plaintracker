<?php
namespace Plainware\PlainTracker;

class X_ModelRecord_Record
{
	public $self = __CLASS__;
	public $t = \Plainware\Time::class;
	public $model = ModelRecord::class;

	public function update( _Record $ret, _Record $m, _Record $m2 )
	{
	// adjust duration if clock in/out changed
		// if( ($m->clockIn != $m2->clockIn) OR ($m->clockOut != $m2->clockOut) ){
			// $ret2 = clone $ret;
			// $ret2->duration = $m2->clockOut ? $this->t->getDuration( $m2->clockOut, $m2->clockIn ) : 0;
			// $ret = $this->model->update( $ret, $ret2 );
		// }

		return $ret;
	}

	public function create( _Record $m )
	{
		if( ! $m->id ) return;

	// update duration if end time is set?
		if( $m->endAt && (! $m->duration) ){
			$duration = $this->t->getDuration( $m->startAt, $m->endAt );
			$m2 = clone $m;
			$m2->duration = $duration;
			$m = $this->model->update( $m, $m2 );
		}

	// if an open record before this one exists for this worker then close it
		$q = [];
		$q[] = [ 'workerId', '=', $m->workerId ];
		$q[] = [ 'endAt', '=', 0 ];
		$q[] = [ 'startAt', '<=', $m->startAt ];
		$q[] = [ 'id', '<>', $m->id ];

		$modelList = $this->model->find( $q );
		foreach( $modelList as $m21 ){
			$m22 = clone $m21;
			$m22->endAt = $m->startAt;
			$this->model->update( $m21, $m22 );
		}

	// if this one is not closed and another record after this one exists then close this one
		if( ! $m->endAt ){
			$q = [];
			$q[] = [ 'workerId', '=', $m->workerId ];
			$q[] = [ 'startAt', '>', $m->startAt ];
			$q[] = [ 'limit', 1 ];
			$q[] = [ 'order', 'startAt', 'ASC' ];
			$q[] = [ 'id', '<>', $m->id ];

			$nextStartAtList = $this->model->findProp( 'startAt', $q );
			if( $nextStartAtList ){
				$nextStartAt = current( $nextStartAtList );
				$m2 = clone $m;
				$this->model->update( $m, $m2 );
			}
		}

		return $m;
	}
}