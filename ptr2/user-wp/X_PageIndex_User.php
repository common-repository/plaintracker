<?php
namespace Plainware\PlainTracker;

class X_PageIndex_User
{
	public $auth = Auth::class;
	public $modelUser = ModelUser::class;

	public function nav( array $ret, array $x )
	{
		$uri = $x[ '$uri' ] ?? null;
		if( ! $uri ) return $ret;

		$currentUserId = $this->auth->getCurrentUserId( $x );
		$currentUser = $currentUserId ? $this->modelUser->findById( $currentUserId ) : null;

		if( $currentUser ){
		}
		else {
			global $wp;
			$returnTo = home_url( $wp->request );
			$to = wp_login_url( $returnTo );
			$ret[ '81-login' ] = [ $to, '__Log in__' ];
		}

		return $ret;
	}
}