<?php
namespace Plainware\PlainTracker;

class CrudAudit extends \Plainware\CrudSqlTable
{
	public static $table = '__audit';
	public static $fields = [
		'id'				=> [ 'type' => 'INTEGER',		'null' => false,	'auto_increment' => true, 'key' => true ],
		'class_id'		=> [ 'type' => 'VARCHAR(32)',	'alias' => 'classId',	'null' => false ],
		'object_id'		=> [ 'type' => 'INTEGER',		'alias' => 'objectId',		'null' => false ],
		'change_at'		=> [ 'type' => 'BIGINT',		'alias' => 'changeAt',	'null' => false ],
		'user_id'		=> [ 'type' => 'INTEGER',		'alias' => 'userId',	'null' => false, 'default' => 0 ],
		'description'	=> [ 'type' => 'TEXT',			'null' => true ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'audit:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'class_id', 'object_id', 'change_at', 'user_id', 'description' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		$this->db->createTable( static::$table, $fields );

		// $this->db->addIndexToTable( static::$table, 'object_type' );
		// $this->db->addIndexToTable( static::$table, 'object_id' );
	}

	public function down1()
	{
		$this->db->dropTable( static::$table );
	}
}