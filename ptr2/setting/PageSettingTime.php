<?php
namespace Plainware\PlainTracker;

class PageSettingTime
{
	public $self = __CLASS__;

	public $setting = \Plainware\Setting::class;
	public $t = \Plainware\Time::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title( array $x )
	{
		$ret = '__Date and time__';
		return $ret;
	}

	public function post( array $x )
	{
		foreach( $x['post'] as $id => $value ){
			$this->setting->set( $id, $value );
		}

		$x[ 'redirect' ] = '.';
		$x[ 'msg' ][] = '__Settings updated__';

		return $x;
	}

	public function get( array $x )
	{
		$ks = [
			'time_date_format', 'time_time_format', 'time_week_starts',
		];

		$v = [];
		foreach( $ks as $k ) $v[$k] = $this->setting->get( $k );
		$x[ '$v' ] = $v;

		return $x;
	}

	public function render( array $x )
	{
		$v = $x[ '$v' ];
?>

<form method="post">

<?php
$option = [
	'j M Y',

	'n/j/Y',
	'm/d/Y',
	'm-d-Y',
	'm.d.Y',

	'j/n/Y',
	'd/m/Y',
	'd-m-Y',
	'd.m.Y',

	'Y/m/d',
	'Y-m-d',
	'Y.m.d',
];

$test = $this->t->getNow();
$option = array_flip( $option );
foreach( array_keys($option) as $k ){
	$option[$k] = $this->t->formatDate( $test, $k );
}
?>
<section>
	<label>
		<span>__Date format__</span>

		<select name="time_date_format">
		<?php foreach( $option as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $v['time_date_format'] ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
		<?php if( isset($x['error']['time_date_format']) ): ?>
			<strong><?php echo esc_html( $x['error']['time_date_format'] ); ?></strong>
		<?php endif; ?>
	</label>
</section>

<?php
$option = [ '7' => '__Sun__', '1' => '__Mon__' ];
?>
<section>
	<label>
		<span>__Week starts on__</span>

		<select name="time_week_starts">
		<?php foreach( $option as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $v['time_week_starts'] ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
		<?php if( isset($x['error']['time_week_starts']) ): ?>
			<strong><?php echo esc_html( $x['error']['time_week_starts'] ); ?></strong>
		<?php endif; ?>
	</label>
</section>

<?php
// time_time_format
$testTimes = [ '202206100800', '202206101400', '202206101515' ];
$option = [ 'g:ia', 'g:i A', 'H:i' ];
$option = [ 'g:ia', 'g:i A', '12short', '12xshort', 'H:i', '24short' ];

$option = array_flip( $option );
foreach( array_keys($option) as $k ){
	$thisView = [];
	foreach( $testTimes as $test ){
		$thisView[] = $this->t->formatTime( $test, $k );
	}
	$thisView = join( ', ', $thisView );
	$option[ $k ] = $thisView;
}
?>
<section>
	<label>
		<span>__Time format__</span>

		<select name="time_time_format">
		<?php foreach( $option as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $v['time_time_format'] ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
		<?php if( isset($x['error']['time_time_format']) ): ?>
			<strong><?php echo esc_html( $x['error']['time_time_format'] ); ?></strong>
		<?php endif; ?>
	</label>
</section>

<footer>
	<button type="submit">__Save__</button>
</footer>

</form>

<?php
	}
}