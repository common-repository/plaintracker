<?php
namespace Plainware\PlainTracker;

class PageUserId_Wordpress
{
	public $self = __CLASS__;
	public $helperUserWordpress = HelperUserWordpress::class;

	public function render( array $ret, array $x )
	{
		$m = $x[ '$m' ] ?? null;
		if( ! $m ) return;

		$ret[ '32-wordpress' ] = [ [$this->self, 'renderWordpress'], $x, $m ];

		return $ret;
	}

	public function renderWordpress( array $x, _User $m )
	{
		$listWpUserRole = $this->helperUserWordpress->getWordpressRole( $m->id );
?>

<table>
<tbody>

<tr>
	<th scope="row">__Wordpress user roles__</th>
	<td>
		<?php if( $listWpUserRole ): ?>
			<span class="pw-comma-separated">
			<?php foreach( $listWpUserRole as $wpRole ): ?><span><?php echo esc_html($wpRole); ?></span><?php endforeach; ?>
			</span>
		<?php else : ?>
			__N/A__
		<?php endif; ?>
	</td>
</tr>

</tbody>
</table>

<?php
	}
}