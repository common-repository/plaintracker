<?php
namespace Plainware\PlainTracker;

class ModelRecord_Audit
{
	public $model = ModelRecord::class;
	public $modelAudit = ModelAudit::class;

	public function create( _Record $m, _Record $m0, $reason = '' )
	{
		$m2 = $this->modelAudit->construct();
		$m2->classId = 'record';
		$m2->objectId = $m->id;
		$m2->meta = [ 'id' => null ];
		if( $reason ){
			$m2->description = $reason;
		}
		$this->modelAudit->create( $m2 );

		return $m;
	}

	public function update( _Record $ret, _Record $m, _Record $m2, $reason = '' )
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
		$m2->classId = 'record';
		$m2->objectId = $ret->id;
		$m2->meta = $meta;
		if( $reason ){
			$m2->description = $reason;
		}
		$this->modelAudit->create( $m2 );

		return $ret;
	}

	public function delete( _Record $m )
	{
		$q = [];
		$q[] = [ 'classId', '=', 'record' ];
		$q[] = [ 'objectId', '=', $m->id ];
		$this->modelAudit->deleteMany( $q );

		return $m;
	}
}