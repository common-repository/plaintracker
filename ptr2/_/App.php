<?php
namespace Plainware
{
class AppProxyObject
{
	public $className;
	public $invokerClassName;
	public $callerFunc;

	public function __construct( $className, $callerFunc, $invokerClassName = null )
	{
		$this->className = $className;
		$this->callerFunc = $callerFunc;
		$this->invokerClassName = $invokerClassName;
	}

	public function __call( $methodName, $args )
	{
		return call_user_func( $this->callerFunc, $this->className . '::' . $methodName, $args, $this->invokerClassName );
	}

	// public function __invoke()
	// {
		// return call_user_func( $this->callerFunc, $this->className, func_get_args() );
	// }
}

class App
{
	public $version;
	public $name;

	public $filter = [];
	public $implementation = [];
	public $filterRe = [];

	public $dirs = [];

	public $instance = [];
	public $proxy = [];
	public $inject = [];

	private $_beforeCallFunc = [];
	private $_afterCallFunc = [];

	public function __construct()
	{
		// $subdirs = glob( __DIR__ . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR|GLOB_NOSORT );
		// foreach( $subdirs as $d ){
			// $this->registerDir( $d, __NAMESPACE__ );
		// }
		spl_autoload_register( [$this, 'autoload'] );
	}

	public function getDirs()
	{
		return $this->dirs;
	}

	public function version()
	{
		return $this->version;
	}

	public function name()
	{
		return $this->name;
	}

	public function autoload( $class )
	{
		$pos = strrpos( $class, '\\' );
		$classClass = substr( $class, $pos + 1 );
		$classNamespace = substr( $class, 0, $pos );

		$shortPath = $classClass;

		$found = false;

		reset( $this->dirs );

// echo "CLNS = '$classNamespace'<br>";
// _print_r( $this->dirs );

		foreach( $this->dirs as $namespace => $dirs ){
			if( $classNamespace != $namespace ) continue;

			foreach( $dirs as $dir ){
				$fl = $dir . DIRECTORY_SEPARATOR . $shortPath . '.php';
	// echo "FOR '$class' TRY '$fl':" . "<br>";

				if( file_exists($fl) ){
	// echo "GOT '$class' => '$fl'<br>";
					include_once( $fl );
					$found = true;
					break;
				}
				else {
	// echo "MISS '$class' -> '$fl'<br>";
				}
			}

			if( $found ){
				break;
			}
		}
	}

	public function registerDir( $dir, $namespace )
	{
// echo "REGISTER DIR '$dir' FOR '$namespace'<br>";

	// contains _ then it is a library
		$pos = strpos( $dir, '_/' );
		if( false !== $pos ){
			$dir = __DIR__ . '/' . substr( $dir, $pos + 2 );
			// echo "'$dir' IS LIBRARY! '$dir'<br>";
			$namespace = __NAMESPACE__;
		}

		// if( '_' == substr($dir, 0, 1) ){
			// $dir = __DIR__ . substr($dir, 1);
			// $namespace = __NAMESPACE__;
		// }

		if( ! isset($this->dirs[$namespace]) ){
			$this->dirs[$namespace] = [];
		}

		if( in_array($dir, $this->dirs[$namespace]) ) return;

		array_unshift( $this->dirs[$namespace], $dir );

	// boot?
		$bootFile = $dir . DIRECTORY_SEPARATOR . '_.php';
		if( file_exists($bootFile) ){
			$filters = require( $bootFile );
	// echo "REQUIRE '$bootFile'<br>";
	// _print_r( $filters );
			foreach( $filters as $f ){
				$this->addFilter( $f[0], $f[1], isset($f[2]) ? $f[2] : 5 );
			}
		}

		return $this;
	}

	public function inject( $hook, $value )
	{
		$this->inject[ $hook ] = $value;
	}

