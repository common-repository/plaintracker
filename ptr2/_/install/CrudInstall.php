<?php
namespace Plainware;

class CrudInstall extends \Plainware\CrudSqlTable
{
	public static $table = '__install';
	public static $fields = [
		'id'			=> [ 'type' => 'VARCHAR(64)', 'null' => FALSE, 'key' => TRUE ],
		'version'	=> [ 'type' => 'INTEGER', 'null'	=> FALSE ],
	];

	public function read( array $q = [], array $propNameList = [] )
	{
		$ret = [];

		try {
			$ret = parent::read( $q, $propNameList );
		}
		catch( \Exception $e ){}

		if( ! $ret ){
			$this->db->disable();
		}

		return $ret;
	}

	public function migrate()
	{
		$ret = [];
		$ret[ '11-install:1' ] = [ __CLASS__ . '::up1', __CLASS__ . '::down1' ];
		return $ret;
	}

	public function up1()
	{
		$this->db->enable();
		$fields = [ 'id', 'version' ];
		$fields = array_intersect_key( static::$fields, array_combine($fields, $fields) );
		$this->db->createTable( static::$table, $fields );
	}

	public function down1()
	{
		$this->db->dropTable( static::$table );
	}
}