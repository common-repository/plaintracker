<?php
namespace Plainware\PlainTracker;

class CrudAuditMeta extends \Plainware\CrudSqlTable
{
	public static $table = '__auditmeta';
	public static $fields = [
		// 'id'		=> [ 'type' => 'INTEGER',		'null' => false,	'auto_increment' => true, 'key' => true ],
		'aidit_id'	=> [ 'type' => 'INTEGER',	'alias' => 'auditId', 'null' => false ],
		'prop_name'	=> [ 'type' => 'VARCHAR(32)',	'alias' => 'propName', 'null' => false ],
		'value_old'	=> [ 'type' => 'TEXT',			'alias' => 'valueOld', 'null' => true ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'auditmeta:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'aidit_id', 'prop_name', 'value_old' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		$this->db->createTable( static::$table, $fields );
	}

	public function down1()
	{
		$this->db->dropTable( static::$table );
	}
}