<?php
namespace Plainware;

abstract class Model
{
	public static $class = _Model::class;

// default order
	public static $order = [];
	// public static $order = [ ['title', 'ASC'], ['id', 'ASC'] ];

// validation
	public static $required = [];
	public static $unique = [];
	public static $id = -1;

	public $self = __CLASS__;
	public $crud = Crud::class;
	public $q = Q::class;
	public $t = Time::class;

// helpers
	protected $_repo = [];
	protected $_repoVirtual = []; // to keep temporary models if create in series, useful to determine errors

	public function clone( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );

		$ret = clone $m;
		if( property_exists($ret, 'id') ){
			if( $ret->id < 0 ){
				$ret->id = static::$id--;
			}
		}

		return $ret;
	}

	public function setVirtual( array $repo )
	{
		$this->_repoVirtual = $repo;
	}

	public function construct( array $a = [] )
	{
		$class = static::$class;

		$m = new $class;

		foreach( $a as $k => $v ){
			if( property_exists($m, $k) ){
				$m->{ $k } = $v;
			}
		}

	// default negative id for not saved entries
		if( property_exists($m, 'id') ){
			if( null === $m->id ){
				// static $id = -1;
				// $m->id = $id--;
				$m->id = static::$id--;
			}
		}

		return $m;
	}

/* possible options for a property */
	public function enum( $propName, $m = null )
	{
		$ret = [];
		return $ret;
	}

	public function toArray( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );

		$ret = get_object_vars( $m );
		return $ret;
	}

	public function export( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );
		$ret = get_object_vars( $m );
		return $ret;
	}

	public function findProp( $propName, array $q = [] )
	{
		if( ! is_array($propName) ) $propName = [ $propName ];
		$ret = $this->crud->read( $q, $propName );
		return $ret;
	}

	public function getLoaded()
	{
		return $this->_repo;
	}

	public function find( array $q = [] )
	{
		$ret = [];

		if( static::$order ){
			$hasOrder = $this->q->has( $q, 'order' );
			if( ! $hasOrder ){
				foreach( static::$order as $o ){
					$q[] = [ 'order', $o[0], $o[1] ];
				}
			}
		}

		$class = static::$class;
		$hasId = property_exists( $class, 'id' ) ? true : false;

		$rowList = $this->crud->read( $q );
		foreach( $rowList as $row ){
			$m = $this->self->construct( $row );
			$id = $hasId ? $m->id : $this->self->id( $m );
			if( null !== $id ){
				$ret[ $id ] = $m;
				$this->_repo[ $id ] = $m;
			}
			else {
				$ret[] = $m;
			}

			// if( $useId ){
				// $ret[ $m->id ] = $m;
				// $this->_repo[ $m->id ] = $m;
			// }
			// else
				// $ret[] = $m;
		}

		if( $this->_repoVirtual ){
			// echo count( $this->_repoVirtual ) . '<br>';
			$retVirtual = $this->_repoVirtual;
			if( $q ) $retVirtual = $this->q->filter( $retVirtual, $q );
			$ret += $retVirtual;
		}

		if( $q ){
			// $ret = $this->q->filter( $ret, $q );
		}

		return $ret;
	}

	public function getById( $id )
	{
		if( is_array($id) ){
			echo __METHOD__ . ': array input is not allowed';
			return;
		}

		if( isset($this->_repo[$id]) ){
			return $this->_repo[$id];
		}

		$q = [];
		$q[] = [ 'id', '=', $id ];
		$q[] = [ 'limit', 1 ];

		$ret = $this->self->find( $q );
		$ret = $ret ? current( $ret ) : false;

		// $res = $this->crud->read( $q );
		// if( $res ){
			// $row = current( $res );
			// $ret = $this->self->construct( $row );
		// }
		// else {
			// $ret = null;
		// }

		$this->_repo[ $id ] = $ret;

		return $ret;
	}

	public function findById( $id )
	{
		return $this->self->getById( $id );
	}

	// public function findById( $id )
	// {
		// if( (! is_array($id)) && isset($this->_repo[$id]) ){
			// return $this->_repo[$id];
		// }

		// if( is_array($id) && (! $id) ){
			// $ret = [];
			// return $ret;
		// }

		// $ret = $this->self->find( ['id' => $id] );
		// if( ! is_array($id) ){
			// $ret = $ret ? current( $ret ) : null;
		// }

		// return $ret;
	// }

	public function count( array $q = [] )
	{
		$ret = $this->crud->count( $q );
		return $ret;
	}

	public function countTotal()
	{
		static $ret = null;

		if( null === $ret ){
			$q = [];
			$ret = $this->self->count( $q );
			if( ! $ret ) $ret = 0;
		}

		return $ret;
	}

	public function countBy( $byPropName, array $q = [] )
	{
		$ret = $this->crud->countMany( $byPropName, $q );
		return $ret;
	}

	public function isOne( array $q = [] )
	{
		static $nullret = null;

		if( ! $q ){
			if( null === $nullret ){
				$q = [];
				$q[] = [ 'limit', 2 ];
				$idList = $this->self->findProp( 'id', $q );
				$nullret = ( 1 === count($idList) ) ? current( $idList ) : false;
			}
			$ret = $nullret;
		}
		else {
			$q[] = [ 'limit', 2 ];
			$idList = $this->self->findProp( 'id', $q );
			$ret = ( 1 === count($idList) ) ? current( $idList ) : false;
		}

		return $ret;
	}

	public function beforeCreate( $m )
	{
		return $m;
	}

	public function create( $m, $reason = '' )
	{
		$m = $this->self->beforeCreate( $m );

		$this->_checkArgumentClass( $m, __METHOD__ );

	// reset negative id for new entries
		if( property_exists($m, 'id') && ($m->id < 0) ){
			$m->id = null;
		}

		$error = $this->self->createError( $m );
		if( $error ){
			$errText = [];
			foreach( $error as $k => $v ) $errText[] = $k . ':' . $v;
			$errText = join( ', ', $errText );
			throw new \Exception( $errText );
		}

		$markCreateAt = property_exists( static::$class, 'createAt' ) ? true : false;
		if( $markCreateAt && (! $m->createAt) ){
			$now = $this->t->getNow();
			$m->createAt = $now;
		}

		$a = $this->self->toArray( $m );
		$id = $this->crud->create( $a );
		if( property_exists($m, 'id') ){
			$m->id = $id;
		}

		return $m;
	}