	public function addFilter( $hook, $func, $order )
	{
		if( ($order > 9) OR ($order < -9) ){
			echo "FOR FILTER ORDER USE FROM -9 to +9, $order is given, default to 5";
		}

		if( __CLASS__ . '::callFunc' == $hook ){
			if( $order > 0 ){
				$this->_afterCallFunc[] = $func;
			}
			elseif( $order < 0 ){
				$this->_beforeCallFunc[] = $func;
			}
			return;
		}

// echo "ADD FILTER '$hook'<br>\n";
	// implementation of interface?
		if( 0 == $order && (false === strpos($hook, '::')) ){
			$this->implementation[ $hook ] = $func;
		}
	// inject?
		elseif( false !== strpos($hook, '$') ){
			$this->inject( $hook, $func );
		}
		else {
			if( ! isset($this->filter[$hook]) ) $this->filter[$hook] = [];
			if( ! isset($this->filter[$hook][$order]) ) $this->filter[$hook][$order] = [];
			$this->filter[$hook][$order][] = $func;
		}

	// contains regular expression?
		if( ! isset($this->filterRe[$hook]) ){
			if( false !== strpos($hook, '*') ){
				$re = $hook;
				$re = preg_quote( $re );
				$re = str_replace( '\*', '.*?', $re );
				// $re = str_replace( ['\\', '*'], ['\\\\', '.*?'], $re );
				$re = '/^' . $re . '$/';
				$this->filterRe[ $hook ] = $re; 
			}
		}
	}

	public static function classExists( $obj )
	{
		return ( $obj instanceof AppProxyObject ) ? class_exists( $obj->className ) : class_exists( $obj );
	}

	public static function methodExists( $obj, $method )
	{
		if( null === $obj ) return false;
		return ( $obj instanceof AppProxyObject ) ? method_exists( $obj->className, $method ) : method_exists( $obj, $method );
	}

	public static function getClass( $obj )
	{
		return ( $obj instanceof AppProxyObject ) ? $obj->className : get_class( $obj );
	}

	public function make( $className, $invokerClassName = null )
	{
		if( is_object($className) ){
			$className = get_class( $className );
		}

		if( __CLASS__ == $className ){
			return $this;
		}

		$proxyKey = $invokerClassName ? $invokerClassName . '->' . $className : $className;
		// if( ! isset($this->proxy[$className]) ){
		if( ! isset($this->proxy[$proxyKey]) ){
			$realClassName = $className;
		// has real implementation
			while( isset($this->implementation[$realClassName]) ){
				$realClassName = $this->implementation[$realClassName];
			}
			// $realClassName = isset( $this->implementation[$className] ) ? $this->implementation[$className] : $className;
			// $this->proxy[$className] = new AppProxyObject( $realClassName, [$this, 'callFunc'] );
			// $this->proxy[$className] = new AppProxyObject( $realClassName, [$this, 'callFunc'], $invokerClassName );
			$this->proxy[$proxyKey] = new AppProxyObject( $realClassName, [$this, 'callFunc'], $invokerClassName );
		}

		// return $this->proxy[$className];
		return $this->proxy[$proxyKey];
	}

