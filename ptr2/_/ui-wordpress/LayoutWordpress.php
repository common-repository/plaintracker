<?php
namespace Plainware;

class LayoutWordpress
{
	public $self = __CLASS__;
	public $uri = \Plainware\Uri::class;
	public $app = App::class;
	public $asset = HtmlAsset::class;
	public $inputRichTextarea = HtmlInputRichTextarea::class;

	public function render( $ret, array $x )
	{
		if( is_admin() ){
			$ret = $this->self->renderAdmin( $x, $ret );
			$ret .= $this->self->renderAdminJs( $x );
		}
		else {
			$ret = $this->self->renderFront( $x, $ret );
		}

	// move script to end script
		// $scriptPosList = [];
		// $pos1 = strpos( $ret, '<script' );
		// while( false !== $pos1 ){
			// $pos2 = strpos( $ret, '</script>', $pos1 );
			// if( false === $pos2 ){
				// break;
			// }
			// $pos2 = $pos2 + strlen( '</script>' );
			// $scriptPosList[] = [ $pos1, $pos2 ];
			// $pos1 = strpos( $ret, '<script', $pos2 );
		// }

		// $retScript = '';
		// foreach( $scriptPosList as $scriptPos ){
			// $retScript .= substr( $ret, $scriptPos[0], $scriptPos[1] - $scriptPos[0] );
		// }

		// foreach( array_reverse($scriptPosList) as $scriptPos ){
			// $ret = substr_replace( $ret, '', $scriptPos[0], $scriptPos[1] - $scriptPos[0] );
		// }

		// if( $retScript ){
			// $ret .= $retScript;
		// }

		return $ret;
	}

	public function renderAdminJs( array $x )
	{
		// initialize rich text area
?>
<script>
(function(){

var wpEditorConfig = { tinymce: true, quicktags: true, mediaButtons: true };

var initFunc = function(){
	var aTextarea = document.querySelectorAll( 'textarea[data-pw-rich]' );
	for( var i = 0; i < aTextarea.length; i++ ){
		wp.editor.initialize( aTextarea[i].id, wpEditorConfig );
	}
}

if( typeof wp !== 'undefined' ){
	initFunc();
}
else {
	document.addEventListener( 'DOMContentLoaded', initFunc );
}

})();
</script>
<?php
	}

	public function renderAdmin( array $x, $ret )
	{
		// $ret = str_replace( '<table', '<table class="widefat striped"', $ret );

		// $ret = str_replace( '<table class="', '<_table class="widefat striped ', $ret );
		// $ret = str_replace( '<table', '<table class="widefat striped"', $ret );
		// $ret = str_replace( '<_table', '<table', $ret );

		$ret = str_replace( '<table class="', '<_table class="widefat ', $ret );
		$ret = str_replace( '<table', '<table class="widefat"', $ret );
		$ret = str_replace( '<_table', '<table', $ret );

		// $ret = str_replace( '<table', '<table class="widefat"', $ret );

	// submit buttons
		$replace = [
			'<button type="submit"' => '<button type="submit" class="button button-primary"',
			'<button type="button"' => '<button type="button" class="button button-secondary"',
			'<button' => '<button class="button button-secondary"',
		];
		$ret = strtr( $ret, $replace );

	// links in in main <nav><ul>
		if( 1 ){
			$ma = [];
			// preg_match_all( '/\<nav>\s*<ul.+\<\/nav\>/smU', $ret, $ma );
			// preg_match_all( '/\<nav>\s*<[uo]l.+\<\/nav\>/smU', $ret, $ma );
			// preg_match_all( '/\<nav>.*<[uo]l.+\<\/nav\>/smU', $ret, $ma );
			preg_match_all( '/\<nav>.+\<\/nav\>/smU', $ret, $ma );

			if( count($ma[0]) ){
				$replace = [];
				for( $ii = 0; $ii < count($ma[0]); $ii++ ){
					$from = $ma[0][$ii];
					$to = str_replace( '<a ', '<a class="page-title-action" ', $from );
					$replace[ $from ] = $to;
				}
				$ret = strtr( $ret, $replace );
			}
		}

	// links in in main <form><footer>
		$ma = [];
		preg_match_all( '/\<form.*<footer(.+)\<\/footer\>.*<\/form>/smU', $ret, $ma );
		// preg_match_all( '/\<footer(.+)\<\/footer\>/smU', $ret, $ma );

// echo "COUNT3 = " . count($ma[1]) . '<br>';
		if( count($ma[1]) ){
			$replace = [];
			for( $ii = 0; $ii < count($ma[1]); $ii++ ){
				$from = $ma[1][$ii];
				$to = str_replace( '<a ', '<a class="page-title-action" ', $from );
				$replace[ $from ] = $to;
			}
			$ret = strtr( $ret, $replace );
		}

		$layoutParam = $this->uri->getLayoutParam();
		$layout = isset( $x[$layoutParam] ) ? $x[$layoutParam] : null;
		if( (null !== $layout) && ('full' != $layout) ){
			return $ret;
		}

		$assetList = [];
		$assetList[ '_/ui/asset/core.css' ] = __DIR__ . '/../ui/asset/core.css';
		$assetList[ '_/ui-wordpress/asset/admin.css' ] = __DIR__ . '/asset/admin.css';
		if( isset($x['asset']) ) $assetList = array_merge( $assetList, $x['asset'] );

// _print_r( $assetList );
// exit;

		foreach( $assetList as $assetPath => $assetFile ){
			$assetId = 'plainware-' . $assetPath;
			$assetUri = $this->asset->uri( $assetPath );
			if( '.css' == substr($assetPath, -strlen('.css')) ){
				wp_enqueue_style( $assetId, $assetUri );
			}
			else {
				wp_enqueue_script( $assetId, $assetUri );
			}
		}
?>
<div class="wrap">
<?php echo $ret; ?>
</div><!-- wrap -->
<?php
	}

	public function renderFront( array $x, $ret )
	{
	// submit buttons
		$ret = str_replace( '<button type="submit"', '<button type="submit" class="wp-element-button"', $ret );

		$assetList = [];
		$assetList[ '_/ui/asset/core.css' ] = __DIR__ . '/../ui/asset/core.css';
		// $assetList[ '_/ui-wordpress/asset/admin.css' ] = __DIR__ . '/asset/admin.css';
		if( isset($x['asset']) ) $assetList = array_merge( $assetList, $x['asset'] );

// _print_r( $assetList );
// exit;

		foreach( $assetList as $assetPath => $assetFile ){
			$assetId = 'plainware-' . $assetPath;
			$assetUri = $this->asset->uri( $assetPath );
			if( '.css' == substr($assetPath, -strlen('.css')) ){
				wp_enqueue_style( $assetId, $assetUri );
			}
			else {
				wp_enqueue_script( $assetId, $assetUri );
			}
		}

		return $ret;
	}
}