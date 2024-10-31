<?php
namespace Plainware;

class LayoutPrint
{
	public $self = __CLASS__;
	public $uri = Uri::class;
	public $asset = HtmlAsset::class;

	public function render( $ret, array $x )
	{
		$layoutParam = $this->uri->getLayoutParam();
		$layout = isset( $x[$layoutParam] ) ? $x[$layoutParam] : null;
		if( 'print' != $layout ) return $ret;

	// title
		$page = $x[ '$page' ];
		$title = $page->title( $x );
		if( is_array($title) ) $title = current( $title );

		$assetList = [];
		$assetList[ '_/ui/asset/core.css' ] = __DIR__ . '/../ui/asset/core.css';
		if( isset($x['asset']) ) $assetList = array_merge( $assetList, $x['asset'] );

		$asset = [ 'css' => [], 'js' => [] ];
		foreach( $assetList as $k => $e ){
			if( '.css' == substr($e, -strlen('.css')) ){
				$asset['css'][$k] = $e;
			}
			if( '.js' == substr($e, -strlen('.js')) ){
				$asset['js'][$k] = $e;
			}
		}
		$isPrintView = false;
		$assetId = 1;

	// remove nav's
		$ma = array();
		preg_match_all( '/\<nav\>.+\<\/nav\>/smU', $ret, $ma );

		$count = count( $ma[0] );
		for( $ii = 0; $ii < $count; $ii++ ){
			$from = $ma[0][$ii];
			$to = '';
			$ret = str_replace( $from, $to, $ret );
		}

	// remove other a's
		$ma = array();
		preg_match_all( '/\<a[\s].+\>(.+)\<\/a\>/smU', $ret, $ma );

		$count = count( $ma[0] );
		for( $ii = 0; $ii < $count; $ii++ ){
			$from = $ma[0][$ii];
			// $to = $ma[1][$ii];
			// $to = '<a>' . $ma[1][$ii] . '</a>';
			$to = '' . $ma[1][$ii] . '';
			$ret = str_replace( $from, $to, $ret );
		}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc_html( $title ); ?></title>
<?php foreach( $asset['css'] as $assetPath => $assetFullPath ): ?>
<link rel="stylesheet" type="text/css" id="pw-asset-<?= $assetId++; ?>" href="<?= $this->asset->uri( $assetPath ); ?>">
<?php endforeach; ?>
<?php foreach( $asset['js'] as $assetPath => $assetFullPath ): ?>
<script language="JavaScript" type="text/javascript" id="pw-asset-<?= $assetId++; ?>" src="<?= $this->asset->uri( $assetPath ); ?>"></script>
<?php endforeach; ?>
</head>
<body>
<?php if( $isPrintView && (! defined('PW_DEV')) ): ?>
<script>window.print();</script>
<?php endif; ?>
<div id="pw2">
<?php echo $ret; ?>
</div><!-- pw2: end -->
</body>
</html>
<?php
	}
}