	public function construct( $className )
	{
// inject all public properties not yet set assuming they are class names
//
// public $command = CommandUserDelete::class;
// becomes $this->command = App::make( CommandUserDelete::class );

		$props = get_class_vars( $className );
		if( ! $props ) $props = [];

		$inject = [];
		foreach( $props as $propName => $propSet ){
		// have forced injects?
			$checkInject = $className . '::$' . $propName;
// echo "CHECKINJ = $checkInject<br>";
// _print_r( array_keys($this->inject) );
			if( isset($this->inject[$checkInject]) ){
				$injectValue = $this->inject[ $checkInject ];

				if( is_string($injectValue) && ('@' == substr($injectValue, 0, 1)) ){
					$callInjectFunc = substr( $injectValue, 1 );
					// echo "$callInjectFunc<br>";
					$injectValue = $this->callFunc( $callInjectFunc );
				}

				$inject[ $propName ] = $injectValue;
			}
			else {
				if( (null === $propSet) OR is_array($propSet) OR is_object($propSet) OR isset($className::${$propName}) ) continue;

				$injectClassName = $propSet;
				if( ! strlen($injectClassName) ) continue;

				if( 'self' == $propName ) $injectClassName = $className;
// echo "CHECKINJ = $checkInject<br>";
// _print_r( array_keys($this->inject) );
				$inject[ $propName ] = $this->make( $injectClassName, $className );
			}
		}

// echo "$className<br>";
// _print_r( array_keys($inject) );
		$ret = new $className( ...array_values($inject) );

	// check if any wasn't set in the class constructor
		foreach( $inject as $propName => $obj ){
			if( is_object($ret->{$propName}) ) continue;
			$ret->{$propName} = $obj;
		}

		return $ret;
	}

// find full chain of func execution
	public function getChain( $funcName )
	{
		static $already = [];
		if( isset($already[$funcName]) ) return $already[$funcName];

		$ret = [];

		$hooks = [ $funcName ];

		$invokerName = $longFuncName = null;
		$pos = strpos( $funcName, '->' );
		if( false !== $pos ){
			$longFuncName = $funcName;
			list( $invokerName, $funcName ) = explode( '->', $funcName );
			$hooks[] = $funcName;
		}

		$pos = strpos( $funcName, '::' );
		$methodName = '';
		if( false === $pos ){
			$className = $funcName;
		}
		else {
			list( $className, $methodName ) = explode( '::', $funcName );
		}

		$ret[ '0:00' ] = $funcName;

		$parents = class_exists( $className ) ? class_parents( $className, false ) : [];
		foreach( $parents as $p ){
			$parentHook = $p;
			if( strlen($methodName) ) $parentHook .= '::' . $methodName;
			array_unshift( $hooks, $parentHook );
			if( $invokerName ){
				array_unshift( $hooks, $invokerName . '->' . $parentHook );
			}
		}

		$countRunLevel = [];

		$matchingHooks = [];
		foreach( $hooks as $hook ){
			$matchingHooks[] = $hook;

		// all methods of class
// echo "TEST '$longFuncName'<br>";
			if( $invokerName && ($hook == $longFuncName) ){
				if( false !== $pos ){
					$matchingHooks[] = $invokerName . '->' . $className . '::';
				}
			}

		// all methods of class
			if( $hook == $funcName ){
				if( false !== $pos ){
					$matchingHooks[] = $className . '::';
				}
			}

		// wildcard
			foreach( $this->filterRe as $wildcardHook => $re ){
				// echo "'$hook' ON '$re': ";
				if( preg_match($re, $hook) ){
					// echo "'$hook' MATCHED '$re'<br>";
					$matchingHooks[] = $wildcardHook;
				}
				else {
					// echo "NO<br>";
				}
			}
		}

		foreach( $matchingHooks as $h ){
			if( ! isset($this->filter[$h]) ) continue;

// if( $funcName == \Plainware\PlainTracker\ModelWorker::class . '::find' ){
// echo "MATCH HOOK '$h'<br>";
// _print_r( $subChain );
// _print_r( $this->filter[$h] );
// }

			foreach( array_keys($this->filter[$h]) as $k ){
			// $k is run level
				if( ! isset($countRunLevel[$k]) ) $countRunLevel[$k] = 0;
				foreach( $this->filter[$h][$k] as $f ){
					$kRunLevel = $countRunLevel[$k]++;
					$kRunLevel = str_pad( $kRunLevel, 2, '0', STR_PAD_LEFT );

					$k2 = $k . ':' . $kRunLevel;

					$our = is_string( $f ) ? true : false;

				// our?
					if( $our ){
					// all methods of class?
						if( '::' == substr($f, -2) ){
							if( ! $methodName ) continue;
							if( ! static::methodExists( substr($f, 0, -2), $methodName) ) continue;
							$f .= $methodName;
						}

						$subChain = $this->getChain( $f );
						foreach( $subChain as $k3 => $f3 ){
						// skip existing?
							if( in_array($f3, $ret) ) continue;
							$ret[ $k2 . '_' . $k3 ] = $f3;
						}

					// unset main func?
						if( (0 == $k) && $subChain ){
							unset( $ret['0:00'] );
						}
					}
				// 3rd party
					else {
						$ret[ $k2 ] = $f;
					}
				}
			}
		}

	// ksort( $ret );
		ksort( $ret, SORT_NUMERIC );
		$already[ $funcName ] = $ret;


// if( '__construct' === $methodName ){
	// if( count($ret) > 1 ){
		// echo "GOT CHAIN FOR '$funcName'<br>";
		// _print_r( $ret );
		// exit;
	// }
// }

// echo "FUNCNAME = $funcName<br>";

// if( $methodName == 'send' ){
// if( $funcName == 'Plainware\EmailStandalone::send' ){
	// echo "GOT CHAIN FOR '$funcName'<br>";
	// _print_r( $ret );
	// exit;
// }

// echo "FN = '$funcName'<br>";
// _print_r( $ret );


/*
if( $funcName == \Plainware\PlainTracker\PresenterRecord::class . '::htmlState' ){
	echo "FN = '$funcName'<br>";

	// echo "All hooks:<br>";
	// _print_r( $hooks );

	// echo "Matching hooks:<br>";
	// _print_r( $matchingHooks );

	// foreach( $matchingHooks as $h ){
		// echo "HOOK = '$h'<br>FILTERS:<br>";
		// if( isset($this->filter[$h]) ){
			// _print_r( $this->filter[$h] );
		// }
		// else {
			// echo 'NONE<br>';
		// }
	// }

	echo "FINAL:<br>";
	_print_r( $ret );
	exit;
}
*/

		return $ret;
	}

