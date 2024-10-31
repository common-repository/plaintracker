<?php
namespace Plainware\PlainTracker;

class Acl
{
	public $self = __CLASS__;

	public function isAdmin( $userId )
	{
		$ret = false;
		return $ret;
	}
}