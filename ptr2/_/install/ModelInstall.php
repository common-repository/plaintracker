<?php
namespace Plainware;

class ModelInstall
{
	public $self = __CLASS__;

	public $app = App::class;
	public $crud = CrudInstall::class;

	protected $versions = null;

	public function isInstalled()
	{
		$ret = $this->self->get( 'install' );
		return $ret;
	}

	public function doUp()
	{
/* [ '11-module:1' => [up1, down2], '11-module:2' => [up1, down2] ] */
		$moduleList = $this->crud->migrate();

		// add sort parts if not
		foreach( array_keys($moduleList) as $k ){
			$pos = strpos( $k, '-' );
			if( false === $pos ){
				$k2 = '50-' . $k;
				$moduleList[ $k2 ] = $moduleList[ $k ];
				unset( $moduleList[$k] );
			}
		}
		ksort( $moduleList );

		foreach( $moduleList as $k => $module ){
		// contains sort order?
			$pos = strpos( $k, '-' );
			$k = substr( $k, $pos + 1 );
			list( $moduleName, $moduleVersion ) = explode( ':', $k );

			$funcUp = isset( $module[0] ) ? $module[0] : null;
			$funcDown = isset( $module[1] ) ? $module[1] : null;

			$installedVersion = $this->self->get( $moduleName );
			if( ! $installedVersion ) $installedVersion = 0;

// echo "UP?: $moduleName: $installedVersion VS $moduleVersion<br>";
			if( $installedVersion >= $moduleVersion ) continue;
// echo "UP: $moduleName: $installedVersion VS $moduleVersion<br>";

			if( $funcUp ){
				if( is_string($funcUp) ){
					$this->app->callFunc( $funcUp );
				}
				elseif( is_array($funcUp) && is_string($funcUp[0]) ){
					$this->app->callFunc( $funcUp[0] . '::' . $funcUp[1] );
				}
				else {
					call_user_func( $funcUp );
				}
			}

			$this->self->set( $moduleName, $moduleVersion );
		}
	}

	public function doDown()
	{
		$moduleList = $this->crud->migrate();

		// add sort parts if not
		foreach( array_keys($moduleList) as $k ){
			$pos = strpos( $k, '-' );
			if( false === $pos ){
				$k2 = '50-' . $k;
				$moduleList[ $k2 ] = $moduleList[ $k ];
				unset( $moduleList[$k] );
			}
		}
		krsort( $moduleList );

		foreach( $moduleList as $k => $module ){
		// contains sort order?
			$pos = strpos( $k, '-' );
			$k = substr( $k, $pos + 1 );
			list( $moduleName, $moduleVersion ) = explode( ':', $k );

			$funcUp = isset( $module[0] ) ? $module[0] : null;
			$funcDown = isset( $module[1] ) ? $module[1] : null;

		// do only 1, just drop everything
			if( 1 != $moduleVersion ) continue;

			$installedVersion = $this->self->get( $moduleName );
			if( ! $installedVersion ) $installedVersion = 0;
			if( ! $installedVersion ) continue;

// echo "DOWN: $moduleName: $installedVersion VS $moduleVersion<br>";
			// if( $installedVersion >= $moduleVersion ) continue;

			if( $funcDown ){
				if( is_string($funcDown) ){
					$this->app->callFunc( $funcDown );
				}
				elseif( is_array($funcDown) && is_string($funcDown[0]) ){
					$this->app->callFunc( $funcDown[0] . '::' . $funcDown[1] );
				}
				else {
					call_user_func( $funcDown );
				}
			}

			// $this->self->set( $moduleName, $moduleVersion );
		}
		$this->versions = [];
	}

	public function get( $id )
	{
	// load all
		if( null === $this->versions ){
			$this->versions = $this->self->load();
		}

		$ret = 0;
		if( array_key_exists($id, $this->versions) ){
			$ret = $this->versions[ $id ];
		}

		return $ret;
	}

	public function set( $id, $version )
	{
	// load all
		if( null === $this->versions ){
			$this->versions = $this->self->load();
		}

		if( array_key_exists($id, $this->versions) ){
			$q = [];
			$q[] = [ 'id', '=', $id ];
			$m = [ 'version' => $version ];
			$this->crud->update( $m, $q );
		}
		else {
			$m = [ 'id' => $id, 'version' => $version ];
			$this->crud->create( $m );
		}

		$this->versions[ $id ] = $version;
		return $version;
	}

	public function load()
	{
		$ret = [];

		$all = $this->crud->read();
		foreach( $all as $m ){
			$ret[ $m['id'] ] = $m['version'];
		}

		return $ret;
	}
}