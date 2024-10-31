<?php
namespace Plainware\PlainTracker;

class HelperUserWordpress
{
	public $self = __CLASS__;

	public function getAllWordpressRole()
	{
		global $wp_roles;

		$ret = [];
		foreach( $wp_roles->roles as $id => $e ){
			$ret[ $id ] = $e['name'];
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

	public function countUserByRole()
	{
		$ret = count_users();
		return $ret;
	}
}