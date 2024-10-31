<?php
namespace Plainware;

class CrudSetting extends \Plainware\CrudSqlTable
{
	public static $table = '__setting';
	public static $fields = [
		'id'					=> [ 'type' => 'INTEGER',	'null' => false,	'auto_increment' => true, 'key' => true ],
		'setting_name'		=> [ 'type' => 'TEXT',		'alias' => 'settingName', 'null' => false ],
		'setting_value'	=> [ 'type' => 'TEXT',		'alias' => 'settingValue','null' => false ],
		// 'setting_if'		=> [ 'type' => 'TEXT',		'alias' => 'settingIf','null' => true, 'default' => '' ],
	];

	public function migrate( array $ret )
	{
		$ret[ 'setting:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		// $ret[ 'setting:2' ] = [ __CLASS__ . '::up2' ];
		return $ret;
	}

	public function up1()
	{
		$fields = [ 'id', 'setting_name', 'setting_value' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		$this->db->createTable( static::$table, $fields );
	}

	// public function up2()
	// {
		// $fields = [ 'setting_if' ];
		// $fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		// foreach( $fields as $col => $f ){
			// $this->db->addColumn( static::$table, $col, $f );
		// }
	// }
}