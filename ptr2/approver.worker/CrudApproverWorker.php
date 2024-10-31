<?php
namespace Plainware\PlainTracker;

class CrudApproverWorker extends \Plainware\CrudSqlTable
{
	public static $table = '__approver_worker';
	public static $fields = [
		'approver_id' => [ 'type' => 'INTEGER',	'alias' => 'approverId', 'null' => false, 'key' => 'true' ],
		'worker_id' => [ 'type' => 'INTEGER',	'alias' => 'workerId', 'null' => false, 'key' => 'true' ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'approver_worker:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'approver_id', 'worker_id' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		$this->db->createTable( static::$table, $fields );
	}

	// public function up2()
	// {
		// $fields = [ 'ref' ];
		// $fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		// foreach( $fields as $col => $f ){
			// $this->db->addColumn( static::$table, $col, $f );
		// }
	// }
}