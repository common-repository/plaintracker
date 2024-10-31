<?php
namespace Plainware;

class PageInputDate
{
	public $self = __CLASS__;
	public $t = Time::class;

	public function can( array $x )
	{
		return true;
	}

	public function title( array $x )
	{
	// current value
		$v = isset( $x['v'] ) ? $x['v'] : $this->t->getDate( $this->t->getNow() );

		$ret = $this->t->formatMonthName( $this->t->getMonth($v) ) . ' ' . $this->t->getYear($v);

		return $ret;
	}

	public function post( array $x )
	{
		$v = isset( $x['v'] ) ? $x['v'] : $this->t->getDate( $this->t->getNow() );

		$y = isset( $x['post']['y'] ) ? $x['post']['y'] : $this->t->getYear( $v );
		$m = isset( $x['post']['m'] ) ? $x['post']['m'] : $this->t->getMonth( $v );
		$v = $this->t->fromYearMonthDay( $y, $m, 1 );

		// navigation param
		if( isset($attr['pnav']) ){
			$p = $attr['pnav'];
		}
		elseif( isset($x['pnav']) ){
			$p = $x['pnav'];
		}
		else {
			$p = 'v';
		}

		$x['redirect'] = [ '..', [$p => $v] ] ;
		return $x;
	}

	public function hasMax( array $x, array $attr )
	{
		if( isset($attr['max']) ){
			return $attr['max'];
		}
		if( isset($x['max']) ){
			return $x['max'];
		}

		$ret = null;
		return $ret;
	}

	public function hasMin( array $x, array $attr )
	{
		if( isset($attr['min']) ){
			return $attr['min'];
		}
		if( isset($x['min']) ){
			return $x['min'];
		}

		$ret = null;
		return $ret;
	}

	public function isAllowed( array $x, array $attr, $v )
	{
		$ret = true;

		if( $min = $this->self->hasMin($x, $attr) ){
			if( $min > $v ){
				$ret = false;
				return $ret;
			}
		}

		if( $max = $this->self->hasMax($x, $attr) ){
			if( $max < $v ){
				$ret = false;
				return $ret;
			}
		}

		return $ret;
	}

	public function render( array $x )
	{
	// current value
		$v = isset( $x['v'] ) ? $x['v'] : $this->t->getDate( $this->t->getNow() );

		$min = $this->self->hasMin( $x, [] );
		if( $min && ($v < $min) ){
			$v = $min;
		}

		$max = $this->self->hasMax( $x, [] );
		if( $max && ($v > $max) ){
			$v = $max;
		}

		$ret = [];
		// $ret[ '21-year' ] = $this->self->renderYear( $x, $v );
		// $ret[ '22-month' ] = $this->self->renderMonth( $x, $v );
		// $ret[ '23-day' ] = $this->self->renderDay( $x, $v );

		$ret[ '31-year' ] = $this->self->renderCompact( $x, $v );

		return $ret;
	}

	public function renderCompact( array $x, $v, array $attr = [] )
	{
		$attr = $attr + [ 'renderDay' => null, 'renderTime' => null, 'to' => '..' ];

		$dayAttr = $attr;
		$dayAttr['render'] = $attr['renderDay'];
		unset( $dayAttr['renderDay'] );

		$monthAttr = $attr;
		$yearAttr = $attr;
?>

<div class="pw-grid-3-1">
	<div>
		<section>
			<?= $this->self->renderDay( $x, $v, $dayAttr ); ?>
		</section>
	</div>

	<div>
		<section>
			<?= $this->self->renderMonth( $x, $v, $monthAttr ); ?>
		</section>
		<section>
			<?= $this->self->renderYear( $x, $v, $yearAttr ); ?>
		</section>
	</div>
</div>

<?php
	}

	public function renderCompactWithTime( array $x, $v, array $attr = [] )
	{
		$attr = $attr + [ 'renderDay' => null, 'renderTime' => null, 'to' => '..' ];

		$dayAttr = $attr;
		$dayAttr['render'] = $attr['renderDay'];

	// navigation param
		if( isset($attr['pnav']) ){
			$pnav = $attr['pnav'];
		}
		elseif( isset($x['pnav']) ){
			$pnav = $x['pnav'];
		}
		else {
			$pnav = 'v';
		}
		$dayAttr['b'] = $pnav;
		unset( $dayAttr['renderDay'] );

		$timeAttr = $attr;
		$timeAttr['render'] = $attr['renderTime'];
		unset( $timeAttr['renderTime'] );

		$monthAttr = $attr;
		$yearAttr = $attr;
?>

<div class="pw-grid-3-1">
	<div>
		<section>
			<?= $this->self->renderDay( $x, $v, $dayAttr ); ?>
		</section>
		<section>
			<?= $this->self->renderDayTime( $x, $v, $timeAttr ); ?>
		</section>
	</div>

	<div>
		<section>
			<?= $this->self->renderMonth( $x, $v, $monthAttr ); ?>
		</section>
		<section>
			<?= $this->self->renderYear( $x, $v, $yearAttr ); ?>
		</section>
	</div>
</div>

<?php
	}

