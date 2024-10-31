<?php
namespace Plainware;

class Sql
{
	public static $intTypes = [
		'INTEGER',
		'INT',
		// 'BIGINT',
		'TINYINT'
	];

	public static function insert( $tableName, array $values, $replace = false )
	{
		$sql = '';
		$sqlArg = [];

	// insert
		if( $replace ){
			$sql = 'REPLACE INTO ' . $tableName;
		}
		else {
			$sql = 'INSERT INTO ' . $tableName;
		}

	// fields
		$sf = [];
		$sv = [];

		foreach( $values as $k => $v ){
			if( null === $v ){
				continue;
			}

			$isInt = is_numeric( $v ) && ( strlen($v) < 12 );
			// $isInt = is_numeric( $v );
			// if( $isInt && (null === $v) ){
				// continue;
			// }

			if( is_array($v) ){
				$v = json_encode( $v );
			}

			$sf[] = $k;
			$sqlArg[] = $v;

			if( $isInt ){
				$sv[] = '%d';
			}
			else {
				$sv[] = '%s';
			}
		}
		$sql .= ' (' . join(', ', $sf) . ') VALUES (' . join(', ', $sv) . ')';

		$ret = [ $sql, $sqlArg ];
		return $ret;
	}

	public static function replace( $tableName, array $values )
	{
		return static::insert( $tableName, $values, true );
	}

	public static function insertMany( $tableName, array $arrayOfValues )
	{
// _print_r( $arrayOfValues );
// exit;

		$sqls = [];
		$sqlArgs = [];

	// split into batches
		$batchSize = 200;
		// $batchSize = 2;
		$batchCount = ceil( count($arrayOfValues) / $batchSize );

		for( $ii = 0; $ii < $batchCount; $ii++ ){
			$thisArrayOfValues = array_slice( $arrayOfValues, $batchSize * $ii, $batchSize );

			$sql = '';
			$sqlArg = [];

		// insert
			$sql = 'INSERT INTO ' . $tableName;

			$sf = [];
			$sv = [];

		// fields
			$notNull = [];

			reset( $thisArrayOfValues );
			foreach( $thisArrayOfValues as $values ){
				foreach( $values as $k => $v ){
					if( null !== $v ){
						$notNull[ $k ] = true;
					}
					if( isset($sf[$k]) ) continue;
					$sf[$k] = $k;
				}
			}

			$keys = array_keys( $sf );
			foreach( $keys as $k ){
				if( ! isset($notNull[$k]) ){
					unset( $sf[$k] );
				}
			}

			reset( $thisArrayOfValues );
			foreach( $thisArrayOfValues as $values ){
				reset( $sf );
				$thisSv = [];
				foreach( $sf as $k ){
					if( ! isset($values[$k]) ){
						$thisSv[] = 'NULL';
					}
					else {
						$sqlArg[] = $values[$k];
						$isInt = is_numeric( $values[$k] ) && ( strlen($values[$k]) < 12 );

						if( $isInt ){
							$thisSv[] = '%d';
						}
						else {
							$thisSv[] = '%s';
						}
					}
				}
				$sv[] = '(' . join(', ', $thisSv) . ')';
			}

			$sql .= ' (' . join(', ', $sf) . ') VALUES ' . join(', ', $sv);

			$sqls[] = $sql;
			$sqlArgs[] = $sqlArg;
		}

// _print_r( $sqls );
// _print_r( $sqlArgs );
// exit;

		$ret = [];
		for( $ii = 0; $ii < count($sqls); $ii++ ){
			$sql = $sqls[$ii];
			$sqlArg = $sqlArgs[$ii];
			$ret[] = [ $sql, $sqlArg ];
		}

		return $ret;
	}

	public static function read( $tableName, array $q = [], array $propNameList = [] )
	{
		$sql = '';
		$sqlArg = [];

	// select
		// $sql = 'SELECT * FROM ' . $tableName;
		$sql = 'SELECT ';
		if( $propNameList ){
			// $sql .= join( ', ', $propNameList );
			$propNameString = [];
			foreach( $propNameList as $k => $f ){
				$propNameString[] = ( $k == $f ) ? $k : $k . ' AS ' . $f;
			}
			$sql .= join( ', ', $propNameString );
		}
		else {
			$sql .= ' *';
		}

		$sql .= ' FROM ' . $tableName;

		$q = Q::normalize( $q );

	// where
		if( $q['where'] ){
			list( $whereSql, $whereSqlArg ) = static::whereToSql( $q['where'] );
			$sql .= $whereSql;
			$sqlArg = array_merge( $sqlArg, $whereSqlArg );
		}

	// order
		if( $q['order'] ){
			$s = [];
			foreach( $q['order'] as $w ){
				list( $name, $direction ) = $w;
				$thisS = $name . ' ' . $direction;
				$s[ $thisS ] = $thisS;
			}
			$sql .= ' ORDER BY ' . join( ', ', $s );
		}

	// limit
		if( $q['limit'] OR $q['offset'] ){
			if( $q['limit'] && $q['offset'] ){
				$sql .= ' LIMIT ' . $q['offset'] . ', ' . $q['limit'];
			}
			elseif( $q['limit'] ){
				$sql .= ' LIMIT ' . $q['limit'];
			}
		}

		$ret = [ $sql, $sqlArg ];
		return $ret;
	}

