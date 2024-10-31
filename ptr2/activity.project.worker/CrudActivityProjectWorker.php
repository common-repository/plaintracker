<?php
namespace Plainware\PlainTracker;

class CrudActivityProjectWorker extends \Plainware\CrudSqlTable
{
	public static $table = '__activity_project_worker';
	public static $fields = [
		'activity_id'	=> [ 'type' => 'INTEGER',	'alias' => 'activityId', 'null' => false, 'key' => 'true' ],
		'project_id'	=> [ 'type' => 'INTEGER',	'alias' => 'projectId', 'null' => false, 'key' => 'true' ],
		'worker_id'		=> [ 'type' => 'INTEGER',	'alias' => 'workerId', 'null' => false, 'key' => 'true' ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'activity_project_worker:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'activity_id', 'project_id', 'worker_id' ];
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