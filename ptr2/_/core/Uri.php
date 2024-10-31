<?php
namespace Plainware;

class _Uri
{
	public $slug = '';
	public $params = [];
	public $parent;
}

class Uri
{
	public static $stackSeparator = '--';
	public static $stackParamPrefix = '-';
	public static $tempParamSuffix = '-';

	// for wordpress
	// public static $slugParam = 'pw';
	// public static $paramPrefix = 'pw_';

	public static $slugParam = 'p';
	public static $paramPrefix = '';
// don't want to have arrays in URL, convert: k[]=1&k[]=2&k[]=3 to k_0=1&k_1=2&k_2=3
	public static $convertArray = true;
	public $self = __CLASS__;

	public function construct()
	{
		$ret = new _Uri;
		return $ret;
	}

	public function getLayoutParam()
	{
		// $ret = $this->self->getSlugParam() . static::$tempParamSuffix;
		$ret = 'layout' . static::$tempParamSuffix;
		return $ret;
	}

	public function getParamPrefix()
	{
		return static::$paramPrefix;
	}

	public function getSlugParam()
	{
		return static::$slugParam;
	}

// maybe redefine this one
	public function toHref( array $queryParams )
	{
		$ret = '';
		if( $queryParams ){
			ksort( $queryParams );
			$ret .= '?' . http_build_query( $queryParams );
		}
		return $ret;
	}

	public function fromString( $string, _Uri $current, $quick = false )
	{
		if( $quick ){
			$ret = $this->self->make( $string, [], $current );
			return $ret;
		}

		$string = htmlspecialchars_decode( $string );

	// json encoded?
		if( '[' == substr($string, 0, 1) ){
			list( $slug, $params ) = json_decode( $string, true );
		}
		else {
			$slug = $string;
			$params = [];

			$pos = strpos( $string, '?' );
			if( false !== $pos ){
				$slug = substr( $string, 0, $pos );
				$paramString = substr( $string, $pos + 1 );
				parse_str( $paramString, $params );
			}
		}

		$ret = $this->self->make( $slug, $params, $current );
		return $ret;
	}

