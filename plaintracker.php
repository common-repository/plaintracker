<?php
/*
Plugin Name: Plain Tracker
Plugin URI: https://www.plaintracker.net/
Description: A lightweight timesheet plugin. Track time, control attendance and review reports.
Version: 2.2.4
Author: plainware.com
Author URI: https://www.plainware.com/
Text Domain: plaintracker
Domain Path: /languages/
*/

if( function_exists('add_action') ){
	add_action( 'plugins_loaded', array('PlainwarePlainTracker', 'start') );
}

if( ! class_exists('PlainwarePlainTracker') ){
class PlainwarePlainTracker
{
	public static $instance;
	public $slug = 'ptr';
	public $app;

	public $x = null;

	public static function start()
	{
		new static( __FILE__ );
	}

	public function __construct()
	{
		self::$instance = $this;

		add_action( 'admin_menu', [$this, 'adminMenu'] );
		add_action( 'admin_init', [$this, 'adminInit'] );
		add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueue'] );
		add_action( 'wp_ajax_' . $this->slug, [$this, 'adminAjax'] );

		add_shortcode( 'plaintracker', [$this, 'shortcode'] );
		add_action( 'init', [$this, 'frontInit'] );
	}

	public function adminMenu()
	{
		$fileContents = file_get_contents( __FILE__ );
		if( preg_match('/plugin name:[\s\t]+?(.+)/i', $fileContents, $v) ){
			$defaultLabel = $v[1];
		}
		else {
			$defaultLabel = basename( __FILE__ );
		}

		$label = get_site_option( 'plaintracker_menu_title', $defaultLabel );

		if( null === $label ) $label = '';
		$label = trim( $label );
		if( ! strlen($label) ) $label = $defaultLabel;

		$cap = 'manage_options';
		$icon = 'dashicons-clock';
		$pos = 5;

		add_menu_page( 
			$label,
			$label,
			$cap,
			$this->slug,
			[$this, 'echoRender'],
			$icon,
			$pos
		);
	}

	public function dirs()
	{
		$ret = [];

		$DIR = file_exists( __DIR__ . '/dev2.php' ) ? require( __DIR__ . '/dev.php' ) : __DIR__;
		$require = [ $DIR . '/dev.php', $DIR . '/include.php' ];
		foreach( $require as $f ){
			if( file_exists($f) ){
				$ret = array_merge( $ret, require($f) );
			}
		}

	// set full path
		foreach( array_keys($ret) as $ii ){
			$ret[ $ii ] = $DIR . DIRECTORY_SEPARATOR . 'ptr2' . DIRECTORY_SEPARATOR . $ret[ $ii ];
		}

	// assume that extenders will provide full path
		$ret = apply_filters( 'plaintracker/dirs', $ret );

		return $ret;
	}

	public function init()
	{
		if( $this->app ) return;

		$dirs = $this->dirs();
		if( ! class_exists('\Plainware\App') ){
			include_once( $DIR . '/_/App.php' );
		}
		$app = new \Plainware\App;

		$namespace = 'Plainware\\PlainTracker';
// _print_r( $dirs );
		foreach( $dirs as $dir ){
			$app->registerDir( $dir, $namespace );
		}

		$app->version = \Plainware\File::versionStringFromFile( __FILE__ );
		$app->name = \Plainware\File::appNameFromFile( __FILE__ );

		$app->inject( \Plainware\DbWordpress::class . '::$conf', ['prefix' => 'plaintracker2_'] );
		$app->inject( \Plainware\Handler::class . '::$namespace', $namespace );

		$app->addFilter( \Plainware\Uri::class . '::toHref', [$this, 'uriToHref'], 0 );
		$app->addFilter( \Plainware\HtmlAsset::class . '::uri', [$this, 'assetUri'], 5 );

		\Plainware\Uri::$slugParam = $this->slug;
		\Plainware\Uri::$paramPrefix = $this->slug . '_';

		$this->app = $app;
	}

	public function assetUri( $file )
	{
		$ret = plugins_url( 'ptr2/' . $file, __FILE__ );
		return $ret;
	}

	public function uriToHref( array $queryParams )
	{
		if( is_admin() ){
			$ret = 'admin.php?page=' . $this->slug;
			if( $queryParams ){
				$ret .= '&' . http_build_query( $queryParams );
			}
		}
		else {
			global $post;
			if( $post ){
				$ret = get_permalink( $post->ID );
				foreach( $queryParams as $k => $v ){
					$ret = add_query_arg( $k, $v, $ret );
				}
			}
			else {
				$ret = '';
				if( $queryParams ){
					$ret .= '?' . http_build_query( $queryParams );
				}
			}
		}

		return $ret;
	}

	public function x()
	{
		static $x = null;

		if( null === $x ){
			$this->init();

			$uri = $this->app->make( \Plainware\Uri::class )->fromQueryParams( $_GET );
			$x = [ '$uri' => $uri ];

			$handler = $this->app->make( \Plainware\Handler::class );
			$x = $handler->handle( $x );
		}

		return $x;
	}

	public function shortcode( $attr = [] )
	{
		$x = $this->x();
		return $this->render( $x );
	}

	public function echoRender()
	{
		$x = $this->x();
		echo $this->render( $x );
	}

	public function render( array $x )
	{
		$handler = $this->app->make( \Plainware\Handler::class );
		$ret = $handler->render( $x );
		$ret = $this->app->make( \Plainware\Layout::class )->render( $x, $ret );
		$ret = $handler->afterRender( $ret, $x );

		return $ret;
	}

	public function adminAjax()
	{
		$x = $this->x();
		echo $this->render( $x );
		wp_die();
	}

	public function adminEnqueue()
	{
		if( ! isset($_REQUEST['page']) ) return;

	// our page?
		$page = sanitize_text_field( $_REQUEST['page'] );
		if( $page != $this->slug ) return;

	// rich text editor
		wp_enqueue_editor();
		wp_enqueue_media();
	}

	public function adminInit()
	{
		if( ! isset($_REQUEST['page']) ) return;

	// our page?
		$page = sanitize_text_field( $_REQUEST['page'] );
		if( $page != $this->slug ) return;

	// init here
		$x = $this->x();

	// has layout param? used for ajax partials, print views, downloads
		$layoutParamName = $this->slug . '_' . 'layout-';
		if( isset($_REQUEST[$layoutParamName]) ){
			echo $this->render( $x );
			exit;
		}
	}

	public function frontInit()
	{
		if( is_admin() ) return;

	// if explicit layout for ajax partials, print views, downloads then render right away
		$layoutParamName = $this->slug . '_' . 'layout-';
		if( ! isset($_REQUEST[$layoutParamName]) ){
			return;
		}

		$x = $this->x();
		echo $this->render( $x );
		exit;
	}
}
}