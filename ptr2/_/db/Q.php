<?php
namespace Plainware;

class Q
{
/* split conditions into 1) with name, 2) without name */
	public static function splitByName( array $q, $pName )
	{
		$ret1 = $ret2 = [];
		$pNameList = is_array( $pName ) ? $pName : [ $pName ];

		foreach( $q as $e ){
			$prop = $e[0];
			if( 'limit' == $prop ){
				$prop = null;
			}
			elseif( 'offset' == $prop ){
				$prop = null;
			}
			elseif( 'order' == $prop ){
				$prop = $e[1];
			}
			if( ! $prop ) continue;

			if( in_array($prop, $pNameList) ){
				$ret1[] = $e;
			}
			else {
				$ret2[] = $e;
			}
		}

		$ret = [ $ret1, $ret2 ];
		return $ret;
	}

/* gets conditions by name */
	public static function getWhereByName( array $q, $pName, $compare = null )
	{
		$q = static::normalize( $q );

		$pNameList = is_array( $pName ) ? $pName : [ $pName ];

		if( null === $compare ){
			$ret = [];
			foreach( array_keys($q['where']) as $id ){
				if( ! in_array($q['where'][$id][0], $pNameList) ) continue;
				// if( $q['where'][$id][0] != $pName ) continue;
				$ret[] = $q['where'][$id];
			}
		}
		else {
			$ret = null;

			foreach( array_keys($q['where']) as $id ){
				// if( $q['where'][$id][0] != $pName ) continue;
				if( ! in_array($q['where'][$id][0], $pNameList) ) continue;
				if( $q['where'][$id][1] != $compare ) continue;

				$v = $q['where'][$id][2];

				if( null === $ret ){
					$ret = $v;
					continue;
				}

				switch( $compare ){
					case '>':
					case '>=':
						if( $val > $ret ){
							$ret = $val;
						}
						break;
					case '<':
					case '<=':
						if( $val < $ret ){
							$ret = $val;
						}
						break;
					case '=':
						$ret = $val;
						break;
					case '<>':
						break;
				}
			}
		}

		return $ret;
	}

	public static function hasWhere( array $q, $pName )
	{
		$ret = false;

		$q = static::normalize( $q );
		$pNameList = is_array( $pName ) ? $pName : [ $pName ];

		foreach( array_keys($q['where']) as $id ){
			if( ! in_array($q['where'][$id][0], $pNameList) ) continue;
			$ret = true;
			break;
		}

		return $ret;
	}

	public function removeWhere( array $q, $pName )
	{
		$q = static::normalize( $q );

		$pNameList = is_array( $pName ) ? $pName : [ $pName ];

		foreach( array_keys($q['where']) as $id ){
			if( ! in_array($q['where'][$id][0], $pNameList) ) continue;
			unset( $q['where'][$id] );
		}

		return $q;
	}

	public static function has( array $q, $pName )
	{
		$ret = false;

		foreach( $q as $qa ){
			if( isset($qa[0]) && ($qa[0] == $pName) ){
				$ret = true;
				break;
			}
		}

		return $ret;
	}

	public static function convertNames( array $q, array $fromTo )
	{
		$q = static::normalize( $q );

		foreach( array_keys($q['where']) as $i ){
			if( false === $q['where'][$i] ){
				continue;
			}

		// concat?
			if( false !== strpos($q['where'][$i][0], '-') ){
				$change = false;
				$nameList = explode( '-', $q['where'][$i][0] );
				for( $j = 0; $j < count($nameList); $j++ ){
					if( isset($fromTo[$nameList[$j]]) ){
						$nameList[$j] = $fromTo[ $nameList[$j] ];
						$change = true;
					}
				}
				if( $change ){
					$q['where'][$i][0] = join( '-', $nameList );
				}
			}
			else {
				if( isset($fromTo[$q['where'][$i][0]]) ){
					$q['where'][$i][0] = $fromTo[$q['where'][$i][0]];
				}
			}
		}

		foreach( array_keys($q['order']) as $i ){
			if( isset($fromTo[$q['order'][$i][0]]) ){
				$q['order'][$i][0] = $fromTo[$q['order'][$i][0]];
			}
		}

		return $q;
	}