/* warning - no error check */
	public function createByArray( array $a )
	{
		$id = $this->crud->create( $a );
		return $id;
	}

/* warning - no error check */
	public function createManyByArray( array $arrayOfArrays )
	{
		$this->crud->createMany( $arrayOfArrays );
	}

	public function findError( array $mList, array $skipIdList = [] )
	{
		$ret = [];
		return $ret;
	}

	public function createError( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );
		$ret = [];

		reset( static::$required );
		foreach( static::$required as $k ){
			if( ! strlen($m->{$k}) ){
				$ret[ $k ] = '__Required field__';
			}
		}

		reset( static::$unique );
		foreach( static::$unique as $k ){
			if( ! strlen($m->{$k}) ) continue;

			$q = [];
			$q[] = [ $k, '=', $m->{$k} ];
			if( isset($m->id) && $m->id ){
				$q[] = [ 'id', '<>', $m->id ];
			}
			$q[] = [ 'limit', 1 ];

			$already = $this->self->findProp( $k, $q );
			if( $already ){
				$ret[ $k ] = '__This value already exists__';
			}
		}

		return $ret;
	}

	public function qId( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );
		$q = [];
		$q[] = [ 'id', '=', $m->id ];
		return $q;
	}

	public function id( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );
		return isset($m->id) ? $m->id : null;
	}