	public function callChain( array $chain, array $args )
	{
		$ret = null;

$debug = false;
// if( 'Plainware\Shift\ModelShift::find' == current($chain) ){
	// $debug = true;
// }


		$keys = array_keys( $chain );
		$prefix = '';

		foreach( $chain as $k => $f ){
		// skip child keys as they are supposed to be called in subchain
			if( strlen($prefix) ){
				if( $prefix == substr($k, 0, strlen($prefix)) ){
					continue;
				}
				else {
					$prefix = '';
				}
			}

			$subPos = strpos( $k, '_' );
			$levelPos = strpos( $k, ':' );
			$myRunLevel = (int) substr( $k, 0, $levelPos );

			$myArgs = $args;
			if( $myRunLevel > 0 ){
			// don't call listeners on null return, means the main func didn't complete properly
				if( null === $ret ){
					continue;
				}
				array_unshift( $myArgs, $ret );
			}

		// if ours?
			$our = is_string( $f ) ? true : false;

		// ours?
			if( $our ){
			// simple func - call normally
				if( false === $subPos ){
					$thisRet = $this->_callFunc( $f, $myArgs );
				}
			// subchain - call recursively
				else {
					$prefix = substr( $k, 0, $subPos + 1 );
					reset( $keys );
					$subChain = [];
					foreach( $keys as $k2 ){
						if( $prefix != substr($k2, 0, $subPos + 1) ) continue;
						$k3 = substr( $k2, $subPos + 1 );
						$subChain[ $k3 ] = $chain[ $k2 ];
					}
					$thisRet = $this->callChain( $subChain, $myArgs );

/*
					$thisRet = $this->callFunc( $f, $myArgs );
*/

if( $debug ){
// echo "CALL RECURS";
// _print_r( $subChain );
// exit;
}


				}
			}
		// otherwise 3rd party callable
			else {
				$thisRet = call_user_func_array( $f, $myArgs );
			}

		// before call, may drop chain or modify arguments
			if( $myRunLevel < 0 ){
			// no return nothing to do
				if( null === $thisRet ){
					continue;
				}

			// return false - drop chain
				if( false === $thisRet ){
					break;
				}

			// replace return
				if( is_array($thisRet) && isset($thisRet[0]) && (false === $thisRet[0]) ){
					$ret = $thisRet[1];
					break;
				}

			// adjust input args if return array
				if( ! is_array($thisRet) ){
					$args[0] = $thisRet;
				}
			// for controllers to adjust $x
				elseif( is_array($args[0]) ){
					$args[0] = $thisRet;
				} 
				elseif( count($thisRet) == count($args) ){
					$args = $thisRet;
				}
				else {
				}
			}
		// after call, may modify return
			else {
			// modify return
				if( null !== $thisRet ){
					$ret = $thisRet;
				}
			}
		}

		return $ret;
	}

