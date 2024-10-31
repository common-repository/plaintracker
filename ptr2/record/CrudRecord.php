<?php
namespace Plainware\PlainTracker;

class CrudRecord extends \Plainware\CrudSqlTable
{
	public static $table = '__record';
	public static $fields = [
		'id'			=> [ 'type' => 'INTEGER',		'null' => false, 'auto_increment' => true, 'key' => true ],

		'start_date'	=> [ 'type' => 'INTEGER',	'alias' => 'startDate', 'null' => false ],
		'duration'		=> [ 'type' => 'INTEGER',	'null' => false ],

		'worker_id'		=> [ 'type' => 'INTEGER',	'alias' => 'workerId', 'null' => false, 'default' => 0 ],
		'project_id'	=> [ 'type' => 'INTEGER',	'alias' => 'projectId', 'null' => false, 'default' => 0 ],
		'activity_id'	=> [ 'type' => 'INTEGER',	'alias' => 'activityId', 'null' => false, 'default' => 0 ],

		'clock_in'		=> [ 'type' => 'BIGINT',	'alias' => 'clockIn', 'null' => false, 'default' => 0 ],
		'clock_out'		=> [ 'type' => 'BIGINT',	'alias' => 'clockOut', 'null' => false, 'default' => 0 ],

		// 'state_id'		=> [ 'type' => 'VARCHAR(16)',	'alias' => 'stateId',	'null' => false, 'default' => 'enter' ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'record:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'start_date', 'duration', 'worker_id', 'project_id', 'activity_id', 'state_id', 'clock_in', 'clock_out' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		$this->db->createTable( static::$table, $fields );
	}

	// public function up2()
	// {
		// $fields = [ 'timesheet_id' ];
		// $fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		// foreach( $fields as $col => $f ){
			// $this->db->addColumn( static::$table, $col, $f );
		// }
	// }
}