<?php
namespace Plainware;

class DbCache
{
	public $ret = [];

	public function beforeQuery( $sql )
	{
		if( isset($this->ret[$sql]) ){
			// echo "<br>HAVE ON CACHE:$sql";
			return [ false, $this->ret[$sql] ];
		}
	}

	public function afterQuery( $ret, $sql )
	{
		if( 'SELECT' == substr($sql, 0, strlen('SELECT')) ){
			$this->ret[ $sql ] = $ret;
		}
		return $ret;
	}
}
