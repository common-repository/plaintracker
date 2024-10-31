<?php
namespace Plainware;

class Handler
{
	public $namespace;

	public $self = __CLASS__;

	public $uri = Uri::class;
	public $htmlForm = HtmlForm::class;
	public $app = App::class;
	public $translate = Translate::class;

	public function findPage( $slug, array $x )
	{
		static $slugToClass = [];

		if( null === $slug ){
			$slug = '';
		}

		$namespaceList = [];
		if( $this->namespace ){
			$namespaceList[] = $this->namespace;
		}
		$namespaceList[] = __NAMESPACE__;

		if( ! strlen($slug) ){
			$slug = 'index';
		}

		if( ! isset($slugToClass[$slug]) ){
			$slugToClass[ $slug ] = '';

			$parts = explode( '-', $slug );
			$countParts = count( $parts );
			for( $ii = 0; $ii < $countParts; $ii++ ){
				$parts[ $ii ] = ucfirst( $parts[ $ii ] );
			}

			$className = join( '', $parts );

			foreach( $namespaceList as $ns ){
				$thisClassName = $ns . '\Page' . $className;
				if( class_exists($thisClassName) ){
					$slugToClass[ $slug ] = $thisClassName;
					break;
				}
				else {
					// echo "CLASS NO EXISTS! '$thisClassName'<br>";
				}
			}
		}

// _print_r( $slugToClass );

		$className = $slugToClass[ $slug ];

		// if( ! class_exists($className) ){
		if( ! $className ){
// echo "CLASS NO EXISTS! '$slug'<br>";
			$ret = null;
			return $ret;
		}

// echo "CLASS = '$className'<br>";
// exit;

		$ret = $this->app->make( $className );
		return $ret;
	}

	// init context
	public function x( array $x )
	{
		$defaultX = [];

		$defaultX[ '$page' ] = null;

	// form and other errors
		$defaultX['error'] = [];
		$defaultX['help'] = [];
	// post values
		$defaultX['post'] = [];

		$defaultX['msg'] = [];
		$defaultX['redirect'] = null;

// _print_r( array_keys($_SERVER) );

		if( $this->isAjax() ){
			$layoutParam = $this->uri->getLayoutParam();
			$defaultX[ $layoutParam ] = 'ajax';
		}

		$x = $x + $defaultX;

		return $x;
	}

	public function outAsset( array $files )
	{
		$ret = '';

		foreach( $files as $f ){
			$fullFile = __DIR__ . '/../' . $f;
			// $fullFile = __DIR__ . '/' . $f;
			$ret .= file_get_contents( $fullFile );
		}

		header( 'content-type:text/css' );
		// header( "Expires: ".gmdate("D, d M Y H:i:s", (time()+900)) . " GMT" ); 
		header( "Expires: ".gmdate("D, d M Y H:i:s", (time()-900)) . " GMT" ); 

		echo $ret;
		exit;
	}

	public function init()
	{
		static $done = false;
		if( false !== $done ) return;

		$done = true;
		return $this;
	}