	public static function normalize( array $q )
	{
	// already normalized?
		$already = true;
		$needs = [ 'where', 'order', 'limit', 'offset' ];
		foreach( $needs as $need ){
			if( ! array_key_exists($need, $q) ){
				$already = false;
				break;
			}
		}
		if( $already ) return $q;

		$where = [];
		$order = [];
		$limit = 0;
		$offset = 0;

		foreach( $q as $k => $v ){
			if( false === $v ){
				// $where[] = [ 1, '=', 0 ];
				$where[] = false;
				continue;
			}

			if( is_int($k) ){
				$name = $v[0];
			}
			else {
				$name = $k;
			}

			$name = trim( $name );
			$spacePos = strpos( $name, ' ' );

			if( false !== $spacePos ){
				list( $name, $part2 ) = explode( ' ', $name );

				if( 'order' == $name ){
					$prop = $part2;
					$direction = $v;
					$order[] = [ $prop, $direction ];
				}
				else {
					$compare = $part2;
					$where[] = [ $name, $compare, $v ];
				}
			}
			else {
				if( 'limit' == $name ){
					$limit = is_array($v) ? $v[1] : $v;
				}
				elseif( 'offset' == $name ){
					$offset = is_array($v) ? $v[1] : $v;
				}
				elseif( 'order' == $name ){
					$prop = $v[1];
					$direction = isset( $v[2] ) ? $v[2] : 'ASC';
					$order[] = [ $prop, $direction ];
				}
				else {
				// already ready
					if( is_array($v) && is_int($k) && (3 == count($v)) ){
						$where[] = $v;
					}
					else {
						$name = $k;
						if( is_string($v) && ('!' == substr($v, 0, 1)) ){
							$compare = '<>';
							$v = substr( $v, 1 );
							if( 'null' === $v ) $v = null;
						}
						else {
							$compare = '=';
						}
						$where[] = [ $name, $compare, $v ];
					}
				}
			}
		}

		$ret = [
			'where' => $where,
			'order' => $order,
			'limit' => $limit,
			'offset' => $offset,
		];

		return $ret;
	}

	public static function order( array $objects, array $orderBy )
	{
		$ret = $objects;

	// orderby
		if( ! $orderBy ){
			return $ret;
		}

		uasort( $ret, function($a, $b) use( $orderBy ){
			$ret = 0;
			reset( $orderBy );
			foreach( $orderBy as $w ){
				list( $name, $direction ) = $w;

				if( 'ASC' == $direction ){
					if( is_object($a) ){
						$cmp1 = property_exists($a, $name) ? $a->{$name} : null;
					}
					else {
						$cmp1 = isset( $a[$name] ) ? $a[$name] : null;
					}

					if( is_object($b) ){
						$cmp2 = property_exists($b, $name) ? $b->{$name} : null;
					}
					else {
						$cmp2 = isset( $b[$name] ) ? $b[$name] : null;
					}
				}
				else {
					if( is_object($b) ){
						$cmp1 = property_exists($b, $name) ? $b->{$name} : null;
					}
					else {
						$cmp1 = isset( $b[$name] ) ? $b[$name] : null;
					}

					if( is_object($a) ){
						$cmp2 = property_exists($a, $name) ? $a->{$name} : null;
					}
					else {
						$cmp2 = isset( $a[$name] ) ? $a[$name] : null;
					}
				}

				if( (null === $cmp1) && (null === $cmp2) ){
					$ret = 0;
				}
				elseif( null === $cmp2 ){
					$ret = 1;
				}
				elseif( null === $cmp1 ){
					$ret = -1;
				}
				elseif( is_numeric($cmp1) && is_numeric($cmp2) ){
					$ret = ( $cmp1 - $cmp2 );
				}
				else {
					$ret = strcmp( $cmp1, $cmp2 );
				}

				if( $ret ){
					return $ret;
				}
			}
			return $ret;
		});

		return $ret;
	}