	public static function count( $tableName, array $q, $groupBy = null )
	{
		$ret = 0;

		$sql = '';
		$sqlArg = [];

	// select
		// $sql = 'SELECT COUNT(*) AS count FROM ' . $tableName;
		$sql = 'SELECT COUNT(*) AS count';
		if( null !== $groupBy ){
			$sql .= ', ' . $groupBy;
		}
		$sql .= ' FROM ' . $tableName;

	// where
		$q = Q::normalize( $q );
		list( $whereSql, $whereSqlArg ) = static::whereToSql( $q['where'] );
		$sql .= $whereSql;
		$sqlArg = array_merge( $sqlArg, $whereSqlArg );

		if( null !== $groupBy ){
			$sql .= ' GROUP BY ' . $groupBy;
		}

	// order
		if( $q['order'] ){
			$s = [];
			foreach( $q['order'] as $w ){
				list( $name, $direction ) = $w;
				$thisS = $name . ' ' . $direction;
				$s[ $thisS ] = $thisS;
			}
			$sql .= ' ORDER BY ' . join( ', ', $s );
		}

	// limit
		if( $q['limit'] OR $q['offset'] ){
			if( $q['limit'] && $q['offset'] ){
				$sql .= ' LIMIT ' . $q['offset'] . ', ' . $q['limit'];
			}
			elseif( $q['limit'] ){
				$sql .= ' LIMIT ' . $q['limit'];
			}
		}


		$ret = [ $sql, $sqlArg ];
		return $ret;
	}

	public static function update( $tableName, array $values, array $q )
	{
		$ret = null;
		if( ! $values ){
			return $ret;
		}

		$sql = '';
		$sqlArg = [];

	// update
		$sql = 'UPDATE ' . $tableName;

	// fields
		$s = [];
		foreach( $values as $k => $v ){
			$isInt = is_numeric( $v ) && ( strlen($v) < 12 );

			if( is_array($v) ){
				$v = json_encode( $v );
			}

			$thisS = $k . '=';
			if( $isInt ){
				$thisS .= '%d';
			}
			else {
				$thisS .= '%s';
			}

			$s[] = $thisS;
			$sqlArg[] = $v;
		}

		if( ! $s ){
			return $ret;
		}

		$sql .= ' SET ' . join(', ', $s);

	// where
		$q = Q::normalize( $q );
		list( $whereSql, $whereSqlArg ) = static::whereToSql( $q['where'] );
		$sql .= $whereSql;
		$sqlArg = array_merge( $sqlArg, $whereSqlArg );

		$ret = [ $sql, $sqlArg ];
		return $ret;
	}

	public static function delete( $tableName, array $q )
	{
		$q = Q::normalize( $q );

		if( ! $q['where'] ){
			exit( __METHOD__ . ': ' . __LINE__ . ': cannot proceed without conditions!' );
		}

		$sql = '';
		$sqlArg = [];

	// sql
		$sql = 'DELETE FROM ' . $tableName;

	// where
		list( $whereSql, $whereSqlArg ) = static::whereToSql( $q['where'] );
		$sql .= $whereSql;
		$sqlArg = array_merge( $sqlArg, $whereSqlArg );

		$ret = [ $sql, $sqlArg ];
		return $ret;
	}

/* database forge */
	public static function dbDropColumn( $tableName, $name )
	{
		$sql = 'ALTER TABLE ' . $tableName . ' DROP COLUMN ' . $name;
		$arg = [];
		$ret = [ $sql, $arg ];
		return $ret;
	}

	public static function dbDropTable( $tableName )
	{
		// $sql = 'DROP TABLE ' . $tableName;
		$sql = 'DROP TABLE IF EXISTS ' . $tableName;
		$arg = [];
		$ret = [ $sql, $arg ];
		return $ret;
	}

	public static function dbAddColumn( $tableName, $k, array $f )
	{
		$intTypes = static::$intTypes;

		$sqlArg = [];

		$sql = '';
		$sql .= 'ALTER TABLE ' . $tableName . ' ADD ';

		$sql .= $k . ' ' . $f['type'];
		$sql .= ( isset($f['null']) && $f['null'] ) ? ' NULL' : ' NOT NULL';

		if( isset($f['default']) && (! is_array($f['default'])) ){
			$sql .= ' DEFAULT ';

			$isInt = in_array( $f['type'], $intTypes ) ? true : false;
			if( $isInt ){
				$sql .= '%d';
			}
			else {
				$sql .= '%s';
			}
			$sqlArg[] = $f['default'];
		}

		$ret = [ $sql, $sqlArg ];
		return $ret;
	}

