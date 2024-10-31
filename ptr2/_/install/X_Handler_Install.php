<?php
namespace Plainware;

class X_Handler_Install
{
	public $modelInstall = ModelInstall::class;

	public function x( array $x )
	{
	// check install
		$installed = $this->modelInstall->isInstalled();
		if( ! $installed ){
			$x['slug'] = 'install';
			return $x;
		}

	// migration up
		if( 'install' !== $x['slug'] ){
			$this->modelInstall->doUp();
		}

		return $x;
	}
}