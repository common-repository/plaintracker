<?php
namespace Plainware;

class HtmlInputColor
{
	public function render( $name, $value, array $attr = [] )
	{
		static $instanceId = 0;
		$instanceId++;

		$colors = [
		// light
			'#cbe86b', '#ffb3a7', '#89c4f4', '#f5d76e', '#be90d4', '#fcf13a', '#ffffbb', '#ffbbff',
			'#87d37c', '#ff8000', '#73faa9', '#c8e9fc', '#cb9987', '#cfd8dc', '#99bb99', '#99bbbb',
			'#bbbbff', '#dcedc8', '#ff6347', '#ff7f50', '#cd5c5c', '#f08080', '#e9967a', '#fa8072',
			'#ffa07a', '#ffa500', '#ffd700', '#daa520', '#eee8aa', '#bdb76b', '#f0e68c', '#ffff00',
			'#9acd32', '#7cfc00', '#7fff00', '#adff2f', '#00ff00', '#90ee90', '#98fb98',
			'#8fbc8f', '#00fa9a', '#00ff7f', '#66cdaa', '#3cb371', '#20b2aa', '#00ffff', '#00ffff',
			'#e0ffff', '#00ced1', '#40e0d0', '#48d1cc', '#afeeee', '#7fffd4', '#b0e0e6', '#00bfff',
			'#add8e6', '#87ceeb', '#87cefa', '#9370db', '#d8bfd8', '#dda0dd', '#ee82ee',
			'#da70d6', '#db7093', '#ff69b4', '#ffb6c1', '#ffc0cb', '#faebd7',
			'#f5f5dc', '#ffe4c4', '#ffebcd', '#f5deb3', '#fff8dc', '#fffacd', '#fafad2', '#ffffe0',
			'#cd853f', '#f4a460', '#deb887', '#d2b48c', '#ffe4b5', '#ffdead', '#ffdab9',
			'#ffe4e1', '#fff0f5', '#faf0e6', '#fdf5e6', '#ffefd5', '#fff5ee', '#f5fffa', '#b0c4de',
			'#e6e6fa', '#fffaf0', '#f0f8ff', '#f8f8ff', '#f0fff0', '#fffff0', '#f0ffff', '#fffafa',
 			'#a9a9a9', '#c0c0c0', '#d3d3d3', '#dcdcdc', '#f5f5f5',

		// dark
			'#808000', '#ff8c00', '#800000', '#8b0000', '#a52a2a', '#b22222', '#dc143c', '#ff0000', '#ff4500',
			'#556b2f', '#6b8e23', '#006400', '#008000', '#228b22', '#2e8b57', '#2f4f4f', '#008080', '#008b8b',
			'#5f9ea0', '#4682b4', '#6495ed', '#1e90ff', '#191970', '#000080', '#00008b', '#0000cd', '#0000ff',
			'#4169e1', '#32cd32', '#8a2be2', '#4b0082', '#483d8b', '#6a5acd', '#7b68ee', '#8b008b', '#9400d3',
			'#9932cc', '#800080', '#c71585', '#8b4513', '#a0522d', '#bc8f8f', '#708090', '#778899', '#696969', 
			'#808080', '#b8860b', '#ba55d3', '#ff00ff', '#ff1493', '#d2691e', 
		];

		$showNone = isset( $attr['nocolor'] ) && $attr['nocolor'] ? true : false;
		if( $showNone ){
			array_unshift( $colors, '' );
		}

		$colorBoxId = isset($attr['target']) ? $attr['target'] : 'pw-html-input-color-' . $instanceId;
		if ( null === $value ) $value = current( $colors );

		$attr['name'] = $name;

		$htmlAttr = Html::attr( $attr );
?>

<?php if( 0 ) : ?>
<span>
	<span>
		<input readonly type="text" id="<?= $colorBoxId; ?>2" style="width: 3em; background-color: <?php echo esc_attr($value); ?>">
	</span>
	<span>
		<select id="<?= $colorBoxId; ?>"  <?= $htmlAttr; ?> onchange="document.getElementById('<?= $colorBoxId; ?>').style.backgroundColor=this.value;">
		<?php foreach( $colors as $color ) : ?>
			<?php if( strlen($color) ) : ?>
				<option value="<?= esc_attr($color); ?>" style="color: #000; background-color: <?= esc_attr($color); ?>;"<?php if( $color == $value ): ?> selected<?php endif; ?>><?= esc_html($color); ?></option>
			<?php else : ?>
				<option value="<?= esc_attr($color); ?>" style=""<?php if( $color == $value ): ?> selected<?php endif; ?>>- __No Color__ -</option>
			<?php endif; ?>
		<?php endforeach; ?>
		</select>
	</span>
</span>
<?php endif; ?>


<?php if( 1 ) : ?>
<select <?= $htmlAttr; ?> style="background-color: <?php echo esc_attr($value); ?>" onchange="this.style.backgroundColor=this.value;">
<?php foreach( $colors as $color ) : ?>
	<?php if( strlen($color) ) : ?>
		<option value="<?= esc_attr($color); ?>" style="color2: #000; background-color: <?= esc_attr($color); ?>;"<?php if( $color == $value ): ?> selected<?php endif; ?>>
			<?= esc_html($color); ?>
		</option>
	<?php else : ?>
		<option value="<?= esc_attr($color); ?>" style=""<?php if( $color == $value ): ?> selected<?php endif; ?>>[__Default color__]</option>
	<?php endif; ?>
<?php endforeach; ?>
</select>
<?php endif; ?>

<?php if( 0 ) : ?>
<select name="<?= esc_attr($name); ?>" onchange="document.getElementById('<?= esc_attr($colorBoxId); ?>').style.backgroundColor=this.value + '66';">
<?php foreach( $colors as $color ) : ?>
	<option value="<?= esc_attr($color); ?>" style="color: #000; background-color: <?= esc_attr($color); ?>;"<?php if( $color == $value ): ?> selected<?php endif; ?>><?= esc_html($color); ?></option>
<?php endforeach; ?>
</select>
<?php endif; ?>

<?php if( 0 && (! isset($attr['target'])) ) : ?>
<div style="margin-top: .5em;"><input type="text" id="<?= esc_attr($colorBoxId); ?>" disabled></div>
<?php endif; ?>

<?php
	}

	public function grab( $name, array $post )
	{
		$ret = $post[$name] ?? '';
		return $ret;
	}
}