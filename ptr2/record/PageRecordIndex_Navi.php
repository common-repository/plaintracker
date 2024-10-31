<?php
namespace Plainware\PlainTracker;

class PageRecordIndex_Navi
{
	public $self = __CLASS__;

	public $modelProject = ModelProject::class;

	public $modelRecord = ModelRecord::class;
	public $presenter = PresenterRecord::class;

	public $t = \Plainware\Time::class;

	public function get( array $x )
	{
		$r = $x['r'];
		$g = $x['g'];

		if( ('1-day' == $r) && ('date' == $g) ){
			$g = 'none';
			$x['g'] = $g;
		}

		$project = null;
		$projectId = $x['project'] ?? null;
		if( $projectId ){
			$project = $this->modelProject->findById( $projectId );
		}
		$x[ '$project' ] = $project;

		return $x;
	}

	public function render( array $ret, array $x )
	{
		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
		if( ! $isPrintView ){
			$ret[ '22-navi' ] = [ $this->self, 'renderNavi' ];
			$ret[ '23-naviform' ] = [ $this->self, 'renderNaviForm' ];
		}

		return $ret;
	}

	public function renderNaviForm( array $x )
	{
		$project = $x[ '$project' ];

		$d1 = $x['d1'];
		$d2 = $x['d2'];
		$g = $x['g'];
		$iknow = $x['iknow'] ?? [];
		$r = $x['r'];

		$stateOption = [ 'submit', 'approve' ];
		if( count($stateOption) > 1 ){
			array_unshift( $stateOption, 'null' );
		}

		$actionCol = false;
		if( ('custom' == $r) && ($d1 OR $d2) ){
			$actionCol = true;
		}
?>

<table style="table-layout: auto;">
<tbody>

<?php if( ! ($project && $project->startDate) ): ?>
<tr>
	<th scope="row">__Start date__</th>
	<?php if( 'custom' == $r ): ?>

		<?php if( $d1 ): ?>

			<td>
				<a href="URI:.input-date?b=d1<?php if( $d2 ): ?>&max=<?php echo esc_attr($d2); ?><?php endif; ?>"><?php echo esc_html( $this->t->formatDateFullWithRelative($d1) ); ?></a>
			</td>
			<th role="menu">
				<nav>
					<a href="URI:.?d1=0"><i>&times;</i><span>__Remove__</span></a>
				</nav>
			</th>

		<?php else : ?>

			<td<?php if( $d2 ): ?> colspan="2"<?php endif; ?>>
				<a href="URI:.input-date?b=d1<?php if( $d2 ): ?>&max=<?php echo esc_attr($d2); ?><?php endif; ?>"><i>&plus;</i><span>__Set start date__</span></a>
			</td>

		<?php endif; ?>

	<?php else : ?>

		<td>
			<a href="URI:.input-date?v=<?php echo esc_attr($d1); ?>&b=d1"><?php echo esc_html( $this->t->formatDateFullWithRelative($d1) ); ?></a>
		</td>

	<?php endif; ?>

</tr>

<?php
$option = [
	'1-day' => '__Day__',
	'1-week' => '__Week__',
	'1-month' => '__Month__',
	'custom' => '__Custom__',
];
?>
<tr>
	<th scope="row">__Period__</th>
	<td<?php if( $actionCol ): ?> colspan="2"<?php endif; ?>>
		<ul>
			<?php foreach( $option as $rangeId => $rangeLabel ): ?>
				<?php
				$on = ( $r == $rangeId ) ? true : false;
				?>
				<li>
				<?php if( $on ): ?>
					<b><?php echo esc_html( $rangeLabel ); ?></b>
				<?php else: ?>
					<?php if( 'custom' == $rangeId ): ?>
						<a href="URI:.?d1=<?php echo esc_attr($d1); ?>&d2=<?php echo esc_attr($d2); ?>&r=null"><?php echo esc_html( $rangeLabel ); ?></a>
					<?php else : ?>
						<a href="URI:.?r=<?php echo esc_attr($rangeId); ?>&d2=null"><?php echo esc_html( $rangeLabel ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</td>
</tr>

<?php if( 'custom' == $r ): ?>
<tr>
	<th scope="row">__End date__</th>
	<?php if( $d2 ): ?>
		<td>
			<a href="URI:.input-date?v=<?php echo esc_attr($d2); ?>&min=<?php echo esc_attr($d1); ?>&b=d2">
				<?php echo esc_html( $this->t->formatDateFull($d2) ); ?>
			</a>
		</td>
		<th role="menu">
			<nav>
				<a href="URI:.?d2=0"><i>&times;</i><span>__Remove__</span></a>
			</nav>
		</th>
	<?php else : ?>
		<td<?php if( $d1 ): ?> colspan="2"<?php endif; ?>>
			<a href="URI:.input-date?min=<?php echo esc_attr($d1); ?>&b=d2"><i>&plus;</i><span>__Set end date__</span></a>
		</td>
	<?php endif; ?>
</tr>
<?php endif; ?>
<?php endif; ?>

<?php
$option = [ 
	'activity' => '__Activity__',
	'project' => '__Project__',
	'worker' => '__Worker__',
	'date' => '__Date__',
	'none' => '__None__',
];
foreach( $iknow as $e ){
	unset( $option[$e] );
}
if( '1-day' == $r ){
	unset( $option['date'] );
}

?>
<tr>
	<th scope="row">__Breakdown by__</th>
	<td<?php if( $actionCol ): ?> colspan="2"<?php endif; ?>>
		<ul>
			<?php foreach( $option as $groupById => $groupByLabel ): ?>
				<?php
				$on = ( $g == $groupById ) ? true : false;
				?>
				<li>
					<?php if( $on ): ?>
						<b><?= esc_html( $groupByLabel ); ?></b>
					<?php else: ?>
						<a href="URI:.?g=<?php echo esc_attr($groupById); ?>"><?php echo esc_html( $groupByLabel ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</td>
</tr>

<?php if( 0 ) : ?>
<?php if( count($stateOption) > 1 ) : ?>
<?php
$v = $x['state'] ?? 'null';
?>
<tr>
	<th scope="row">__Status__</th>
	<td<?php if( $actionCol ): ?> colspan="2"<?php endif; ?>>
		<span>
			<?php foreach( $stateOption as $stateId ): ?>
				<?php
				$on = ( $v == $stateId ) ? true : false;
				$label = ( 'null' == $stateId ) ? '__All__' : $this->presenter->textState( $stateId );
				?>
				<span>
					<?php if( $on ): ?>
						<b><?= esc_html( $label ); ?></b>
					<?php else: ?>
						<a href="URI:.?state=<?= esc_attr($stateId); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endif; ?>
				</span>
			<?php endforeach; ?>
		</span>
	</td>
</tr>
<?php endif; ?>
<?php endif; ?>

</tbody>
</table>

<?php
	}

	public function renderNavi( array $x )
	{
		$project = $x[ '$project' ] ?? null;
		if( $project && $project->startDate ) return;

		$d1 = $x['d1'];
		$d2 = $x['d2'];
		$r = $x['r'];

		if( 'custom' != $r ){
			$rText = str_replace( '-', ' ', $r );

			$dEnd = $d1;
			$dEnd = $this->t->modify( $dEnd, '+' . $rText );
			$dEnd = $this->t->getPrevDate( $dEnd );

			$prev = $d1;
			$prev = $this->t->modify( $prev, '-' . $rText );
			$prev = $this->t->getDate( $prev );

			$prevEnd = $this->t->getPrevDate( $d1 );
			$prevEnd = $this->t->getDate( $prevEnd );

			$prevLabel = $this->t->formatDateRange( $prev, $prevEnd );

			$next = $this->t->modify( $d1, '+' . $rText );
			$next = $this->t->getDate( $next );

			$nextEnd = $this->t->modify( $next, '+' . $rText );
			$nextEnd = $this->t->getPrevDate( $nextEnd );
			$nextEnd = $this->t->getDate( $nextEnd );

			$nextLabel = $this->t->formatDateRange( $next, $nextEnd );
		}
		else {
		}

		if( $d1 && $d2 ){
			$currentLabel = $this->t->formatDateRange( $d1, $d2 );
		}
		elseif( $d1 ){
			$currentLabel = $this->t->formatDateFull( $d1 ) . ' &rarr;';
		}
		elseif( $d2 ){
			$currentLabel = ' &rarr; ' . $this->t->formatDateFull( $d2 );
		}
		else {
			$currentLabel = '__All time__';
		}
?>

<?php if( 'custom' != $r ): ?>
<nav>
<ul class="pw-grid-auto pw-valign-middle pw-align-center">
<li>
	<a href="URI:.?d1=<?php echo esc_attr($prev); ?>"><i>&laquo;</i><span><?php echo esc_html($prevLabel); ?></span></a>
</li>
<li>
<?php endif; ?>
	<h3><?php echo esc_html( $currentLabel ); ?></h3>
<?php if( 'custom' != $r ): ?>
</li>
<li>
	<a href="URI:.?d1=<?php echo esc_attr($next); ?>"><span><?php echo esc_html($nextLabel); ?></span><i>&raquo;</i></a>
</li>
</ul>
</nav>
<?php endif; ?>

<?php
	}
}