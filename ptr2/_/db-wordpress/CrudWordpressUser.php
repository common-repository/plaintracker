<?php
namespace Plainware;

class CrudWordpressUser extends Crud
{
	public static $fields = [
		'ID'				=> [ 'alias' => 'id' ],
		'display_name'	=> [ 'alias' => 'title' ],
		'user_email'	=> [ 'alias' => 'email' ],
		// 'color'			=> [ 'meta' => true ],
	];

	public $q = \Plainware\Q::class;

	public function convertQ( array $q = [] )
	{
		$propCore = $propMeta = $alias = [];

		foreach( static::$fields as $k => $field ){
			if( isset($field['alias']) ){
				$listAliasK = is_array( $field['alias'] ) ? $field['alias'] : [ $field['alias'] ];
				foreach( $listAliasK as $aliasK ){
					$alias[ $aliasK ] = $k;
				}
			}
			if( isset($field['meta']) && $field['meta'] )
				$propMeta[] = $k;
			else
				$propCore[] = $k;
		}

		if( $alias ){
			$q = $this->q->convertNames( $q, $alias );
		}
		$q = $this->q->normalize( $q );

		$propCore[] = 'role';

		$wpQ = [];
		// $wpQ[ 'post_type' ] = static::$postType;
		// $wpQ[ 'perm' ] = 'readable';

		foreach( $propCore as $k ){
			$listThisWhere = $this->q->getWhereByName( $q, $k );
			foreach( $listThisWhere as $e ){
				list( , $compare, $v ) = $e;

				if( 'ID' == $k ){
					if( in_array($compare, ['=']) ){
						if( is_array($v) && (! $v) ){
							return false;
						}
						$wpQ['include'] = is_array($v) ? $v : [ $v ];
					}
					elseif( in_array($compare, ['<>']) ){
						$wpQ['exclude'] = is_array($v) ? $v : [ $v ];
					}
					else {
						echo "DO NOT KNOW HOW TO CHECK '$k' ON '$compare'<br>";
					}
				}

				elseif( 'role' == $k ){
					if( in_array($compare, ['=']) ){
						$wpQ['role__in'] = is_array($v) ? $v : [ $v ];
					}
					elseif( in_array($compare, ['<>']) ){
						$wpQ['role__not_in'] = is_array($v) ? $v : [ $v ];
					}
					else {
						echo "DO NOT KNOW HOW TO CHECK '$k' ON '$compare'<br>";
					}
				}

				else {
					echo "DO NOT KNOW HOW TO CHECK '$k' ON '$compare'<br>";
				}

				// if( 'post_name' == $k ){
					// if( in_array($compare, ['=']) ){
						// $wpQ['post_name__in'] = is_array($v) ? $v : [ $v ];
					// }
					// else {
						// echo "DO NOT KNOW HOW TO CHECK '$k' ON '$compare'<br>";
					// }
				// }

				// if( 'post_title' == $k ){
					// if( in_array($compare, ['=']) ){
						// $wpQ['title'] = $v;
					// }
					// else {
						// echo "DO NOT KNOW HOW TO CHECK '$k' ON '$compare'<br>";
					// }
				// }

				// if( 'post_status' == $k ){
					// if( in_array($compare, ['=']) ){
						// $wpQ['post_status'] = $v;
					// }
					// else {
						// echo "DO NOT KNOW HOW TO CHECK '$k' ON '$compare'<br>";
					// }
				// }
			}
		}

		// if( ! isset($wpQ['post_status']) ){
			// $wpQ[ 'post_status' ] = [ 'any', 'trash', 'draft' ];
		// }

    // 'meta_query' => array(
        // 'relation' => 'AND',
        // array(
            // 'key'     => 'longitude-key',
            // 'value'   => '',
            // 'compare' => 'NOT'
        // ),
        // array(
            // 'key'     => 'latitude-key',
            // 'value'   => '',
            // 'compare' => 'NOT'
        // ),
        // array(
            // 'key'     => 'name-key',
            // 'value'   => '',
            // 'compare' => 'NOT'
        // ),

		$wpMetaQuery = [];
		foreach( $propMeta as $k ){
			$listThisWhere = $this->q->getWhereByName( $q, $k );
			foreach( $listThisWhere as $e ){
				list( , $compare, $v ) = $e;

				$wpCompare = $compare;
				if( ('=' == $compare) && is_array($v) ){
					$wpCompare = 'IN';
				}
				if( '<>' == $compare ){
					$wpCompare = is_array($v) ? 'NOT IN' : '!=';
				}

				$wpMetaQuery[] = [
					'key' => $k,
					'value' => $v,
					'compare' => $wpCompare
				];
			}
		}

	// limit
		$wpQ[ 'number' ] = $q['limit'] ? $q['limit'] : -1;
		$wpQ[ 'offset' ] = $q['offset'];

	// sort
		$wpOrderby = [];

		foreach( $q['order'] as $e ){
			if( in_array($e[0], $propCore) ){
				$wpOrderby[ $e[0] ] = $e[1];
			}
			else {
				$wpMetaQuery[ $e[0] . '_clause' ] = [ 'key' => $e[0] ];
				$wpOrderby[ $e[0] . '_clause' ] = $e[1];
			}
		}

		if( $wpMetaQuery ){
			$wpMetaQuery['relation'] = 'AND';
			$wpQ[ 'meta_query' ] = $wpMetaQuery;
		}

		if( $wpOrderby ){
			$wpQ['orderby'] = $wpOrderby;
		}

// _print_r( $q );
// _print_r( $wpQ );
// exit;

		return $wpQ;
	}

