<?php
namespace Plainware\PlainTracker;

class PageTimesheetIndex_Filter
{
	public $self = __CLASS__;
	public $modelTimesheet = ModelTimesheet::class;
	public $pageTimesheetId = PageTimesheetId::class;

	public function get( array $x )
	{
		$q = $x[ '$q' ];

		if( isset($x['state']) ){
			$q[] = [ 'stateId', '=', $x['state'] ];
		}

		$x[ '$q' ] = $q;

		return $x;
	}

	public function render( array $ret, array $x )
	{
		$ret[ '23-filter' ] = [ $this->self, 'renderFilter' ];
		return $ret;
	}

	public function renderFilter( array $x )
	{
		$isPrintView = isset( $x['layout-'] ) && ( 'print' == $x['layout-'] ) ? true : false;
		if( $isPrintView ) return;

		$q = $x[ '$q' ];

		// $countStateAll = [ 'draft', 'submit', 'approve' ];
		$countState = $this->modelTimesheet->countBy( 'stateId', $q );
		if( ! $countState ) return;
?>

<table>
<tbody class="pw-valign-middle">
<tr>
	<th scope="row">__Status__</th>
	<td>
		<ul>
		<?php foreach( $countState as $stateId => $count ): ?>
			<li>
				<a href="URI:.?state=<?php echo esc_attr($stateId); ?>"><span><?php echo $this->pageTimesheetId->renderState( $x, $stateId ); ?></span><i>(<?php echo $count; ?>)</i></a>
			</li>
		<?php endforeach; ?>
		</ul>
	</td>
	<?php if( isset($x['state']) ): ?>
		<th role="menu">
			<nav>
				<a href="URI:.?state=null"><i>&laquo;</i><span>__Show all__</span></a>
			</nav>
		</th>
	<?php endif; ?>
</tr>
</tbody>
</table>

<?php
	}
}