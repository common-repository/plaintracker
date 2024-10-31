<?php
namespace Plainware;

class HtmlCalendar
{
	public $t = \Plainware\Time::class;

	public function render( array $selected = [], array $error = null  )
	{
		if( ! $selected ) return;

		$d1 = min( $selected );
		$d2 = max( $selected );

		$manyMonthMatrix = $this->t->getManyMonthMatrix( $d1, $d2 );
		$dictWkd = $this->t->getFormatWeekdays();
?>

<style>
#pw2 .pw-calendar { text-align: center; }
#pw2 .pw-calendar td, #pw2 .pw-calendar th, #pw2 .pw-calendar caption { padding: 0; text-align: center; }
#pw2 .pw-calendar td mark, #pw2 .pw-calendar td > span { display: block; padding: .25em; }
</style>

<section class="pw-inline-flex">
<?php foreach( $manyMonthMatrix as $month => $monthMatrix ): ?>
<section style="width: 17.5rem;">
	<table class="pw-calendar">
		<caption>
			<?php echo $this->t->formatMonthName( $month ); ?> <?php echo $this->t->getYear($month); ?>
		</caption>
		<thead>
			<tr>
				<?php foreach( $dictWkd as $wkd => $wkdLabel ): ?>
					<th>
						<?php echo $wkdLabel; ?>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $monthMatrix as $week ): ?>
				<tr>
				<?php foreach( $week as $d ): ?>
					<td>
						<?php if( $d ): ?>
							<?php
							$v = $this->t->getDay( $d );
							$v = esc_html( $v );
							if( in_array($d, $selected) ){
								$v = '<mark>' . $v . '</mark>';
								if( null !== $error ){
									if( in_array($d, $error) ){
										$v = '<strong>' . $v . '</strong>';
									}
									else {
										$v = '<em>' . $v . '</em>';
									}
								}
							}
							else {
								$v = '<span>' . $v . '</span>';
							}
							?>
							<?php echo $v; ?>
						<?php endif; ?>
					</td>
				<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
<?php endforeach; ?>
</section>

<?php
	}
}