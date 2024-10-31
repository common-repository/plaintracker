<?php
namespace Plainware;

class HtmlInputDuration
{
	public $html = Html::class;
	public $form = HtmlForm::class;

	public function render( $name, $value = 0, array $attr = [] )
	{
		$pos = strpos( $name, '[' );
		if( false === $pos ){
			$nameH = $name . '_h';
			$nameM = $name . '_m';
		}
		else {
			$nameH = substr( $name, 0, $pos ) . '_h' . substr( $name, $pos );
			$nameM = substr( $name, 0, $pos ) . '_m' . substr( $name, $pos );
		}

		if( null === $value ){
			$valueH = '';
			$valueM = '';
		}
		else {
			$valueH = floor( $value / (60 * 60) );
			$valueM = floor( ($value - $valueH * (60 * 60)) / 60 );
		}

// echo "$valueH $valueM<br>";

		$optionH = [];
		foreach( range(0, 24, 1) as $k ){
			$v = $k;
			$optionH[ $k ] = $k . '__h__';
		}

		$step = $attr['step'] ?? 5;
		if( $step >= 60 ) $step = ceil( $step/60 );

		$optionM = [];
		foreach( range(0, 59, $step) as $k ){
			$v = $k;
			// $v = sprintf( '%02d', $k );
			$optionM[ $k ] = $v . '__m__';
		}

		$error = $this->form->getError( $name );
?>

<ul>
	<li>
		<select name="<?php echo esc_attr($nameH); ?>">
		<?php foreach( $optionH as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $valueH ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</li>
	<li>
		<select name="<?php echo esc_attr($nameM); ?>">
		<?php foreach( $optionM as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $valueM ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</li>
</ul>

<?php if( strlen($error) ): ?><?php echo $this->html->renderInputError( $error ); ?><?php endif; ?>

<?php
	}

	public function grab( $name, array $post )
	{
		$nameIndex = null;
		$pos = strpos( $name, '[' );
		if( false === $pos ){
			$nameH = $name . '_h';
			$nameM = $name . '_m';
		}
		else {
			$nameH = substr( $name, 0, $pos ) . '_h';
			$nameM = substr( $name, 0, $pos ) . '_m';
			$nameIndex = substr( $name, $pos + 1, -1 );
		}

		$ret = null;

		if( null !== $nameIndex ){
			if( isset($post[$nameH][$nameIndex]) && isset($post[$nameM][$nameIndex]) ){
				$hour = (int) $post[ $nameH ][ $nameIndex ];
				$minute = (int) $post[ $nameM ][ $nameIndex ];
				// $value = sprintf( '%02d', $hour ) . sprintf( '%02d', $minute );
				$ret = 60 * 60 * $hour + 60 * $minute;
			}
		}
		else {
			if( isset($post[$nameH]) && isset($post[$nameM]) ){
				$hour = (int) $post[ $nameH ];
				$minute = (int) $post[ $nameM ];
				// $value = sprintf( '%02d', $hour ) . sprintf( '%02d', $minute );
				$ret = 60 * 60 * $hour + 60 * $minute;
			}
		}

		return $ret;
	}
}