<?php
namespace Plainware\PlainTracker;

class CrudTimesheet extends \Plainware\CrudSqlTable
{
	public static $table = '__timesheet';
	public static $fields = [
		'id'			=> [ 'type' => 'INTEGER',		'null' => false, 'auto_increment' => true, 'key' => true ],

		'start_date'	=> [ 'type' => 'INTEGER',	'alias' => 'startDate', 'null' => false ],
		'end_date'	=> [ 'type' => 'INTEGER',	'alias' => 'endDate', 'null' => false ],
		'worker_id'		=> [ 'type' => 'INTEGER',	'alias' => 'workerId', 'null' => false, 'default' => 0 ],

		'state_id'		=> [ 'type' => 'VARCHAR(16)',	'alias' => 'stateId',	'null' => false, 'default' => 'draft' ], // draft,submit,approve,process
	];

	public function migrate( array $ret )
	{
		$ret[ 'timesheet:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'start_date', 'end_date', 'worker_id', 'state_id' ];
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