<?php
namespace Plainware;

class HtmlInputDate
{
	public $form = HtmlForm::class;
	public $t = Time::class;

	public function render( $name, $value = '' )
	{
		if( null === $value ) $value = '';
		if( strlen($value) < 8 ) $value = '';

		$nameY = $name . '_y';
		$nameM = $name . '_m';
		$nameD = $name . '_d';

		$submittedValue = $this->form->getValue( $name );
		if( null !== $submittedValue ) $value = $submittedValue;

		if( ! strlen($value) ){
			$value = $this->t->getDate( $this->t->getNow() );
		}

		$valueY = $this->t->getYear( $value );
		$valueM = $this->t->getMonth( $value );
		$valueD = $this->t->getDay( $value );

		$error = $this->form->getError( $name );
?>

<ul>
	<li>
		<?php
		$option = range( 1, 31 );
		$option = array_combine( $option, $option );
		?>
		<select name="<?php echo esc_attr($nameD); ?>">
		<?php foreach( $optionH as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $valueD ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</li>

	<li>
		<?php
		$option = [];
		foreach( range(1, 12) as $k ) $option[$k] = Time::formatMonthName( $k );
		?>
		<select name="<?php echo esc_attr($nameM); ?>">
		<?php foreach( $optionH as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $valueM ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</li>

	<li>
		<?php
		$option = range( $valueY - 10, $valueY + 10 );
		$option = array_combine( $option, $option );
		?>
		<select name="<?php echo esc_attr($nameY); ?>">
		<?php foreach( $optionH as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $valueY ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</li>
</ul>

<?php if( strlen($error) ) : ?>
	<div><strong><?= esc_html( $error ); ?></strong></div>
<?php endif; ?>

<?php
	}

	public function grab( $name, array $post )
	{
		$nameY = $name . '_y';
		$nameM = $name . '_m';
		$nameD = $name . '_d';

		$ret = null;
		if( isset($post[$nameY]) ){
			$year = $post[ $nameY ];
			$month = $post[ $nameM ];
			$day = $post[ $nameD ];
			$ret = $year . sprintf('%02d', $month) . sprintf('%02d', $day);
		}

		return $ret;
	}
}