	public function make( $slug, array $params, _Uri $current )
	{
		$currentParams = $current->params;

	// unset temp
		$currentKeys = array_keys( $currentParams );
		foreach( $currentKeys as $k ){
			if( static::$tempParamSuffix == substr($k, -strlen(static::$tempParamSuffix)) ){
				unset( $currentParams[$k] );
			}
		}

	// clear prefix if already set
		$trimmedParams = [];
		foreach( array_keys($params) as $k ){
			$k2 = $k;
			if( static::$paramPrefix === substr($k, 0, strlen(static::$paramPrefix)) ){
				$k2 = substr( $k, strlen(static::$paramPrefix) );
			}
			$trimmedParams[ $k2 ] = $params[ $k ];
		}
		$params = $trimmedParams;

		$globalParams = [];
		foreach( array_keys($currentParams) as $k ){
			if( '*' !== substr($k, 0, 1) ) continue;
			$globalParams[$k] = $currentParams[$k];
			unset( $currentParams[$k] );
		}

	// if prefixed with parent
		$parentPrefix = '';
		$parentPrefixList = [ '../../', '../..', '../', '..', '.', './.' ];
		foreach( $parentPrefixList as $testParentPrefix ){
			if( $testParentPrefix == substr($slug, 0, strlen($testParentPrefix)) ){
				$parentPrefix = $testParentPrefix;
				$slug = substr( $slug, strlen($testParentPrefix) );
				break;
			}
		}

		$stack = [];

		$currentStack = [];
		$parent = $current->parent;
		while( $parent ){
			$currentStack[] = [ $parent->slug, $parent->params ];
			$parent = $parent->parent;
		}

	// stacked? like slug--parent1--parent2
		$stackSlugs = explode( static::$stackSeparator, $slug );
		$stackLevel = count( $stackSlugs );
		if( $stackLevel > 1 ){
			$slug = array_pop( $stackSlugs );
			foreach( $stackSlugs as $stackSlug ){
				array_unshift( $stack, [$stackSlug, []] );
			}
		}

		if( $parentPrefix ){
			$slug = $parentPrefix . $slug;
// echo "SLUG = '$slug'<br>";
// _print_r( $stack );
		}

		// $slug = array_shift( $stackSlugs );
		// foreach( $stackSlugs as $stackSlug ) $stack[] = [ $stackSlug, [] ];

	// is ../.. move to second in stack
		if( ('../..' == $slug) ){
			$stack = $currentStack;

			if( $stack ){
				$e = array_shift( $stack );
				if( $stack ){
					$e = array_shift( $stack );
				}
				$slug = $e[0];
				$params += $e[1];
			}
			else {
				$slug = '';
			}
		}

	// is .. move up one step in stack
		if( '..' == $slug ){
			$stack2 = $currentStack;
			$e = array_shift( $stack2 );
			if( $e ){
				$slug = $e[0];
				$params += $e[1];
			}
			else {
				$slug = '';
			}

			$stack = array_merge( $stack, $stack2 );
		}

	// starts with parent
		if( '../../' == substr($slug, 0, strlen('../../')) ){
			$slug = substr( $slug, strlen('../../') );
			$stack2 = $currentStack;
			array_shift( $stack2 );
			$stack = array_merge( $stack, $stack2 );
		}

	// starts with parent
		if( '../' == substr($slug, 0, strlen('../')) ){
			$slug = substr( $slug, strlen('../') );
			$stack2 = $currentStack;
			$stack = array_merge( $stack, $stack2 );
			// array_shift( $stack );
		}

	// starts with . - add current to stack
		if( '.' == substr($slug, 0, 1) ){
			$slug = substr( $slug, 1 );

		// current and next
			if( strlen($slug) ){
				$stack2 = $currentStack;
				array_unshift( $stack2, [$current->slug, $currentParams] );
				$stack = array_merge( $stack, $stack2 );
			}
			else {
				$stack = $currentStack;
			}

		// just current
			if( (! strlen($slug)) OR ('/.' == $slug) ){
				$slug = $current->slug;

				$keys = array_keys( $currentParams );
				foreach( $keys as $k ){
				// append to currentParams?

					if( array_key_exists($k, $params) ){
// merging new params and currentParams
						if( is_array($params[$k]) && is_array($currentParams[$k]) ){
							$append = false;
							$thisParams = $append ? $currentParams[$k] : [];
							foreach( array_keys($params[$k]) as $k2 ){
								$thisParams[ $k2 ] = $params[$k][$k2];
							}
							$params[$k] = $thisParams;
						}
					}
					else {
						$params[ $k ] = $currentParams[ $k ];
					}
				}
			}
		}

		$params += $globalParams;

	// redefine stack params if any
		$stackLevel = count( $stack );
		while( $stackLevel ){
			$checkStackPrefix = str_repeat( static::$stackParamPrefix, $stackLevel );
			$len = strlen( $checkStackPrefix );
			foreach( array_keys($params) as $k ){
				if( substr($k, 0, $len) != $checkStackPrefix ) continue;
				$k2 = substr( $k, $len );
				$stack[ $stackLevel - 1 ][ 1 ][ $k2 ] = $params[ $k ];
				unset( $params[$k] );
			}
			$stackLevel--;
		}

// echo "PARAMS";
// _print_r( $params );

		if( static::$convertArray ){
			$params = $this->_unflattenArray( $params );
			foreach( array_keys($stack) as $stackIndex ){
				$stack[$stackIndex][1] = $this->_unflattenArray( $stack[$stackIndex][1] );
			}
		}

		$ret = $this->self->construct();

		$ret->slug = $slug;
		$ret->params = $params;

		$parent = null;
		while( $parentArray = array_pop($stack) ){
			$p = $this->self->construct();
			$p->slug = $parentArray[0];
			$p->params = $parentArray[1];
			if( $parent ){
				$p->parent = $parent;
			}
			$parent = $p;
		}
		$ret->parent = $parent;

		return $ret;
	}