	public function renderYear( array $x, $v, array $attr = [] )
	{
		$y = $this->t->getYear( $v );

		$min = $this->self->hasMin( $x, $attr );
		if( $min ){
			$min = $this->t->getDate( $this->t->getStartMonth($min) );
		}

		$max = $this->self->hasMax( $x, $attr );
		if( $max ){
			$max = $this->t->getDate( $this->t->getEndMonth($max) );
		}

	// navigation param
		if( isset($attr['pnav']) ){
			$p = $attr['pnav'];
		}
		elseif( isset($x['pnav']) ){
			$p = $x['pnav'];
		}
		else {
			$p = 'v';
		}
?>

<table class="pw-align-center pw-noresponsive">
	<caption>__Year__</caption>
	<tbody>
	<?php for( $i = -1; $i <= 1; $i++ ): ?>
		<tr>
		<?php for( $j = -1; $j <= 1; $j++ ): ?>
			<td>
				<?php
				$y2 = $y + $i*3 + $j;
				if( $y2 > $y ){
					$v2 = $this->t->fromYearMonthDay( $y2, 1, 1 );
				}
				elseif( $y2 < $y ){
					$v2 = $this->t->fromYearMonthDay( $y2, 12, 1 );
				}
				?>
				<?php if( (0 == $i) && (0 == $j) ): ?>
					<b><?= $y2; ?></b>
				<?php else : ?>
					<?php if( ($min && ($v2 < $min)) OR ($max && ($v2 > $max)) ): ?>
						<?= $y2; ?>
					<?php else : ?>
						<a href="URI:.?<?= esc_attr( $p ); ?>=<?= esc_attr( $v2 ); ?>"><?= $y2; ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		<?php endfor; ?>
		</tr>
	<?php endfor; ?>
	</tbody>
</table>

<?php
	}

