<?php
namespace Plainware\PlainTracker;

class Acl_Wordpress
{
	public $self = __CLASS__;
	public $helperUserWordpress = HelperUserWordpress::class;

	protected static $_listAdminRole = [ 'administrator', 'developer' ];

	public function isAdmin( $ret, $userId )
	{
		if( false !== $ret ) return $ret;

		static $cache = [];
		if( ! isset($cache[$userId]) ){
			$dictWpUserRole = $this->helperUserWordpress->getWordpressRole( $userId );
			foreach( static::$_listAdminRole as $checkRole ){
				if( isset($dictWpUserRole[$checkRole]) ){
					$ret = true;
					break;
				}
			}
			$cache[ $userId ] = $ret;
		}

		return $cache[ $userId ];
	}
}