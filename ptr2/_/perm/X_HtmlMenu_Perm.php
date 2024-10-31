<?php
namespace Plainware;

class X_HtmlMenu_Perm
{
	public $permHandler = PermHandler::class;
	public $uri = Uri::class;

	public function finalizeOne( $ret, array $item, array $x )
	{
		if( ! is_array($ret) ){
			return $ret;
		}

		list( $to, $title ) = $ret;
		if( $this->uri->isFull($to) ){
			return $ret;
		}

		$can = $this->permHandler->can( $to, $x );

		if( ! $can ){
			$ret = false;
		}

		return $ret;
	}
}