	public function renderMonth( array $x, $v, array $attr = [] )
	{
		$y = $this->t->getYear( $v );
		$m = $this->t->getMonth( $v );
		$d = $this->t->getDay( $v );

		$min = $this->self->hasMin( $x, $attr );
		if( $min ){
			$min = $this->t->getDate( $this->t->getStartMonth($min) );
		}

		$max = $this->self->hasMax( $x, $attr );
		if( $max ){
			$max = $this->t->getDate( $this->t->getEndMonth($max) );
		}

	// navigation param
		if( isset($attr['pnav']) ){
			$p = $attr['pnav'];
		}
		elseif( isset($x['pnav']) ){
			$p = $x['pnav'];
		}
		else {
			$p = 'v';
		}
?>

<table class="pw-align-center pw-noresponsive">
	<caption>__Month__</caption>
	<tbody>
	<?php for( $i = 0; $i < 4; $i++ ): ?>
		<tr>
		<?php for( $j = 1; $j <= 3; $j++ ): ?>
			<td>
				<?php
				$m2 = $i * 3 + $j;
				$v2 = $this->t->fromYearMonthDay( $y, $m2, 1 );
				?>
				<?php if( $m == $m2 ): ?>
					<b><?= $this->t->formatMonthName( $m2 ); ?></b>
				<?php else : ?>
					<?php if( ($min && ($v2 < $min)) OR ($max && ($v2 > $max)) ): ?>
						<?= $this->t->formatMonthName( $m2 ); ?>
					<?php else : ?>
						<a href="URI:.?<?= esc_attr( $p ); ?>=<?= esc_attr( $v2 ); ?>"><?= $this->t->formatMonthName( $m2 ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</td>
		<?php endfor; ?>
		</tr>
	<?php endfor; ?>
	</tbody>
</table>

<?php
	}

	public function renderDay( array $x, $v, array $attr = [] )
	{
		$today = $this->t->getDate( $this->t->getNow() );

		$attr = $attr + [ 'render' => null, 'to' => '..' ];
		$renderFunc = $attr[ 'render' ];
		$to = $attr[ 'to' ];

	// back param
		if( isset($attr['b']) ){
			$b = $attr['b'];
		}
		elseif( isset($x['b']) ){
			$b = $x['b'];
		}
		else {
			$b = 'date';
		}

		// $matrix = $this->t->getMonthMatrix( $v, 'withoverlap' );
		$matrix = $this->t->getMonthMatrix( $v, false );

		$wkds = $this->t->getWeekdays();
		$wkdLabels = [];
		foreach( $wkds as $wkd ){
			$wkdLabels[ $wkd ] = $this->t->formatWeekday( $wkd );
		}
		// _print_r( $wkds );

		$m = $this->t->getMonth( $v );
		$y = $this->t->getYear( $v );

		$min = $this->self->hasMin( $x, $attr );
		$max = $this->self->hasMax( $x, $attr );

	// prev/next
		$nextV = $this->t->getStartMonth( $v );
		$nextV = $this->t->modify( $nextV, '+1 month' );
		$nextV = $this->t->getDate( $nextV );
		if( $max && ($max < $nextV) ){
			$nextV = null;
		}
		else {
			$nextLabel = $this->t->formatMonthName( $this->t->getMonth($nextV) ) . ' ' . $this->t->getYear( $nextV );
		}

		$prevV = $this->t->getStartMonth( $v );
		$prevV = $this->t->getDate( $prevV );
		if( $min && ($min > $prevV) ){
			$prevV = null;
		}
		else {
			$prevV = $this->t->modify( $prevV, '-1 month' );
			$prevV = $this->t->getDate( $prevV );
			$prevLabel = $this->t->formatMonthName( $this->t->getMonth($prevV) ) . ' ' . $this->t->getYear( $prevV );
		}

	// navigation param
		if( isset($attr['pnav']) ){
			$pnav = $attr['pnav'];
		}
		elseif( isset($x['pnav']) ){
			$pnav = $x['pnav'];
		}
		else {
			$pnav = 'v';
		}
?>

<section>
<table class="pw-align-center pw-noresponsive">
<caption>
<nav>
	<ul class="pw-grid-1-2-1 pw-valign-middle">
		<li>
			<?php if( null !== $prevV ): ?>
				<a href="URI:.?<?php echo $pnav; ?>=<?php echo esc_attr($prevV); ?>" title="<?php echo esc_attr($prevLabel); ?>"><i>&laquo;</i></a>
			<?php endif; ?>
		</li>
		<li>
			<?= esc_html( $this->t->formatMonthName($m) ); ?> <?= esc_html( $y ); ?>
		</li>
		<li>
			<?php if( null !== $nextV ): ?>
				<a href="URI:.?<?php echo $pnav; ?>=<?php echo esc_attr($nextV); ?>" title="<?php echo esc_attr($nextLabel); ?>"><i>&raquo;</i></a>
			<?php endif; ?>
		</li>
	</ul>
</nav>
</caption>

<thead class="pw-small">
<tr>
<?php foreach( $wkdLabels as $wkd => $wkdLabel ): ?>
<th><?= esc_html( $wkdLabel ); ?></th>
<?php endforeach; ?>
</tr>
</thead>

<tbody>
<?php foreach( $matrix as $week ): ?>
<tr>
	<?php foreach( $week as $wkd => $date ): ?>
		<?php
		$addon = [];
		if( $date && $renderFunc ){
			$addon = $renderFunc( $x, $date );
			if( ! is_array($addon) ){
				$addon = [ 'content' => $addon ];
			}
		}
		?>
		<td title="<?= esc_attr( $wkdLabels[$wkd] ); ?>"<?php if( isset($addon['class']) ): ?> class="<?php echo $addon['class']; ?>"<?php endif; ?>>
			<?php if( $date ) : ?>
				<?php
				$ok = $this->isAllowed( $x, $attr, $date );
				$title = $this->t->formatDateFull( $date );
				?>
				<?php if( $ok ): ?>
					<a title="<?= esc_attr($title); ?>" href="URI:<?= esc_attr( $to . '?' . $b . '=' . $date ); ?>"><?php if( $v == $date ): ?><b><?php endif; ?><?php if( $today == $date ) : ?>__Today__<?php else: ?><?php echo $this->t->getDay($date); ?><?php endif; ?><?php if( $v == $date ): ?></b><?php endif; ?></a>
				<?php else : ?>
					<?= $this->t->getDay( $date ); ?>
				<?php endif; ?>
				<?php if( isset($addon['content']) ): ?><div><small><?php echo $addon['content']; ?></small></div><?php endif; ?>
			<?php endif; ?>
		</td>
	<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</section>

<?php
	}

	public function renderDayTime( array $x, $v, array $attr = [] )
	{
		$attr = $attr + [ 'render' => null, 'to' => '..' ];
		$renderFunc = $attr[ 'render' ];
		$to = $attr[ 'to' ];

	// back param
		if( isset($attr['b']) ){
			$b = $attr['b'];
		}
		elseif( isset($x['b']) ){
			$b = $x['b'];
		}
		else {
			$b = 'datetime';
		}

		$stepMinute = 5;
?>

<table class="pw-align-center pw-noresponsive">
<caption><?= esc_html( $this->t->formatDateFull( $v ) ); ?></caption>

<tbody class="pw-nowrap">
<?php for( $h = 0; $h <= 24; $h++ ): ?>
<tr>
	<?php for( $m = 0; $m <= 55; $m += $stepMinute ): ?>
		<?php
		$thisV = $h * 60*60 + $m*60;
		$thisV = $this->t->fromDateSeconds( $v, $thisV );
		$label = $this->t->formatTime( $thisV );
		?>
		<td>
			<a title="<?= esc_attr($label); ?>" href="URI:<?= esc_attr( $to . '?' . $b . '=' . $thisV ); ?>"><?= esc_html($label); ?></a>
		</td>
	<?php endfor; ?>
</tr>
<?php endfor; ?>
</tbody>

</table>

<?php
	}
}