/*
	public function save( $m2 )
	{
		$this->_checkArgumentClass( $m2, __METHOD__ );

	// exists? update
		$q = $this->self->qId( $m2 );
		$q[] = [ 'limit', 1 ];
		$mList = $this->self->find( $q );

		$m = current( $mList );
		if( $m ){
			$ret = $this->self->update( $m, $m2 );
		}
		else {
	// otherwise create new
			$ret = $this->self->create( $m2 );
		}

		return $ret;
	}
*/

	public function arrayDiff( array $a1, array $a2 )
	{
		$ret = [];

		foreach( $a1 as $k => $v ){
			if( array_key_exists($k, $a2) ){
				if( is_array($v) ){
					$v2 = $a2[$k];
					if( ! is_array($v2) ){
						$v2 = [ $v2 ];
					}

					$test1 = json_encode( $v );
					$test2 = json_encode( $v2 );

					if( $test1 != $test2 ){
						$ret[ $k ] = $v;
					}
					// $diff = $this->arrayDiff( $v, $a2[$k] );
					// if( count($diff)){
						// $ret[ $k ] = $diff;
					// }
				}
				else {
					if( $v != $a2[$k] ){
						$ret[ $k ] = $v;
					}
				}
			}
			else {
				$ret[ $k ] = $v;
			}
		}

		return $ret;
	}

	public function beforeUpdate( $m, $m2 )
	{
		return $m2;
	}

	public function update( $m, $m2, $reason = '' )
	{
		$m2 = $this->self->beforeUpdate( $m, $m2 );

		$error = $this->self->updateError( $m, $m2 );
		if( $error ){
			$errText = [];
			foreach( $error as $k => $v ) $errText[] = $k . ':' . $v;
			$errText = join( ', ', $errText );
			throw new \Exception( $errText );
		}

		$a1 = $this->self->toArray( $m );
		$a2 = $this->self->toArray( $m2 );

		$a = $this->arrayDiff( $a2, $a1 );
		unset( $a['id'] );

		// $a = array_diff_assoc( $a2, $a1 );

		if( ! $a ) return;

		$markUpdateAt = property_exists( static::$class, 'updateAt' ) ? true : false;
		if( $markUpdateAt && (! isset($a['updateAt'])) ){
			$now = $this->t->getNow();
			$a['updateAt'] = $now;
		}

		// $this->crud->update( $a, ['id' => $m->id] );
		$q = $this->self->qId( $m );
		$this->crud->update( $a, $q );

		if( property_exists($m2, 'id') ){
			$m2->id = $m->id;
		}

		return $m2;
	}

	public function updateError( $m, $m2 )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );
		$this->_checkArgumentClass( $m2, __METHOD__ );

		$ret = [];

		reset( static::$required );
		foreach( static::$required as $k ){
			if( ! strlen($m2->{$k}) ){
				$ret[ $k ] = '__Required field__';
			}
		}

		reset( static::$unique );
		foreach( static::$unique as $k ){
			if( $m->{$k} == $m2->{$k} ) continue;

			$q = [];
			$q[] = [ $k, '=', $m2->{$k} ];
			if( isset($m->id) && $m->id ){
				$q[] = [ 'id', '<>', $m->id ];
			}

			$already = $this->self->findProp( $k, $q );
			if( $already ){
				$ret[ $k ] = '__This value already exists__';
			}
		}

		return $ret;
	}

/* warning - no error check */
	public function updateMany( array $array, array $q )
	{
		$markUpdateAt = property_exists( static::$class, 'updateAt' ) ? true : false;
		if( $markUpdateAt && (! isset($array['updateAt']) ) ){
			$now = $this->t->getNow();
			$array[ 'updateAt' ] = $now;
		}

		$this->crud->update( $array, $q );
	}

	public function createMany( array $modelList )
	{
		$markCreateAt = property_exists( static::$class, 'createAt' ) ? true : false;
		if( $markCreateAt ){
			$now = $this->t->getNow();
		}

		$aList = [];
		foreach( $modelList as $m ){
			if( $markCreateAt && (! $m->createAt) ){
				$m->createAt = $now;
			}

		// reset negative id for new entries
			if( property_exists($m, 'id') && ($m->id < 0) ){
				$m->id = null;
			}
			$aList[] = $this->self->toArray( $m );
		}
		$this->crud->createMany( $aList );
		return $modelList;
	}

	public function delete( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );

		$error = $this->self->deleteError( $m );
		if( $error ) throw new \Exception( join(',', $error) );

		// $this->crud->delete( ['id' => $m->id] );
		$q = $this->self->qId( $m );
		$this->crud->delete( $q );

		return $m;
	}

	public function deleteError( $m )
	{
		$this->_checkArgumentClass( $m, __METHOD__ );

		$ret = [];
		return $ret;
	}

	public function deleteMany( array $q )
	{
		$this->crud->delete( $q );
		return true;
	}

	private function _checkArgumentClass( $arg, $methodName )
	{
		$class = static::$class;
		if( ! $arg instanceof $class ){
			throw new \InvalidArgumentException( 'Argument 1 passed to ' . $methodName . ' must be an instance of ' . $class . ', instance of ' . get_class($arg) . ' given' );
		}
	}
}