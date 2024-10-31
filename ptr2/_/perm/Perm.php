<?php
namespace Plainware;

class Perm
{
	public $self = __CLASS__;
	public $q = Q::class;
	public $crud = CrudPerm::class;

	protected $data = null;
	protected $catalog = null;

	public function catalog()
	{
		$ret = [];
		return $ret;
	}

// default rules
	public function rule()
	{
		// $ret[] = [ 'permtaskId' => 'user-create', 'userId' => 123, 'isOn' => true ];
		$ret = [];
		return $ret;
	}

	public function depend()
	{
		$ret = [];

		// $ret[] = [ 'ifOn' => 'user-create', 'thenOn' => 'user-read' ];
		// $ret[] = [ 'ifOff' => 'user-read', 'thenOff' => 'user-create' ];

		return $ret;
	}

	public function canUser( $userId, $permId )
	{
		$ret = false;

	// load data
		if( null === $this->data ){
			$this->data = $this->self->load();
		}
		if( null === $this->catalog ){
			$this->catalog = $this->self->catalog();
		}

		if( '*' != $permId ){
			if( ! isset($this->catalog[$permId]) ){
				echo 'perm ' . esc_html($permId) . ' is not registered<br>';
				return $ret;
			}

		// all?
			$k = $userId . ':' . '*';
			if( isset($this->data[$k]) && $this->data[$k] ){
				$ret = true;
				return $ret;
			}
		}

		$k = $userId . ':' . $permId;
		$ret = array_key_exists( $k, $this->data ) ? $this->data[ $k ] : false;

		return $ret;
	}

	public function allowUserAll( $userId )
	{
		return $this->self->setUserPerm( $userId, '*', true );
	}

	public function allowUser( $userId, $permId )
	{
		return $this->self->setUserPerm( $userId, $permId, true );
	}

	public function disallowUser( $userId, $permId )
	{
		return $this->self->setUserPerm( $userId, $permId, false );
	}

	public function setUserPerm( $userId, $permId, $can )
	{
		$nowCan = $this->self->canUser( $userId, $permId );

	// need to create entry
		if( $nowCan != $can ){
			$q = [];
			$q[] = [ 'permId', '=', $permId ];
			$q[] = [ 'userId', '=', $userId ];
			$already = $this->crud->read( $q );

			$a = [];
			$a[ 'isOn' ] = $can ? 1 : 0;

			if( $already ){
				$this->crud->update( $a, $q );
			}
			else {
				$a[ 'permId' ] = $permId;
				$a[ 'userId' ] = $userId;
				$this->crud->create( $a );
			}
		}

	// dependent tasks
		$this->self->notifyDependent( $userId, $permId, $can );

		return true;
	}

	public function resetUserPerm( $userId, $permId )
	{
		$q = [];
		$q[] = [ 'userId', '=', $userId ];
		if( null !== $permId ){
			$q[] = [ 'permId', '=', $permId ];
		}
		$this->crud->delete( $q );
		return true;
	}

	public function notifyDependent( $userId, $permId, $can )
	{
		$dependList = $this->self->depend();

		$dependSetOn = $dependSetOff = [];
		foreach( $dependList as $depend ){
			$ok = false;

			if( $can && isset($depend['ifOn']) && ($permId == $depend['ifOn']) ){
				$ok = true;
			}

			if( (! $can) && isset($depend['ifOff']) && ($permId == $depend['ifOff']) ){
				$ok = true;
			}

			if( ! $ok ) continue;

			if( isset($depend['thenOn']) ) $dependSetOn[] = $depend['thenOn'];
			if( isset($depend['thenOff']) ) $dependSetOff[] = $depend['thenOff'];
		}

		$dependSetOn = array_unique( $dependSetOn );
		$dependSetOff = array_unique( $dependSetOff );

		foreach( $dependSetOn as $linkedPermId ){
			$this->self->setUserPerm( $userId, $linkedPermId, true );
		}
		foreach( $dependSetOff as $linkedPermId ){
			$this->self->setUserPerm( $userId, $linkedPermId, false );
		}
	}

/* return ids of users who can do permId. If permId === null then return all user ids */
	public function whoCan( $permId )
	{
		$ret = [];

	// load data
		if( null === $this->data ){
			$this->data = $this->self->load();
		}

		foreach( array_keys($this->data) as $k ){
			list( $thisUserId, $thisPermId ) = explode( ':', $k );
			if( (null === $permId) OR ($thisPermId == $permId) ){
				$thisUserId = (int) $thisUserId;
				$ret[ $thisUserId ] = $thisUserId;
			}
		}

		return $ret;
	}

/* return permids */
	public function whatUserCan( $userId )
	{
		$ret = [];

	// load data
		if( null === $this->data ){
			$this->data = $this->self->load();
		}

		foreach( array_keys($this->data) as $k ){
			list( $thisUserId, $thisPermId ) = explode( ':', $k );
			if( $thisUserId == $userId ){
				$ret[ $thisPermId ] = $thisPermId;
			}
		}

		return $ret;
	}

	public function load()
	{
		$ret = [];

	// default rules
		$rowList = $this->self->rule();

	// explicit rules
		$q = [];
		$rowList = array_merge( $rowList, $this->crud->read($q) );

// _print_r( $rowList );

		foreach( $rowList as $row ){
			$k = $row['userId'] . ':' . $row['permId'];
			$isOn = $row['isOn'] ? true : false;

			if( $isOn ){
				$ret[$k] = true;
			}
			else {
				unset( $ret[$k] );
			}
		}

// _print_r( array_keys($ret) );
// exit;

		return $ret;
	}
}