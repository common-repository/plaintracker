<?php
namespace Plainware\PlainTracker;

class X_Layout_App
{
	public function render( $ret )
	{
		$ret = str_replace( '<i>!</i>', '<i><mark style="padding: 0 .25rem; background-color: #c30; color: #fff; border-radius: .25rem;">!</mark></i>', $ret );
		return $ret;
	}
}