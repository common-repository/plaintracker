<?php
namespace Plainware;

class HtmlInputTimeHourMinute
{
	public $t = Time::class;

	public function render( $name, $value = null, $attr = [] )
	{
		$today = '20230109';

		$optionH = [];

		$stepMinute = 5;
		if( isset($attr['step']) ){
			$stepMinute = (int) ( $attr['step'] / 60 );
			unset( $attr['step'] );
		}

		if( isset($attr['with-none']) ){
			if( $attr['with-none'] ){
				$optionH[ '' ] = ' &times; ' . '__None__';
			}
			unset( $attr['with-none'] );
		}

		$withStartDay = false;
		if( isset($attr['with-start-day']) ){
			if( $attr['with-start-day'] ){
				$optionH[ 'start' ] = '- ' . '__Start of day__' . ' -';
				$withStartDay = true;
			}
			unset( $attr['with-start-day'] );
		}

		foreach( range(0, 23, 1) as $k ){
			$v = $k;

			$k2 = $this->t->fromDateSeconds( $today, $k * 60 * 60 );
			$v2 = $this->t->formatHour( $k2 );

			$v = sprintf( '%02d', $k );
			$optionH[ $k ] = $v2;
		}

		$withEndDay = false;
		if( isset($attr['with-end-day']) ){
			if( $attr['with-end-day'] ){
				$optionH[ 'end' ] = '- ' . '__End of day__' . ' -';
				$withEndDay = true;
			}
			unset( $attr['with-end-day'] );
		}

		$optionM = [];
		// foreach( range(0, 55, 5) as $k ){
		foreach( range(0, 59, $stepMinute) as $k ){
			$v = $k;
			$v = sprintf( '%02d', $k );
			$optionM[ $k ] = $v;
		}

		$attrH = $attr;
		// $attrH['style'] = 'width: 8em;';

		$attrM = $attr;
		// $attrM['style'] = 'width: 4em;';

		$nameH = $name . '_h';
		$nameM = $name . '_m';

		$valueH = null;
		$valueM = null;

		if( (null === $value) OR ('' === $value) ){
			$valueH = $withStartDay ? 'start' : 0;
			$valueM = 0;
		}
		elseif( $withEndDay && (24*60*60 == $value) ){
			$valueH = 'end';
			$valueM = 0;
		}
		else {
			$value = (int) $value;
			if( (0 === $value) && $withStartDay ){
				$valueH = 'start';
				$valueM = 0;
			}
			else {
				$valueH = floor( $value / (60 * 60) );
				$valueM = floor( ($value - $valueH * (60 * 60)) / 60 );
			}
		}
?>

<ul>
	<li>
		<select name="<?php echo esc_attr($nameH); ?>">
		<?php foreach( $optionH as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $valueH ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</li>
	<li>:</li>
	<li>
		<select name="<?php echo esc_attr($nameM); ?>">
		<?php foreach( $optionM as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $valueM ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</li>
</ul>

<?php
	}

	public function grab( $name, array $post )
	{
		$ret = null;

		$nameH = $name . '_h';
		$nameM = $name . '_m';

		if( isset($post[$nameH]) && isset($post[$nameM]) ){
			$hour = $post[ $nameH ];
			$minute = $post[ $nameM ];

			if( strlen($hour) ){
				if( 'end' === $hour ){
					$ret = 24 * 60 * 60;
				}
				elseif( 'start' === $hour ){
					$ret = 0;
				}
				else {
					$ret = 60 * 60 * (int) $hour + 60 * (int) $minute;
				}
			}
			else {
				$ret = null;
			}
		}

		return $ret;
	}
}