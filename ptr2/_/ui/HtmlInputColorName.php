<?php
namespace Plainware;

class HtmlInputColorName
{
	public function render( $name, $value, array $attr = [] )
	{
		static $instanceId = 0;
		$instanceId++;

		$colors = [
			'black', 'brown', 'blue', 'fuchsia', 'gray', 'green', 'navy', 'orange',
			'purple', 'red', 'turquoise', 'lime', 'salmon', 'gold', 'aqua', 'maroon',
		];
		sort( $colors );

		$showNone = isset( $attr['nocolor'] ) && $attr['nocolor'] ? true : false;
		if( $showNone ){
			array_unshift( $colors, '' );
		}

		$colorBoxId = isset($attr['target']) ? $attr['target'] : 'pw-html-input-color-' . $instanceId;
		if ( null === $value ) $value = current( $colors );

		$attr['name'] = $name;

		$htmlAttr = Html::attr( $attr );
?>

<select <?= $htmlAttr; ?> style="color: <?php echo esc_attr($value); ?>" onchange="this.style.color=this.value;">
<?php foreach( $colors as $color ) : ?>
	<?php if( strlen($color) ) : ?>
		<option value="<?php echo esc_attr($color); ?>" style="color: <?php echo esc_attr($color); ?>;"<?php if( $color == $value ): ?> selected<?php endif; ?>>
			<?php echo esc_html($color); ?>
		</option>
	<?php else : ?>
		<option value="" style="color: inherit;"<?php if( $color == $value ): ?> selected<?php endif; ?>>[__No color__]</option>
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