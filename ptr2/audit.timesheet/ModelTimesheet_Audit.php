<?php
namespace Plainware\PlainTracker;

class ModelTimesheet_Audit
{
	public $model = ModelTimesheet::class;
	public $modelAudit = ModelAudit::class;

	public function create( _Timesheet $m, _Timesheet $m0, $reason = '' )
	{
		$m2 = $this->modelAudit->construct();
		$m2->classId = 'timesheet';
		$m2->objectId = $m->id;
		$m2->meta = [ 'id' => null ];
		if( $reason ){
			$m2->description = $reason;
		}
		$this->modelAudit->create( $m2 );

		return $m;
	}

	public function update( _Timesheet $ret, _Timesheet $m, _Timesheet $m2, $reason = '' )
	{
		$a = $this->model->toArray( $m );
		$a2 = $this->model->toArray( $m2 );

		$meta = [];
		foreach( $a2 as $k2 => $vNew ){
			$vOld = array_key_exists($k2, $a) ? $a[$k2] : null;
			if( $vOld == $vNew ){
				continue;
			}
			$meta[ $k2 ] = $vOld;
		}

		if( ! $meta ){
			return $ret;
		}

		$m2 = $this->modelAudit->construct();
		$m2->classId = 'timesheet';
		$m2->objectId = $ret->id;
		$m2->meta = $meta;
		if( $reason ){
			$m2->description = $reason;
		}
		$this->modelAudit->create( $m2 );

		return $ret;
	}

	public function delete( _Timesheet $m )
	{
		$q = [];
		$q[] = [ 'classId', '=', 'timesheet' ];
		$q[] = [ 'objectId', '=', $m->id ];
		$this->modelAudit->deleteMany( $q );

		return $m;
	}
}