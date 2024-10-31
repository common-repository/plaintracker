<?php
namespace Plainware\PlainTracker;

class CrudProject extends \Plainware\CrudSqlTable
{
	public static $table = '__project';
	public static $fields = [
		'id'			=> [ 'type' => 'INTEGER',			'null' => false,	'auto_increment' => true, 'key' => true ],
		'title'		=> [ 'type' => 'VARCHAR(255)',	'null' => false, 'default' => '' ],
		'state_id'	=> [ 'type' => 'VARCHAR(16)',		'alias' => 'stateId', 'null' => false, 'default' => 'active' ],

		'start_date' => [ 'type' => 'INTEGER',	'alias' => 'startDate', 'null' => false, 'default' => 0 ],
		'end_date'	=> [ 'type' => 'INTEGER',	'alias' => 'endDate', 'null' => false, 'default' => 99999999 ],
		'start_submit'	=> [ 'type' => 'BIGINT',	'alias' => 'startSubmit', 'null' => false, 'default' => 0 ],
		'end_submit'	=> [ 'type' => 'BIGINT',	'alias' => 'endSubmit', 'null' => false, 'default' => 999999999999 ],

		'pay_period' => [ 'type' => 'VARCHAR(16)',	'alias' => 'payPeriod', 'null' => false, 'default' => 'week' ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'project:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		$ret[ 'project:2' ] = [ __CLASS__ . '::up2' ];
		$ret[ 'project:3' ] = [ __CLASS__ . '::up3' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'title', 'state_id' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		$this->db->createTable( static::$table, $fields );
	}

	public function up2()
	{
		$fields = [ 'start_date', 'end_date', 'start_submit', 'end_submit' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		foreach( $fields as $col => $f ){
			$this->db->addColumn( static::$table, $col, $f );
		}
	}

	public function up3()
	{
		$fields = [ 'pay_period' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		foreach( $fields as $col => $f ){
			$this->db->addColumn( static::$table, $col, $f );
		}
	}
}