<?php
namespace Plainware;

abstract class Db
{
	public $self = __CLASS__;

	public $isDisabled = false;
	public $prefix = null;

	abstract function getPrefix();
	abstract public function prepareQuery( $sql, array $arg );
	abstract public function doQuery( $sql );

	public function disable()
	{
		$this->isDisabled = true;
	}

	public function enable()
	{
		$this->isDisabled = false;
	}

	public function read( $tableName, array $q = [], array $propNameList = [] )
	{
		list( $sql, $arg ) = Sql::read( $tableName, $q, $propNameList );

		$ret = $this->self->query( $sql, $arg );
		if( ! $ret ) $ret = [];

		return $ret;
	}

	public function count( $tableName, array $q, $groupBy = null )
	{
		list( $sql, $arg ) = Sql::count( $tableName, $q, $groupBy );
		$rows = $this->self->query( $sql, $arg );

		if( null === $groupBy ){
			if( $rows ){
				$row = current( $rows );
				$ret = (int) $row['count'];
			}
			else {
				$ret = 0;
			}
		}
		else {
			$ret = [];
			if( $rows ){
				foreach( $rows as $row ){
					$ret[ $row[$groupBy] ] = $row['count'];
				}
			}
		}

		return $ret;
	}

	public function replace( $tableName, $values )
	{
		list( $sql, $arg ) = Sql::replace( $tableName, $values );
		return $this->self->query( $sql, $arg );
	}

	public function insert( $tableName, $values )
	{
		list( $sql, $arg ) = Sql::insert( $tableName, $values );
		return $this->self->query( $sql, $arg );
	}

	public function insertMany( $tableName, $arrayOfValues )
	{
		$sqls = Sql::insertMany( $tableName, $arrayOfValues );
		foreach( $sqls as $e ){
			list( $sql, $arg ) = $e;
			$this->self->query( $sql, $arg );
		}
		return true;
	}

	public function update( $tableName, array $values, array $where )
	{
		list( $sql, $arg ) = Sql::update( $tableName, $values, $where );
		return $this->self->query( $sql, $arg );
	}

	public function delete( $tableName, array $where )
	{
		list( $sql, $arg ) = Sql::delete( $tableName, $where );
		return $this->self->query( $sql, $arg );
	}

	public function deleteAll( $tableName )
	{
		list( $sql, $arg ) = Sql::dbEmptyTable( $tableName );
		return $this->self->query( $sql, $arg );
	}

	public function createTable( $tableName, array $fields, array $uniques = [] )
	{
		list( $sql, $arg ) = Sql::dbCreateTable( $tableName, $fields, $uniques );
		return $this->self->query( $sql, $arg );
	}

	public function addIndexToTable( $tableName, $colName )
	{
		// ALTER TABLE `tableName` ADD INDEX ( `colName` );
		$sql = 'ALTER TABLE ' . $tableName . ' ADD INDEX (' . $colName . ')';
		return $this->self->query( $sql, [] );
	}

	public function dropTable( $tableName )
	{
		list( $sql, $arg ) = Sql::dbDropTable( $tableName );
		return $this->self->query( $sql, $arg );
	}

	public function addColumn( $tableName, $colName, array $f )
	{
		list( $sql, $arg ) = Sql::dbAddColumn( $tableName, $colName, $f );
		return $this->self->query( $sql, $arg );
	}

	public function dropColumn( $tableName, $colName )
	{
		list( $sql, $arg ) = Sql::dbDropColumn( $tableName, $colName, $f );
		return $this->self->query( $sql, $arg );
	}

	public function query( $sql, array $arg )
	{
		if( $this->isDisabled ){
			return;
		}

		if( false !== strpos($sql, '__') ){
			if( null === $this->prefix ){
				$this->prefix = $this->self->getPrefix();
			}
			$sql = str_replace( '__', $this->prefix, $sql );
		}

		// if( $arg ){
			$sql = $this->self->prepareQuery( $sql, $arg );
		// }

	// replace \% to %
		if( false !== strpos($sql, '\%') ){
			$sql = str_replace( '\%', '%', $sql );
		}

		return $this->self->doQuery( $sql );
	}
}