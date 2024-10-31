<?php
namespace Plainware;

class HtmlPager
{
	public $self = __CLASS__;

	public function slice( array $list )
	{
		$ret = array_slice( $list, $this->offset, $this->limit, true );
		return $ret;
	}

	public function attr()
	{
		$ret = [
			'limit' => 10,
			'offset' => 0,
			'paramLimit' => 'limit-',
			'paramOffset' => 'offset-',
		];
		return $ret;
	}

	public function getLimitOffset( array $x, array $attr = [] )
	{
		$attr = $attr + $this->self->attr();

		$limit = isset( $x[ $attr['paramLimit'] ] ) ? $x[ $attr['paramLimit'] ] : $attr['limit'];
		$offset = isset( $x[ $attr['paramOffset'] ] ) ? $x[ $attr['paramOffset'] ] : $attr['offset'];

		$ret = [ $limit, $offset ];
		return $ret;
	}

	public function render( array $x, $totalCount, array $attr = [] )
	{
		$attr = $attr + $this->self->attr();

		$to = $attr['to'] ?? '.';
		$to = esc_attr( $to );
		$attrLink = $attr['attr'] ?? [];

		$limit = $attr['limit'];
		$offset = $attr['offset'];
		$paramOffset = $attr['paramOffset'];

		$ret = '';

		if( ! $totalCount ) return $ret;
		if( $totalCount <= $limit ) return $ret;

		$displayed1 = $totalCount ? $offset + 1 : 0;
		$displayed2 = $limit ? min( $offset + $limit, $totalCount ) : $totalCount;

		$prevOffset = null;
		if( $limit && $offset ){
			$prevOffset = max( $offset - $limit, 0 );
		}

		$firstOffset = null;
		if( $offset && $prevOffset ){
			$firstOffset = 0;
		}

		$nextOffset = null;
		if( $limit && ($totalCount > $displayed2) ){
			$nextOffset = $offset + $limit;
		}

		$lastOffset = null;
		if( $limit && ($totalCount > $limit) ){
			$lastOffset = ( ceil($totalCount / $limit) - 1 ) * $limit;
			if( $lastOffset == $nextOffset ) $lastOffset = null;
			if( $lastOffset == $offset ) $lastOffset = null;
		}

		$strAttr = '';
		if( $attr ){
			$strAttr = [];
			foreach( $attrLink as $k => $v ){
				$strAttr[] = $k . '="' . esc_attr($v) . '"'; 
			}
			$strAttr = join( ' ', $strAttr );
		}

		$p = [];
		$passTemp = [ 'a-', 's-' ];
		foreach( $passTemp as $e ){
			if( isset($x[$e]) ){
				$p[ $e ] = $x[ $e ];
			}
		}
?>

<nav>
<ul>
	<?php if( null !== $firstOffset ) : ?>
		<?php
		if( ! $firstOffset ) $firstOffset = 'null';
		$p2 = $p;
		$p2[ $paramOffset ] = $firstOffset;
		?>
		<li>
		<a data-pw-keep-scroll<?php if( $strAttr ): ?> <?php echo $strAttr; ?><?php endif; ?>  title="__First page__" href="URI:<?php echo $to; ?>?<?php echo http_build_query($p2); ?>"><i>&laquo;</i><span>__First page__</span></a>
		</li>
	<?php endif; ?>

	<?php if( null !== $prevOffset ) : ?>
		<?php
		if( ! $prevOffset ) $prevOffset = 'null';
		$p2 = $p;
		$p2[ $paramOffset ] = $prevOffset;
		?>
		<li>
		<a data-pw-keep-scroll<?php if( $strAttr ): ?> <?php echo $strAttr; ?><?php endif; ?> title="__Previous page__" href="URI:<?php echo $to; ?>?<?php echo http_build_query($p2); ?>"><i>&lsaquo;</i><span>__Previous page__</span></a>
		</li>
	<?php endif; ?>

	<li>
	<span>
		<?php echo $displayed1; ?> - <?php echo $displayed2; ?> / <?php echo $totalCount; ?>
	</span>
	</li>

	<?php if( $nextOffset ) : ?>
		<?php
		$p2 = $p;
		$p2[ $paramOffset ] = $nextOffset;
		?>
		<li>
		<a data-pw-keep-scroll<?php if( $strAttr ): ?> <?php echo $strAttr; ?><?php endif; ?> title="__Next page__" href="URI:<?php echo $to; ?>?<?php echo http_build_query($p2); ?>"><span>__Next page__</span><i>&rsaquo;</i></a>
		</li>
	<?php endif; ?>

	<?php if( $lastOffset ) : ?>
		<?php
		$p2 = $p;
		$p2[ $paramOffset ] = $lastOffset;
		?>
		<li>
		<a data-pw-keep-scroll<?php if( $strAttr ): ?> <?php echo $strAttr; ?><?php endif; ?> title="__Last page__" href="URI:<?php echo $to; ?>?<?php echo http_build_query($p2); ?>"><span>__Last page__</span><i>&raquo;</i></a>
		</li>
	<?php endif; ?>
</ul>
</nav>

<?php
	}
}