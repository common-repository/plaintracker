<?php
namespace Plainware;

class HelperWordpressUser
{
	public $self = __CLASS__;
	public $crud = CrudWordpressUser::class;

	public function getAllWordpressRole()
	{
		global $wp_roles;

		$ret = [];
		foreach( $wp_roles->roles as $id => $e ){
			$ret[ $id ] = $e['name'];
		}

		return $ret;
	}

	public function countUserOfAllRole()
	{
		$count = count_users();
		$ret = $count['avail_roles'] ?? [];
		return $ret;
	}

	public function userHasRole( $userId, $roleId )
	{
		$ret = false;

		$listRoleId = is_array( $roleId ) ? $roleId : [ $roleId ];

		$wpUser = get_user_by( 'id', $userId );
		if( $wpUser && $wpUser->roles ){
			foreach( $wpUser->roles as $roleId ){
				if( ! in_array($roleId, $listRoleId) ) continue;
				$ret = true;
				break;
			}
		}

		return $ret;
	}

	public function getWordpressRole( $userId )
	{
		static $cache = [];

		if( ! isset($cache[$userId]) ){
			$ret = [];

			$wpUser = get_user_by( 'id', $userId );
			if( $wpUser && $wpUser->roles ){
				foreach( $wpUser->roles as $roleId ){
					$ret[ $roleId ] = $roleId;
				}
			}

			$repoWordpressRole = $this->self->getAllWordpressRole();
			$ret = array_intersect_key( $repoWordpressRole, $ret );

			$cache[ $userId ] = $ret;
		}

		return $cache[ $userId ];
	}

	public function count( array $q = [] )
	{
		return $this->crud->count( $q );
	}

	public function find( array $q = [] )
	{
		return $this->crud->read( $q );
	}
}