	public static function filterOr( array $objects, array $qs ) // OR comparision of $qs
	{
		$ret = [];

		foreach( array_keys($qs) as $ii ){
			$qs[$ii] = static::normalize( $qs[$ii] );
		}

		foreach( $objects as $id => $obj ){
			$ok = false;
			reset( $qs );
			foreach( $qs as $q ){
				if( static::checkWhere($obj, $q['where']) ){
					$ok = true;
					break;
				}
			}
			if( ! $ok ) continue;
			$ret[ $id ] = $obj;
		}

		$q = reset( $qs );

		if( $q['order'] ){
			$ret = static::order( $ret, $q['order'] );
		}

		if( $q['limit'] && (count($ret) > $q['limit']) ){
			$ret = array_slice( $ret, $q['offset'], $q['limit'], true );
		}

		return $ret;
	}

	public static function filter( array $objects, array $q )
	{
		$q = static::normalize( $q );

		$ret = [];
		foreach( $objects as $id => $obj ){
			if( ! static::checkWhere($obj, $q['where']) ) continue;
			$ret[ $id ] = $obj;
		}

		if( $q['order'] ){
			$ret = static::order( $ret, $q['order'] );
		}

		if( $q['limit'] && (count($ret) > $q['limit']) ){
			$ret = array_slice( $ret, $q['offset'], $q['limit'], true );
		}

		return $ret;
	}

	public static function check( $obj, array $q )
	{
		$q = static::normalize( $q );
		return static::checkWhere( $obj, $q['where'] );
	}

	public static function checkWhere( $obj, array $where )
	{
		$ret = true;

		foreach( $where as $w ){
			$thisRet = static::checkOne( $obj, $w );
			if( ! $thisRet ){
				$ret = false;
				break;
			}
		}

		return $ret;
	}

	public static function getCompares()
	{
		$ret = [];

		$ret[ '=' ] = 1;
		$ret[ '<>' ] = 1;
		$ret[ '>' ] = 1;
		$ret[ '>=' ] = 1;
		$ret[ '<' ] = 1;
		$ret[ '<=' ] = 1;
		$ret[ 'CONTAINS' ] = 1;

		return $ret;
	}

	public static function checkOne( $obj, $cond )
	{
		$ret = false;

		if( false === $cond ){
			return $ret;
		}

		list( $k, $compare, $to ) = $cond;

		if( is_object($obj) ){
			if( ! property_exists($obj, $k) ){
				return $ret;
			}
			$v = $obj->{$k};
		}
		else {
			if( ! array_key_exists($k, $obj) ){
				// $ret = true;
				return $ret;
			}
			$v = $obj[$k];
		}

		// if( null === $v ){
			// $ret = true;
			// return $ret;
		// }

		switch( $compare ){
			case '=':
				if( is_array($to) ){
					if( is_array($v) ){
						$ret = array_intersect($v, $to) ? true : false;
					}
					else {
						$ret = in_array( $v, $to );
					}
				}
				else {
					if( is_array($v) ){
						$ret = in_array( $to, $v );
					}
					else {
						$ret = ( $v == $to );
					}
				}
				break;

			case '<>':
				if( is_array($to) ){
					$ret = ! in_array( $v, $to );
				}
				else {
					$ret = ( $v != $to );
				}
				break;

			case '>':
				$ret = ( $v > $to );
				break;

			case '&':
				$ret = ( $v & $to );
				break;

			case '>=':
				$ret = ( $v >= $to );
				break;

			case '<':
				$ret = ( $v < $to );
				break;

			case '<=':
				$ret = ( $v <= $to );
				break;

			case 'CONTAINS':
			case 'LIKE':
				if( is_array($to) ){
					if( is_array($v) ){
						$ret = array_intersect($v, $to) ? true : false;
					}
					else {
						$ret = in_array( $v, $to );
					}
				}
				else {
					$ret = ( false === strpos($v, $to) ) ? false : true;
				}
				break;

			default:
				exit( __METHOD__ . ": unknown compare: '$compare'" );
				break;
		}

		return $ret;
	}
}