	public function fromQueryParams( array $queryParams = [] )
	{
// echo "QUERY PARAMS:";
// _print_r( $queryParams );

// $queryParams = [ 'p' => 'twoslug--oneslug', '-p' => 'twoslug', 'a' => '1one', 'b' => '1two', '-a' => '2one', '-c' => '2two' ];
// _print_r( $queryParams );

		$slug = $queryParams[static::$slugParam] ?? '';
		unset( $queryParams[static::$slugParam] );

		$params = [];

	// our param at all?
		if( strlen(static::$paramPrefix) ){
			$keys = array_keys( $queryParams );
			foreach( $keys as $k ){
				if( static::$paramPrefix === substr($k, 0, strlen(static::$paramPrefix)) ){
					$k2 = substr( $k, strlen(static::$paramPrefix) );
					$queryParams[ $k2 ] = $queryParams[ $k ];
				}
				unset( $queryParams[ $k ] );
			}
		}

	// is stack?
		$stack = [];
		$stackedSlugs = explode( self::$stackSeparator, $slug );
		if( count($stackedSlugs) > 1 ){
			$slug = array_pop( $stackedSlugs );
			foreach( array_reverse($stackedSlugs) as $parentSlug ){
				$stack[] = [ $parentSlug, [] ];
			}
		}

		foreach( array_reverse(array_keys($stack)) as $stackIndex ){
			$checkStackPrefix = str_repeat( static::$stackParamPrefix, $stackIndex + 1 );
			$len = strlen( $checkStackPrefix );
			foreach( array_keys($queryParams) as $k ){
				if( substr($k, 0, $len) != $checkStackPrefix ) continue;
				$k2 = substr( $k, $len );
				$stack[ $stackIndex ][ 1 ][ $k2 ] = $queryParams[ $k ];
				unset( $queryParams[$k] );
			}
		}

		$params = $queryParams;

		if( static::$convertArray ){
			$params = $this->_unflattenArray( $params );
			foreach( array_keys($stack) as $stackIndex ){
				$stack[$stackIndex][1] = $this->_unflattenArray( $stack[$stackIndex][1] );
			}
		}

// echo "MY PARAMS&STACK:";
// _print_r( $params );
// _print_r( $stack );

		$ret = $this->self->construct();

		$ret->slug = $slug;
		$ret->params = $params;

		$parent = null;
		while( $parentArray = array_pop($stack) ){
			$p = $this->self->construct();
			$p->slug = $parentArray[0];
			$p->params = $parentArray[1];
			if( $parent ){
				$p->parent = $parent;
			}
			$parent = $p;
		}
		$ret->parent = $parent;

		return $ret;
	}

