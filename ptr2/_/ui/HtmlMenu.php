<?php
namespace Plainware;

class HtmlMenu
{
	public $self = __CLASS__;
	public $uri = Uri::class;

	public function finalizeOne( array $item, array $x )
	{
		$to = $item[ 0 ];
		$title = $item[ 1 ];
		$attr = $item[ 2 ] ?? [];

		if( null === $to ){
			$to = '';
		}

		if( ! strlen($title) ){
			$ret = false;
			return $ret;
		}

		if( $this->uri->isFull($to) ){
			$ret = [ $to, $title ];
			return $ret;
		}

		$params = [];
		if( is_array($to) ){
			list( $to, $params ) = $to;
		}

		$currentUri = $x['$uri'];

		$uri = $this->uri->fromString( $to, $currentUri );
		$uri->params = array_merge( $uri->params, $params );
// echo "GOT PAGE FOR '" . $uri->slug . "'<br>";

		$to2 = $this->uri->toString( $uri );

		$ret = [ $to2, $title, $attr ];

		return $ret;
	}

	public function finalize( array $ret, array $x )
	{
// echo __METHOD__ . '<br>';
// _print_r( array_keys($ret) );
// _print_r( array_keys($x) );
		if( ! isset($x['$uri']) ){
// echo "NO CURRENT URI!<br>";
// _print_r( array_keys($x) );
			return $ret;
		}

// echo __METHOD__ . '2<br>';
// _print_r( array_keys($ret) );

	// sort
		ksort( $ret );

	// remove sorting prefixes if any
		$ret2 = [];
		$keys = array_keys( $ret );
		foreach( $keys as $k ){
			$k2 = $k;
			$pos2 = strpos( $k, '-' );
			if( false !== $pos2 ){
				$k2 = substr( $k, $pos2 + 1 );
			}
			$ret2[ $k2 ] = $ret[ $k ];
		}
		$ret = $ret2;

		foreach( array_keys($ret) as $k ){
			$thisRet = $ret[$k];
			if( ! is_array($thisRet) ) $thisRet = [ null, $thisRet ];

			$thisRet = $this->self->finalizeOne( $thisRet, $x );

			if( false === $thisRet ){
				unset( $ret[$k] );
				continue;
			}

			$ret[$k] = $thisRet;
		}

		return $ret;
	}

	public function render( array $x, array $nav, $attr = [] )
	{
		if( ! $nav ) return;

		$nav = $this->self->finalize( $nav, $x );
		if( ! $nav ) return;

		$sep = $attr['sep'] ?? null;
		$isVertical = $attr['vert'] ?? false;
		$isMark = $attr['mark'] ?? false;
		$isCollapse = $attr['collapse'] ?? false;
		$label = $attr['label'] ?? '__Actions__';

		$currentUri = $x[ '$uri' ];
		$uri = $this->uri->make( '.', [ 'a-' => $x['a-'] ?? null ], $currentUri );
		$currentTo = $this->uri->toString( $uri );

	// skip pointing to the same page
		// foreach( array_keys($nav) as $i ){
			// if( ($nav[$i][0] == $currentTo) && (count($nav) < 2) ){
				// unset( $nav[$i] );
			// }
		// }

		if( ! $nav ) return;

		$totalCount = count( $nav );
		$ii = 0;

		$targetString = $this->uri->getParamPrefix() . 'target=_blank';
// echo "currentTo:'$currentTo'<br>";

		$listView = [];
		foreach( $nav as $e ){
			$thisUri = $this->uri->fromString( $e[0], $currentUri );
			// $isCurrent = ( (count($nav) > 1) && ($e[0] == $currentTo) ) ;
			$isCurrent = ( $e[0] == $currentTo ) ? true : false;

			$itemLabel = $e[1];
			$href = $e[0];
			$htmlTitle = strip_tags( $itemLabel );

			$attr = $e[2] ?? [];

			$target = false;
			if( false !== strpos($e[0], $targetString) ){
				$e[0] = str_replace( $targetString, '', $e[0] );
				$attr[ 'target' ] = '_blank';
				$target = true;
			}

			$strAttr = '';
			if( $attr ){
				$strAttr = [];
				foreach( $attr as $k => $v ){
					if( 'title' == $k ){
						$htmlTitle = $v;
						continue;
					}
					// $strAttr[] = $k . '="' . esc_attr($v) . '"';
					$strAttr[] = $k . '="' . $v . '"';
				}
				$strAttr = join( ' ', $strAttr );
			}

			if( $isMark ){
				if( $isCurrent ){
					// $href = null;
				}
				else {
					$itemLabel = '<small>' . $itemLabel . '</small>';
				}
			}

			if( $href ){
				$thisRet = '<a' . ( $strAttr ? ' ' . $strAttr : '' ) . ' href="URI:' . esc_attr($href) . '" title="' . esc_attr($htmlTitle) . '">' . $itemLabel . '</a>';
			}
			else {
				$thisRet = '<span>' . $itemLabel . '</span>';
			}

			if( $isCurrent ){
				if( $isCollapse && (count($nav) == 1) ){
				}
				else {
					$thisRet = '<b>' . $thisRet . '</b>';
				}
			}
			$listView[] = $thisRet;
		}
?>

<?php if( $isCollapse && (count($nav) == 1) ): ?>
	<?php echo current( $listView ); ?>
<?php else : ?>
	<?php if( $isCollapse && (count($nav) > 1) ): ?><details><summary><?php echo $label; ?></summary><?php endif; ?>
	<?php if( $isVertical ) : ?><menu title="__Actions__"><?php else: ?><ul><?php endif; ?>
		<?php $ii = 0; ?>
		<?php foreach( $listView as $thisView ) : ?>
			<li><?php echo $thisView; ?></li>
			<?php if( (null !== $sep) && ($ii != ($totalCount - 1)) ): ?>
				<li><?php echo $sep; ?></li>
			<?php endif; ?>
			<?php $ii++; ?>
		<?php endforeach; ?>
	<?php if( $isVertical ) : ?></menu><?php else : ?></ul><?php endif; ?>
	<?php if( $isCollapse && (count($nav) > 1) ): ?></details><?php endif; ?>
<?php endif; ?>

<?php
	}

	public function renderSelect( array $x, array $nav )
	{
		if( ! $nav ) return;

		$currentUri = $x[ '$uri' ];
		$uri = $this->uri->make( '.', [], $currentUri );
		$currentTo = $this->uri->toString( $uri );
?>

<select name="href" onchange="document.location.href=this.value;" data-pw-reloader="1">
<option value="URI:<?php echo $currentTo; ?>">&ndash; __Menu__ &ndash;</option>

<?php foreach( $nav as $e ): ?>
<?php
$isCurrent = ( $e[0] == $currentTo ) ? true : false;
$label = $e[1];
$label = strip_tags( $label );
?>
<option<?php if( $isCurrent ): ?> selected<?php endif; ?> value="URI:<?php echo esc_attr($e[0]); ?>"><?php echo $label; ?></option>
<?php endforeach; ?>
</select>

<?php
	}
}