	public function handle( array $x = [] )
	{
		$this->self->init();

		$currentUri = $x['$uri'];

		$paramsFromUri = $currentUri->params;

	// stack params
		$parentParamsFromUri = [];
	// for nearest parent only so far
		if( isset($currentUri->parent) ){
			$parentParamsFromUri = $currentUri->parent->params;
		}

	// won't allow any params starting with $, or error
		foreach( array_keys($paramsFromUri) as $k ){
			if( '$' == substr($k, 0, 1) ){
				unset( $paramsFromUri[$k] );
			}
			if( ('error' == $k) ){
				unset( $paramsFromUri[$k] );
			}
		}
		foreach( array_keys($parentParamsFromUri) as $k ){
			if( '$' == substr($k, 0, 1) ){
				unset( $parentParamsFromUri[$k] );
			}
			if( ('error' == $k) ){
				unset( $parentParamsFromUri[$k] );
			}
		}
		if( $parentParamsFromUri ){
			$x['..'] = $parentParamsFromUri;
		}

		$x = array_merge( $x, $paramsFromUri );

		$x['slug'] = $currentUri->slug;

	// if uri is stacked also include stacked params ['..'] => [], ['../..'] => [] etc
		// for( $stackIndex = 0; $stackIndex < count($currentUri->stack); $stackIndex++ ){
			// $stackKey = join( '/', array_fill(0, $stackIndex + 1, '..') );
			// $x[ $stackKey ] = $currentUri->stack[ $stackIndex ][ 1 ];
		// }

		$requestMethod = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field($_SERVER['REQUEST_METHOD']) : 'get';
		$requestMethod = strtoupper( $requestMethod );
		$x[ '$requestMethod' ] = $requestMethod;

	// so slug can be either redefined in filters
		$x = $this->self->x( $x );

	// page handler
		$page = $this->self->findPage( $x['slug'], $x );
		if( ! $page ){
			$page = $this->self->findPage( '404', $x );
		}
		if( ! $page ){
			exit( "no handler for '" . $x['slug'] . "'" );
		}

		$originalSlug = $x['slug'];

		try {
		// do GET first even before POST
			if( $this->app->methodExists($page, 'get') ){
				$x2 = $page->get( $x );
				if( is_array($x2) ) $x = $x2;
			}
 
		// redirect?
			if( isset($x['redirect']) ){
				return $this->self->redirect( $x['redirect'], $x );
			}

		// slug redefined?
			// if( ! isset($x['slug']) ) $x['slug'] = '';
			if( $x['slug'] != $originalSlug ){
				$uri = $this->uri->fromString( $x['slug'], $currentUri );

				$x = $uri->params + $x;
				$x['slug'] = $uri->slug;
				$x['$uri'] = $uri;

				$page = $this->self->findPage( $x['slug'], $x );

				if( $this->app->methodExists($page, 'get') ){
					$x2 = $page->get( $x );
					if( is_array($x2) ) $x = $x2;
				}
			}

		// post?
			if( 'POST' === $requestMethod ){
				unset( $x['msg-'] );
				$x['post'] = $this->self->grabPost( $_POST );

				if( $this->app->methodExists($page, 'post') ){
					$x2 = $page->post( $x );
					if( is_array($x2) ) $x = $x2;
				}

				foreach( $x['post'] as $k => $v ){
					$this->htmlForm->setValue( $k, $v );
				}
				foreach( $x['error'] as $k => $v ){
					$this->htmlForm->setError( $k, $v );
				}
			}
		}
		catch( \Exception $e ){
			$x['$ret'] = $e->getMessage();
		}
		catch( \Error $e ){
			$x['$ret'] = $e->getMessage() . '<br>' . $e->getFile() . ':' . $e->getLine();
		}

	// redirect?
		if( isset($x['redirect']) ){
		// default message?
			$to = $x['redirect'];
			if( ! is_array($to) ){
				$to = [ $to, [] ];
			}

			if( ('POST' === $requestMethod) && (! isset($to[1]['msg-'])) ){
				$to[1]['msg-'] = 1;
			}
			$x['redirect'] = $to;

			return $this->self->redirect( $x['redirect'], $x );
		}

		$x[ '$page' ] = $page;

		return $x;
	}

	public function render( array $x )
	{
		$slug = $x['slug'];
		$page = $x['$page'];

	// content
		$ret = '';

		try {
			if( isset($x['$ret']) ){
				$ret = $x['$ret'];
			}
			else {
				if( $this->app->methodExists($page, 'render') ){
					$ret = $page->render( $x );
				}
			}
			$ret = $this->self->prepareRender( $ret, $x );
		}
		catch( \Exception $e ){
			$ret = $e->getMessage();
		}
		catch( \Error $e ){
			$ret = $e->getMessage() . '<br>' . $e->getFile() . ':' . $e->getLine();
		}

		return $ret;
	}

	public function redirect( $to, array $x )
	{
		$currentUri = $x['$uri'];

		$globalSlugParams = [];
		foreach( $x as $k => $v ){
			if( '*' === substr($k, 0, 1) ){
				$globalSlugParams[ $k ] = $x[ $k ];
			}
		}

		$param = [];
		if( is_array($to) ){
			list( $to, $param ) = $to;
		}

	// transform controller's message 'msg-' to 'slug-msg-'
		if( isset($param['msg-']) ){
			$k = $x['slug'] . '-msg-';
			$param[ $k ] = $param['msg-'];
			unset( $param['msg-'] );
		}

		if( null === $to ) $to = $currentUri->slug;

		$param += $globalSlugParams;
// echo "2REDIRECT TO = '$to' VS '$currentUri->slug'<br>";

// _print_r( $param );
// exit;

	// keep layout param for ajax post
		$layoutParam = $this->uri->getLayoutParam();
		if( isset($x[$layoutParam]) ){
			$param[ $layoutParam ] = $x[ $layoutParam ];
		}


// echo "TO  = '$to'<br>";
		$uri = $this->uri->make( $to, $param, $currentUri );
// _print_r( $param );
// _print_r( $currentUri );
// _print_r( $uri );
		$p = $this->uri->toQueryParams( $uri );
// _print_r( $p );
		$to = $this->uri->toHref( $p );
// echo "TO = '$to'<br>";
// exit;

		$this->self->doRedirect( $to, $x );
	}

