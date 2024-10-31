<?php
namespace Plainware\PlainTracker;

class CrudActivity extends \Plainware\CrudSqlTable
{
	public static $table = '__activity';
	public static $fields = [
		'id'			=> [ 'type' => 'INTEGER',			'null' => false,	'auto_increment' => true, 'key' => true ],
		// 'ref'			=> [ 'type' => 'VARCHAR(16)',		'null' => false ],
		'title'		=> [ 'type' => 'VARCHAR(255)',	'null' => false ],
		'show_order'	=> [ 'type' => 'INTEGER',		'alias' => 'showOrder', 'null' => false, 'default' => 1 ],
		'state_id'		=> [ 'type' => 'VARCHAR(16)',		'alias' => 'stateId', 'null' => false, 'default' => 'active' ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'activity:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		// $ret[ 'activity:2' ] = [ CrudActivity::class . '::up2' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'title', 'show_order', 'state_id' ];
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