	public function callFunc( $funcName, array $args = [], $invokerName = null )
	{
		if( $invokerName ) $funcName = $invokerName . '->' . $funcName;

		$chain = $this->getChain( $funcName );
		$ret = $this->callChain( $chain, $args );

		return $ret;
	}

	public function _callFunc( $funcName, array $args = [] )
	{
	// has anything to call before?
		foreach( $this->_beforeCallFunc as $f ){
			call_user_func( $f, $funcName, ...$args );
		}

// echo "CALLONE: '$funcName'<br>";
		if( false === strpos($funcName, '::') ){
			$className = $funcName;
			$methodName = '';
		}
		else {
			list( $className, $methodName ) = explode( '::', $funcName );
		}

	// need to instantiate?
		if( ! isset($this->instance[$className]) ){
			$this->instance[$className] = $this->construct( $className );

		// after construct
			$afterConstructHook = $className . '::__construct';
			if( isset($this->filter[$afterConstructHook]) ){
				$this->callFunc( $afterConstructHook, [] );
			}
		}

		$func = strlen($methodName) ? [ $this->instance[$className], $methodName ] : $this->instance[$className];

		$isRender = ( ('render' == substr($methodName, 0, strlen('render'))) OR ('html' == substr($methodName, 0, strlen('html'))) );

		if( $isRender ){
			ob_start();
		}

// if( strlen($className) ){
	// echo "CALLING $className::$methodName<br>";
// }
// }

		if( '__construct' == $methodName ){
			$ret = $this->instance[ $className ];
		}
		else {
			$ret = call_user_func_array( $func, $args );
		}

		if( $isRender ){
			$echoRet = ob_get_clean();

			if( (null === $ret) && is_string($echoRet) ){
				$echoRet = trim( $echoRet );
				if( strlen($echoRet) ) $ret = $echoRet;
			}
		}

	// has anything to call after?
		foreach( $this->_afterCallFunc as $f ){
			call_user_func( $f, $ret, $funcName, ...$args );
		}

		return $ret;
	}

	public static final function versionNumFromString( $verString )
	{
		$ret = explode( '.', $verString );
		if( strlen($ret[2]) < 2 ) $ret[2] = '0' . $ret[2];
		$ret = join( '', $ret );
		$ret = (int) $ret;
		return $ret;
	}
}
}

namespace {
if( ! function_exists('_print_r') ){
function _print_r( $thing ){
	$wpAdmin = defined('WPINC') && is_admin();

	if( $wpAdmin ){
		echo '<div style="margin-left: 15em;">';
	}

	echo '<pre>';
	print_r( $thing );
	echo '</pre>';

	if( $wpAdmin ){
		echo '</div>';
	}
}
}

if( ! function_exists('\sanitize_text_field') ){
function sanitize_text_field( $ret ){
	return $ret;
}
}

if( ! function_exists('\esc_html') ){
function esc_html( $ret ){
	if( null === $ret ) $ret = '';
	$ret = htmlspecialchars( $ret );
	return $ret;
}
}

if( ! function_exists('\esc_attr') ){
function esc_attr( $ret ){
	if( null === $ret ) $ret = '';
	$ret = htmlspecialchars( $ret, ENT_QUOTES );
	return $ret;
}
}

if( ! function_exists('\esc_textarea') ){
function esc_textarea( $ret ){
	return $ret;
}
}
}