	public function isAjax()
	{
		$ret = false;
		if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ('XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH']) ){
			$ret = true;
		}
		return $ret;
	}

	public function doRedirect( $to, array $x )
	{
		if( defined('WPINC') ){
			if( ! headers_sent() ){
				wp_redirect( $to );
			}
			else {
				echo '<META http-equiv="refresh" content="0;URL=' . esc_attr($to) . '">';
				exit;
			}
		}
		else {
			$header = 'Location:' . $to;
			if( 0 && $this->isAjax() ){
				echo $header;
			}
			else  {
				header( $header );
			}
		}
		exit;
	}

	public function prepareRender( $ret, array $x )
	{
	// if is array remove nulls or empty, then sort sections if needed
		if( ! is_array($ret) ){
			return $ret;
		}

		$needSort = false;
	// perhaps sort
		foreach( array_keys($ret) as $k ){
			$pos = strpos( $k, '-' );
			if( false !== $pos ){
				$needSort = true;
				break;
			}
		}

		if( $needSort ){
			ksort( $ret );

		// remove sort parts
			reset( $ret );
			foreach( array_keys($ret) as $k ){
				$pos = strpos( $k, '-' );
				if( false !== $pos ){
					$k2 = substr( $k, $pos + 1 );
					if( isset($ret[$k2]) ){
						$ret[ $k2 ] = array_merge( $ret[$k2], $ret[$k] );
					}
					else {
						$ret[$k2] = $ret[ $k ];
					}
					unset( $ret[$k] );
				}
			}
		}

	// if layout specified and we have it in ret, remove all others
		$layoutParam = $this->uri->getLayoutParam();
		if( isset($x[$layoutParam]) ){
			$layout = $x[ $layoutParam ];
			if( array_key_exists($layout, $ret) ){
				$listKey = array_keys( $ret );
				foreach( $listKey as $k ){
					if( $k != $layout ){
						unset( $ret[$k] );
					}
				}
			}
		}

	// have callables?
		reset( $ret );
		foreach( array_keys($ret) as $k ){
			if( is_string($ret[$k]) ) continue;
			$ret[$k] = $this->processPartOfRet( $ret[$k], $x );
		}

	// remove nulls or empty
		reset( $ret );
		$listKey = array_keys( $ret );
		foreach( $listKey as $k ){
			if( (null === $ret[$k]) OR ( ! strlen($ret[$k]) )){
				unset( $ret[$k] );
			}
		}

		return $ret;
	}

	public function processPartOfRet( $ret, array $x )
	{
		while( is_array($ret) ){
		// callable with arguments
			if( isset($ret[0]) && is_callable($ret[0]) ){
				$func = array_shift( $ret );
				$args = $ret;
				// array_unshift( $args, $x );
				$ret = call_user_func_array( $func, $args );
			}
		// just callable - pass in $x
			elseif( is_callable($ret) ){
				$ret = call_user_func( $ret, $x );
			}
		// glue output
			else {
			// a bit of dirty - if only 10-start and 99-end keys are present check if it's emtpy
				$wrapLater = false;
				ksort( $ret );
				foreach( array_keys($ret) as $k ){
					$ret[$k] = $this->processPartOfRet( $ret[$k], $x );

					if( '10-start' == $k ){
						$ret1 = $ret[$k];
						unset( $ret[$k] );
						$wrapLater = true;
					}

					if( '99-end' == $k ){
						$ret2 = $ret[$k];
						unset( $ret[$k] );
						$wrapLater = true;
					}
				}

				$ret = join( '', $ret );

				if( $wrapLater && strlen($ret) ){
					$ret = $ret1 . $ret . $ret2;
				}
			}
		}

		return $ret;
	}

	public function afterRender( $ret, array $x )
	{
		if( null === $ret ){
			$ret = '';
			return $ret;
		}
// return $ret;
	// replace tabs as we have them a lot
		$ret = str_replace( "\t", '', $ret );

		$ret = $this->self->setFormAction( $ret, $x );
		$ret = $this->self->parseLinks( $ret, $x );
		$ret = $this->self->parseHelpers( $ret, $x );
		$ret = $this->self->translate( $ret, $x );

		return $ret;
	}

	public function translate( $ret, array $x )
	{
		return $this->translate->translate( $ret );
	}

	public function setFormAction( $ret, array $x )
	{
		$currentUri = $x[ '$uri' ];
		$to = $this->uri->toString( $currentUri );

		// $uri = $this->uri->make( '.', [], $currentUri );
		// $to = $this->uri->toString( $uri );

		$ret = str_replace( '<form method="post"', '<form method="post" action="URI:' . $to . '"', $ret );
		return $ret;
	}

	public function parseHelpers( $ret, array $x )
	{
		// $ret = str_replace( 'pw-confirm="1"', 'onclick="if(!confirm(\'__Are you sure__?\')){event.stopPropagation();return false;};"', $ret );
		preg_match_all( '/pw-confirm="(.+)\"/smU', $ret, $ma );
		// _print_r( $ma );

		if( count($ma[0]) ){
			$replace = [];
			for( $ii = 0; $ii < count($ma[0]); $ii++ ){
				$msg = ( '1' == $ma[1][$ii] ) ? '__Are you sure?__' : $ma[1][$ii];
				$to = 'onclick="if(!confirm(\'' . addslashes($msg) . '\')){event.stopPropagation();return false;};"';
				$from = $ma[0][$ii];
				$replace[ $from ] = $to;
			}
// _print_r( $replace );
// exit;
			$ret = strtr( $ret, $replace );
		}

		return $ret;
	}

	public function parseLinks( $ret, array $x )
	{
		$currentUri = $x[ '$uri' ];

	// replace URIs - replace "URI:smth" to real urls
		$strings = [];

		$start = 'URI:';
		$startLen = strlen( $start );
		$end = '"';
		$endLen = strlen( $end );

// echo 'LEN = ' . strlen( $ret ) . '<br>';

	// find strings to change to URIs
		$replace = [];
		$pos1 = strpos( $ret, $start );
		while( false !== $pos1 ){
			$pos2 = strpos( $ret, $end, $pos1 + $startLen );
			if( false === $pos2 ) break;

			$part = substr( $ret, $pos1 - 1, $pos2 - $pos1 + 2 );

			if( ! isset($replace[$part]) ){
				$toString = substr( $part, 1 + $startLen, -1 );
				$replace[ $part ] = $toString;
			}

			$pos2 = $pos2 + $endLen;
			$pos1 = strpos( $ret, $start, $pos2 + 1 );
		}

	// build real URIs
		foreach( array_keys($replace) as $k ){
			if( $this->uri->isFull($replace[$k]) ){
				$toString = $replace[$k];
			}
			else {
				$uri = $this->uri->fromString( $replace[$k], $currentUri );
				$p = $this->uri->toQueryParams( $uri );
				$toString = $this->uri->toHref( $p );
			}

			$replace[$k] = '"' . $toString . '"';
		}

	// replace in output
		$ret = strtr( $ret, $replace );

		return $ret;
	}

	public function grabPost( array $post )
	{
		$ret = [];

		foreach( array_keys($post) as $k ){
			if( ! is_array($post[$k]) ){
				$nl = '--OMGKEEPNEWLINE--';

				$v = str_replace( "\n", $nl, $post[$k] );

				if( function_exists('wp_kses_post') ){
					$v = wp_kses_post( $v );
				}
				else {
					// $v = htmlentities( $v );
					$v = sanitize_text_field( $v );
				}

				// $v = sanitize_text_field( str_replace("\n", $nl, $post[$k]) );
				$v = str_replace( $nl, "\n", $v );

				if( is_numeric($v) ){
					$v = floatval( $v );
				}
				$ret[$k] = $v;
			}
			else {
				$ret[$k] = $this->self->grabPost( $post[$k] );
			}
		}

		return $ret;
	}
}