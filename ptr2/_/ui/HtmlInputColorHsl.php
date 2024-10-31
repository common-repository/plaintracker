<?php
namespace Plainware;

class HtmlInputColorHsl
{
	public function render( $name, $value, array $attr = [] )
	{
		$colors = range( 0, 350, 20 );
		// _print_r( $colors );

		$showNone = isset( $attr['nocolor'] ) && $attr['nocolor'] ? true : false;
		if( $showNone ){
			array_unshift( $colors, '' );
		}

		if( null === $value ) $value = current( $colors );

		$attr['name'] = $name;

		$htmlAttr = Html::attr( $attr );
		
?>

<select <?php echo $htmlAttr; ?> style="background-color: hsl(<?php echo esc_attr($value); ?>, 75%, 60%)" onchange="this.style.backgroundColor='hsl(' + this.value + ', 75%, 60%)';">
<?php foreach( $colors as $color ) : ?>
	<?php if( strlen($color) ) : ?>
		<option value="<?php echo esc_attr($color); ?>" style="background-color: hsl(<?php echo esc_attr($color); ?>, 75%, 60%);"<?php if( $color == $value ): ?> selected<?php endif; ?>>
			<?php echo esc_html($color); ?>
		</option>
	<?php else : ?>
		<option value="<?php echo esc_attr($color); ?>" style=""<?php if( $color == $value ): ?> selected<?php endif; ?>>[__Default color__]</option>
	<?php endif; ?>
<?php endforeach; ?>
</select>

<?php
	}

	public function grab( $name, array $post )
	{
		$ret = $post[$name] ?? '';
		return $ret;
	}
}