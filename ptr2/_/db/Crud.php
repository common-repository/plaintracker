<?php
namespace Plainware;

abstract class Crud
{
	public $self = __CLASS__;

	abstract public function create( array $values );
	abstract public function createMany( array $arrayOfValues );

	abstract public function read( array $q = [], array $listPropName = [] );
	abstract public function update( array $values, array $q );
	abstract public function delete( array $q );

	abstract public function count( array $q = [] );
	abstract public function countMany( $groupBy, array $q = [] );

	public static $convert = [
		// 'duration' => \Plainware\Crud::class . '::convertArray',
	];

	public static function convertArray( $in, $toDb = true )
	{
		if( $toDb ){
			$ret = join( ',', $in );
		}
		else {
			$ret = explode( ',', $in );
		}
		return $ret;
	}

	public static function convertJson( $in, $toDb = true )
	{
		if( $toDb ){
			$ret = json_encode( $in );
		}
		else {
			$ret = strlen( $in ) ? json_decode( $in, true ) : [];
		}
		return $ret;
	}

	public function convertValueListFromDb( array $valueList )
	{
		reset( static::$fields );
		foreach( static::$fields as $k => $f ){
			if( isset(static::$fields[$k]['alias']) ){
				$k = static::$fields[$k]['alias'];
			}
			if( ! array_key_exists($k, $valueList) ) continue;

			// if( isset($f['type']) && in_array($f['type'], ['INTEGER', 'TINYINT']) ){
			if( isset($f['type']) && in_array($f['type'], ['INTEGER', 'TINYINT', 'BIGINT']) ){
				$valueList[ $k ] = (int) $valueList[ $k ];
			}
		}

	// convert func if any
		reset( static::$convert );
		foreach( static::$convert as $k => $func ){
			if( isset(static::$fields[$k]['alias']) ){
				$k = static::$fields[$k]['alias'];
			}

			if( ! array_key_exists($k, $valueList) ) continue;
			$valueList[ $k ] = call_user_func( $func, $valueList[$k], false );
		}

		return $valueList;
	}

	public function convertValueListToDb( array $valueList )
	{
		$ret = [];

		reset( static::$fields );
		foreach( static::$fields as $k => $f ){
			if( array_key_exists($k, $valueList) ){
				$ret[ $k ] = $valueList[ $k ];
			}
			else {
				if( isset($f['alias']) ){
					if( array_key_exists($f['alias'], $valueList) ){
						$ret[ $k ] = $valueList[ $f['alias'] ];
					}
				}
			}

			if( array_key_exists($k, $ret) ){
				$ret[ $k ] = $this->self->convertToDb( $k, $ret[$k] );
			}
		}

	// convert func if any
		reset( static::$convert );
		foreach( static::$convert as $k => $func ){
			if( ! array_key_exists($k, $ret) ) continue;
			$ret[ $k ] = call_user_func( $func, $ret[$k], true );
		}

		return $ret;
	}

	public function convertToDb( $k, $val )
	{
		return $val;
	}
}