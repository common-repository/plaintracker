<?php
namespace Plainware;

class PermHandler
{
	public $self = __CLASS__;

	public $handler = Handler::class;
	public $uri = Uri::class;
	public $app = App::class;

	protected $slug2page = [];

	public function can( $slug, array $x )
	{
		$ret = false;

		$currentUri = $x['$uri'];
		$uri = $this->uri->fromString( $slug, $currentUri );
		$slug = $uri->slug;
		$x = array_merge( $x, $uri->params );

		$page = $this->handler->findPage( $slug, $x );
		if( ! $page ){
			return $ret;
		}

		if( $this->app->methodExists($page, 'can') ){
			$ret = true;

			$pageRet = $page->can( $x );
			if( null !== $pageRet ){
				$ret = $pageRet;
			}
		}
		else {
			echo "SLUG STRING: $slug<br>";
			$msg = $this->app->getClass( $page ) . ' must implement can()';
			echo $msg;
		}

		return $ret;
	}

	public function canPost( $slug, array $x )
	{
		$ret = false;

		$currentUri = $x['$uri'];
		$uri = $this->uri->fromString( $slug, $currentUri );
		$slug = $uri->slug;
		$x = array_merge( $x, $uri->params );

		$page = $this->handler->findPage( $slug, $x );
		if( ! $page ){
			return $ret;
		}

		if( ! $this->app->methodExists($page, 'canPost') ){
			return $this->self->can( $slug, $x );
		}

		$ret = true;
		$can = $page->canPost( $x );
		if( false === $can ){
			$ret = false;
		}

		return $ret;
	}
}