<?php
namespace Plainware\PlainTracker;

class CrudWorker extends \Plainware\CrudSqlTable
{
	public static $table = '__worker';
	public static $fields = [
		'id'			=> [ 'type' => 'INTEGER',			'null' => false,	'auto_increment' => true, 'key' => true ],
		'title'		=> [ 'type' => 'VARCHAR(255)',	'null' => false ],
		'description'	=> [ 'type' => 'TEXT',		'null' => true, 'default' => '' ],
		'email'	=> [ 'type' => 'VARCHAR(255)',	'null' => false, 'default' => '' ],
		'user_id'	=> [ 'type' => 'INTEGER', 'alias' => 'userId', 'null' => false, 'default' => 0 ],
		'state_id'	=> [ 'type' => 'VARCHAR(16)',		'alias' => 'stateId', 'null' => false, 'default' => 'active' ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'worker:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		// $ret[ 'activity:2' ] = [ CrudActivity::class . '::up2' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'title', 'description', 'email', 'user_id', 'state_id' ];
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