	public function toQueryParams( _Uri $uri )
	{
// echo "TO QUERY";
// _print_r( $uri );

		$p = [];

		$slug = $uri->slug;
		$p[ static::$slugParam ] = $slug;

		foreach( $uri->params as $k => $v ){
			$k2 = static::$paramPrefix . $k;
// echo "SETTING '$k2' FROM MAIN!<br>";
			$p[ $k2 ] = $v;
		}

	// layout param?
		if( isset($p['layout']) ){
			$layoutParam = $this->self->getLayoutParam();
			$p[ $layoutParam ] = $p['layout'];
			unset( $p['layout'] );
		}

		$fullSlug = $slug;

	// stacked slug
		$parent = $uri->parent;
		$ii = 0;
		while( $parent ){
			$ii++;
			$stackParamPrefix = str_repeat( static::$stackParamPrefix, $ii );
// echo "SPP = '$stackParamPrefix' FOR '$ii'<br>";

			$thisSlug = $parent->slug;
			if( strlen($thisSlug) ){
				$fullSlug = strlen( $fullSlug ) ? $thisSlug . static::$stackSeparator . $fullSlug : $thisSlug;
			}

			foreach( $parent->params as $k => $v ){
				$k2 = static::$paramPrefix . $stackParamPrefix . $k;

				$setThis = true;
			// already exists and set to null
			// that means the child wants to reset parent's param
				if( array_key_exists($k2, $p) ){
					if( (null === $p[$k2]) OR ('null' === $p[$k2]) ){
						$setThis = false;
					}
				}

				if( $setThis ){
					if( isset($p[$k2]) ){
						echo "SETTING '$k2' FROM STACK2 BUT ITS ALREADY SET!<br>";
					}
					else {
						$p[ $k2 ] = $v;
					}
				}
			}
			$parent = $parent->parent;
		}

		// echo "FULL SLUG = '$fullSlug'<br>";
		$p[ static::$slugParam ] = $fullSlug;

		if( static::$convertArray ){
			// $p = $this->_flattenArray( $p );

		// flatten array parts
			$p2 = [];
			$keys = array_keys( $p );
			foreach( $keys as $k ){
				if( ! is_array($p[$k]) ) continue;
				$p2[$k] = $p[$k];
				unset( $p[$k] );
			}

			if( $p2 ){
				$p2 = $this->_flattenArray( $p2 );
				foreach( $p2 as $k2 => $v2 ){
				// if meanwhile a flattened key was defined then skip it
					if( array_key_exists($k2, $p) ){
						$v2 = $p[$k2];
						unset( $p[$k2] );
					}
					$p[$k2] = $v2;
				}
			}
		}

	// remove 'null' values
		$keys = array_keys($p);
// _print_r( $p );
		foreach( $keys as $k ){
			if( 'null' === $p[$k] ){
				unset($p[$k]);
				continue;
			}

			if( is_array($p[$k]) ){
				$keys2 = array_keys($p[$k]);
				foreach( $keys2 as $k2 ){
					if( 'null' === $p[$k][$k2] ) unset($p[$k][$k2]);
				}
			}
		}

		return $p;
	}

// our internal representation
	public function toString( _Uri $uri )
	{
		$p = $this->self->toQueryParams( $uri );

		$ret = $p[static::$slugParam];
		unset( $p[static::$slugParam] );

		ksort( $p );
		$queryString = $p ? http_build_query( $p ) : '';
		if( strlen($queryString) ) $ret .= '?' . $queryString;

		return $ret;
	}

	public function isFull( $uriString )
	{
		$ret = false;
		if( null === $uriString ){
			return $ret;
		}

		if( is_array($uriString) ){
			return $ret;
		}

		$prfx = [ 'http://', 'https://', '//', 'webcal://' ];
		reset( $prfx );
		foreach( $prfx as $prf ){
			if( substr($uriString, 0, strlen($prf)) == $prf ){
				$ret = true;
				break;
			}
		}

		return $ret;
	}

	public function to( $to, $x )
	{
		$currentUri = $x[ '$uri' ];

		list( $to, $params ) = is_array($to) ? $to : [ $to, [] ];
		$uri = $this->self->fromString( $to, $currentUri, true );
		if( $params ){
			$uri->params = array_merge( $uri->params, $params );
		}

		$p = $this->self->toQueryParams( $uri );
		$ret = $this->self->toHref( $p );

		return $ret;
	}

	protected function _flattenArray( array $p, $sep = '_' )
	{
		$keys = array_keys( $p );
		foreach( $keys as $k ){
			if( ! is_array($p[$k]) ) continue;

			foreach( $p[$k] as $k2 => $v2 ){
				if( is_array($v2) ){
					$p3 = $this->_flattenArray( $v2, $sep );
					foreach( $p3 as $k3 => $v3 ){
						$p[ $k . $sep . $k2 . $sep . $k3 ] = $v3;
					}
				}
				else {
					$p[ $k . $sep . $k2 ] = $v2;
				}
			}
			unset( $p[$k] );
		}

		return $p;
	}

	protected function _unflattenArray( array $data, $sep = '_' )
	{
		$ret = [];

		foreach( $data as $k => $v ){
			$parts = explode( $sep, $k );
			$nested = &$ret;
			while( count($parts) > 1 ){
				$nested = &$nested[ array_shift($parts) ];
				if( ! is_array($nested) ) $nested = [];
			}
		$nested[ array_shift($parts) ] = $v;
		}

		return $ret;
	}
}