	public function read( array $q = [], array $listField = [] )
	{
		$propCore = $propMeta = $convertNameToWp = $convertNameFromWp = [];

		foreach( static::$fields as $k => $field ){
			if( isset($field['alias']) ){
				$listAliasK = is_array( $field['alias'] ) ? $field['alias'] : [ $field['alias'] ];
				foreach( $listAliasK as $aliasK ){
					if( $listField && (! in_array($aliasK, $listField)) ) continue;
					$convertNameToWp[ $aliasK ] = $k;
				}
				$convertNameFromWp[ $k ] = $field['alias'];
			}
			else {
				if( $listField && (! in_array($k, $listField)) ) continue;
			}

			if( isset($field['meta']) && $field['meta'] ){
				$propMeta[] = $k;
			}
			else {
				$propCore[] = $k;
			}
		}

		if( $propMeta && (! in_array('ID', $propCore)) ) $propCore[] = 'ID';

		$wpQ = $this->convertQ( $q );

		$ret = [];
		if( false === $wpQ ){
			return $ret;
		}

		if( ! isset($wpQ['fields']) ){
		// limit fields
			if( $listField ){
				$listWpField = [];
				foreach( $listField as $f ){
					$listWpField[] = $convertNameToWp[ $f ];
				}
				$wpQ['fields'] = $listWpField;
			}
			else {
				$wpQ['fields'] = $propCore;
			}
		}

// _print_r( $wpQ );

		$wpQ[ 'count_total' ] = false;

// _print_r( $wpQ );
		$wpUserQuery = new \WP_User_Query( $wpQ );
		$listWpUser = $wpUserQuery->get_results();
// echo 'COUNT = ' . count( $listWpUser ) . '<br>';

// _print_r( $propCore );
// _print_r( $propMeta );

// echo "AXAXA";
// _print_r( $listWpUser );
// exit;
// _print_r( $propMeta );
// exit;

		foreach( $listWpUser as $wpUser ){
			$id = $wpUser->ID ?? null;

			$thisRet = [];
			foreach( $propCore as $k ){
				$v = isset( $wpUser->{$k} ) ? $wpUser->{$k} : null;
				if( isset($convertNameFromWp[$k]) ){
					$listAliasK = is_array($convertNameFromWp[$k]) ? $convertNameFromWp[$k] : [ $convertNameFromWp[$k] ];
					foreach( $listAliasK as $aliasK ){
						$thisRet[ $aliasK ] = $v;
					}
				}
				else {
					$thisRet[ $k ] = $v;
				}
			}

			if( $propMeta ){
				$dictThisWpMeta = get_metadata( 'user', $wpUser->ID );
				foreach( $propMeta as $k ){
					$v = isset( $dictThisWpMeta[$k] ) ? $dictThisWpMeta[$k][0] : null;
					if( isset($convertNameFromWp[$k]) ){
						$listAliasK = is_array($convertNameFromWp[$k]) ? $convertNameFromWp[$k] : [ $convertNameFromWp[$k] ];
						foreach( $listAliasK as $aliasK ){
							$thisRet[ $aliasK ] = $v;
						}
					}
					else {
						$thisRet[ $k ] = $v;
					}
				}
			}

			if( $listField ){
				$justOneProp = ( 1 === count($listField) ) ? current($listField) : false; 

				if( $justOneProp ){
					$thisRet = $thisRet[ $justOneProp ];
					$id = $thisRet;
				}
				else {
					$v = [];
					foreach( $listField as $propName ){
						$v[ $propName ] = $thisRet[ $propName ];
					}
					$id = join( '-', $v );
					$thisRet = $v;
				}
			}

			if( null !== $id ){
				$ret[ $id ] = $thisRet;
			}
			else {
				$ret[] = $thisRet;
			}
		}

		return $ret;
	}

	public function count( array $q = [] )
	{
		$wpQ = $this->convertQ( $q );

		$ret = 0;
		if( false === $wpQ ){
			return $ret;
		}

		$wpQ[ 'count_total' ] = true;
// _print_r( $wpQ );
		$wpUserQuery = new \WP_User_Query( $wpQ );
		$ret = $wpUserQuery->get_total();
		return $ret;
	}

	public function create( array $values ){}
	public function createMany( array $arrayOfValues ){}

	public function update( array $values, array $q ){}
	public function delete( array $q ){}
	public function countMany( $groupBy, array $q = [] ){}
}