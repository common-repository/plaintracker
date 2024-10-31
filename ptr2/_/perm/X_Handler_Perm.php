<?php
namespace Plainware;

class X_Handler_Perm
{
	public $permHandler = PermHandler::class;
	public $uri = Uri::class;

	public function x( array $x )
	{
		$slug = $x['slug'];
		if( 'POST' === $x['$requestMethod'] ){
			$can = $this->permHandler->canPost( $slug, $x );
		}
		else {
			$can = $this->permHandler->can( $slug, $x );
		}

		if( true === $can ){
			return $x;
		}

		if( null === $can ){
			return $x;
		}

		$canLogin = $this->permHandler->can( 'login', $x );
		$x['slug'] = $canLogin ? 'login' : 'notallowed';

		return $x;
	}
}