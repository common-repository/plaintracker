<?php
namespace Plainware;

abstract class CrudSqlTable extends Crud
{
	public $db = Db::class;

	public static $table = '__table';
	public static $fields = [
		'id'				=> [ 'type' => 'INTEGER',		'null' => false,	'auto_increment' => true, 'key' => true ],
		'obj_class'		=> [ 'type' => 'VARCHAR(32)',	'null' => false ],
		'obj_id'			=> [ 'type' => 'INTEGER',		'null' => false ],
		'meta_id'		=> [ 'type' => 'VARCHAR(32)',	'null' => false ],
		'meta_value'	=> [ 'type' => 'TEXT',			'null' => true ],
	];
	public static $unique = [];

	public function down1()
	{
		$this->db->dropTable( static::$table );
	}

	public function getFields()
	{
		return static::$fields;
	}

	public function convertQToDb( array $q )
	{
		$alias = [];

		reset( static::$fields );
		foreach( static::$fields as $k => $f ){
			if( ! isset($f['alias']) ){
				continue;
			}
			$alias[ $f['alias'] ] = $k;
		}

		if( ! $alias ){
			return $q;
		}

		$q = Q::convertNames( $q, $alias );
		return $q;
	}

	public function count( array $q = [] )
	{
		$q = $this->convertQToDb( $q );
		return $this->db->count( static::$table, $q );
	}

	public function countMany( $groupBy, array $q = [] )
	{
		$dbGroupBy = $groupBy;

		reset( static::$fields );
		foreach( static::$fields as $k => $f ){
			if( isset($f['alias']) && ($groupBy == $f['alias']) ){
				$dbGroupBy = $k;
				break;
			}
		}

		$q = $this->convertQToDb( $q );
		$ret = $this->db->count( static::$table, $q, $dbGroupBy );

		return $ret;
	}

	public function create( array $valueList )
	{
		$valueList = $this->convertValueListToDb( $valueList );
		$id = $this->db->insert( static::$table, $valueList );
		return $id;
	}

	public function replace( array $valueList )
	{
		$valueList = $this->convertValueListToDb( $valueList );
		$id = $this->db->replace( static::$table, $valueList );
		return $id;
	}

	public function createMany( array $arrayOfValues )
	{
		foreach( array_keys($arrayOfValues) as $k ){
			$arrayOfValues[$k] = $this->convertValueListToDb( $arrayOfValues[$k] );
		}
		return $this->db->insertMany( static::$table, $arrayOfValues );
	}

	public function read( array $q = [], array $propNameList = [] )
	{
		$ret = [];

		$q = $this->convertQToDb( $q );

		$dbPropNameList = [];
		foreach( static::$fields as $k => $f ){
			$aliasedK = isset( $f['alias'] ) ? $f['alias'] : $k;
			$dbPropNameList[ $k ] = $aliasedK;
		}

		if( $propNameList ){
			$keys = array_keys( $dbPropNameList );
			foreach( $keys as $k ){
				if( ! in_array($dbPropNameList[$k], $propNameList) ){
					unset( $dbPropNameList[$k] );
				}
			}
		}

		$ret = $this->db->read( static::$table, $q, $dbPropNameList );

		foreach( array_keys($ret) as $id ){
			$ret[ $id ] = $this->convertValueListFromDb( $ret[$id] );
		}

		if( $propNameList ){
			$justOne = ( 1 === count($propNameList) ) ? true : false; 
			$propName = current( $propNameList );

			$newRet = [];
			foreach( array_keys($ret) as $id ){
				if( $justOne ){
					$v = $ret[ $id ][ $propName ];
					$newId = $v;
				}
				else {
					$v = [];
					foreach( $propNameList as $propName ){
						$v[ $propName ] = $ret[ $id ][ $propName ];
					}
					$newId = join( '-', $v );
				}
				$newRet[ $newId ] = $v;
				unset( $ret[$id] );
			}

			$ret = $newRet;
		}

		return $ret;
	}

// like MIN(startAt)
	public function readFuncProp( $funcPropName, array $q = [] )
	{
		$ret = null;

		$q = $this->convertQToDb( $q );

		$pos1 = strpos( $funcPropName, '(' );
		$pos2 = strrpos( $funcPropName, ')' );

		$funcName = substr( $funcPropName, 0, $pos1 );
		$propName = substr( $funcPropName, $pos1 + 1, $pos2 - ($pos1 + 1) );

		$dbPropName = $propName;
		reset( static::$fields );
		foreach( static::$fields as $k => $f ){
			if( isset($f['alias']) && ($propName == $f['alias']) ){
				$dbPropName = $k;
				break;
			}
		}

		$dbPropName = $funcName . '(' . $dbPropName . ')';
		$dbPropNameList = [ $dbPropName => $dbPropName ];

		$rowList = $this->db->read( static::$table, $q, $dbPropNameList );
		if( $rowList ){
			$row = current( $rowList );
			$ret = $row[ $dbPropName ];
		}

		return $ret;
	}

	public function update( array $valueList, array $q )
	{
		if( ! $valueList ) return;
		$q = $this->convertQToDb( $q );

		$valueList = $this->convertValueListToDb( $valueList );
		if( ! $valueList ) return;

		$this->db->update( static::$table, $valueList, $q );
	}

	public function delete( array $q )
	{
		$q = $this->convertQToDb( $q );
		$this->db->delete( static::$table, $q );
	}

	public function deleteAll()
	{
		$this->db->deleteAll( static::$table );
	}
}