	public static function dbEmptyTable( $tableName )
	{
		// $sql = 'TRUNCATE ' . $tableName;
		$sql = 'DELETE FROM ' . $tableName;
		$sqlArg = [];

		$ret = [ $sql, $sqlArg ];
		return $ret;
	}

	public static function dbCreateTable( $tableName, array $fields, array $uniques = [] )
	{
		$intTypes = static::$intTypes;

		$sqlArg = [];

		$sql = '';
		$sql .= 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' (';

		$s = [];
		$ks = [];
		reset( $fields );
		foreach( $fields as $k => $f ){
			$thisS = $k . ' ' . $f['type'];
			$thisS .= ( isset($f['null']) && $f['null'] ) ? ' NULL' : ' NOT NULL';
			if( isset($f['auto_increment']) && $f['auto_increment'] ){
				$thisS .= ' AUTO_INCREMENT';
			}

			if( isset($f['default']) && (! is_array($f['default'])) ){
				$thisS .= ' DEFAULT ';

				$isInt = ( isset($f['type']) && in_array($f['type'], $intTypes) ) ? true : false;
				if( $isInt ){
					$thisS .= '%d';
				}
				else {
					$thisS .= '%s';
				}
				$sqlArg[] = $f['default'];
			}

			$s[] = $thisS;

			if( isset($f['key']) && $f['key'] ){
				$ks[ $k ] = $k;
			}
		}

		if( $ks ){
			$s[] = ' CONSTRAINT ' . join('_', $ks) . ' PRIMARY KEY(' . join(',', $ks) . ')';
		}

		if( $uniques ){
			foreach( $uniques as $unique ){
				if( is_array($unique) ){
					$unique = join( ', ', $unique );
				}
				$s[] = ' UNIQUE(' . $unique . ')';
			}
		}

		$sql .= join( ', ', $s );
		$sql .= ')';

		$ret = [ $sql, $sqlArg ];
		return $ret;
	}
/* end of database forge */

	public static function whereToSql( array $where )
	{
		$sql = '';

		$s = [];
		$sqlArgs = [];

		if( in_array(false, $where) ){
			$s[] = '1=%d';
			$sqlArgs[] = 0;

			$sql .= ' WHERE ' . join( ' AND ', $s );
			$ret = [ $sql, $sqlArgs ];
			return $ret;
		}

		foreach( $where as $k => $w ){
			list( $name, $compare, $value ) = $w;

			if( null === $value ){
				if( '=' == $compare ) $compare = ' IS ';
				if( '<>' == $compare ) $compare = ' IS NOT ';
			}

			if( is_array($value) ){
				$isInt = true;
				foreach( $value as $v ){
					$thisIsInt = is_numeric( $v ) && ( strlen($v) < 12 );
					if( ! $thisIsInt ){
						$isInt = false;
						break;
					}
				}
				if( $isInt ){
					$value = array_map( 'intval', $value );
				}
			}
			else {
				$isInt = is_numeric( $value ) && ( strlen($value) < 12 );
				// $isInt = is_numeric( $value );
			}

			if( 'LIKE' === $compare ) $compare = ' LIKE ';
			if( 'CONTAINS' === $compare ) $compare = ' LIKE ';

			if( $isInt ){
				$thisV = '%d';
			}
			else {
				$thisV = '%s';
			}

			if( is_array($value) && ('=' == $compare) ){
				if( $value ){
					$compare = ' IN ';
					$thisV = array_fill( 0, count($value), $thisV );
					$thisV = '(' . join( ',', $thisV ) . ')';
					foreach( $value as $v ) $sqlArgs[] = $v;
				}
				else {
					// echo "EMPTY ARRAY!";
					$name = '1';
					$compare = '=';
					$thisV = '0';
				}
			}
			elseif( is_array($value) && ('<>' == $compare) ){
				if( $value ){
					$compare = ' NOT IN ';
					$thisV = array_fill( 0, count($value), $thisV );
					$thisV = '(' . join( ',', $thisV ) . ')';
					foreach( $value as $v ) $sqlArgs[] = $v;
				}
				else {
					// echo "EMPTY ARRAY!";
					$name = '1';
					$compare = '=';
					$thisV = '1';
				}
			}
			else {
				$sqlArgs[] = $value;
			}

		// need to concat?
			if( false !== strpos($name, '-') ){
				$nameList = explode( '-', $name );
				$name = 'CONCAT(';
				for( $i = 0; $i < count($nameList); $i++ ){
					if( $i ){
						$name .= ',\'-\',';
					}
					$name .= $nameList[$i];
				}
				$name .= ')';
			}

			$thisS = $name . $compare . $thisV;
			$s[] = $thisS;
		}

		if( $s ){
			$sql .= ' WHERE ' . join( ' AND ', $s );
		}

		// if( ! $s ){
			// $s[] = '1=%d';
			// $sqlArgs[] = 1;
		// }

		// $sql .= ' WHERE ' . join( ' AND ', $s );
		$ret = [ $sql, $sqlArgs ];

		return $ret;
	}
}