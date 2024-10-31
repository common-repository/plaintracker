<?php
namespace Plainware\PlainTracker;

class X_PageWorkerId_Timesheet
{
	public $self = __CLASS__;
	public $modelTimesheet = ModelTimesheet::class;
	public $settingTimesheet = SettingTimesheet::class;

	public function nav( array $ret, array $x )
	{
		$id = $x['id'] ?? null;
		if( ! $id ) return $ret;

		$q = [];
		$q[] = [ 'workerId', '=', $id ];
		$count = $this->modelTimesheet->count( $q );
		// if( ! $count ) return $ret;

		$p = [];
		$p['worker'] = $id;
		$p['iknow'] = [ 'worker' ];

		$ret[ '44-timesheet' ] = [ ['.timesheet-index', $p], '<span>__Timesheets__</span><i>(' . $count . ')</i>' ];

		$p['worker'] = $id;
		$ret[ '71-payperiod' ] = [ ['.worker-payperiod', $p], '__Change pay period__' ];

		return $ret;
	}

	public function render( array $ret, array $x )
	{
		$ret[ '55-payperiod' ] = [ $this->self, 'renderPayPeriod' ];
		return $ret;
	}

	public function renderPayPeriod( array $x )
	{
		$workerId = $x['id'];
		$v = $this->settingTimesheet->getPayPeriod( null, $workerId );
?>

<table>
<tr>
	<th scope="row">__Pay period__</th>
	<td>
		<?php echo $v; ?>
	</td>
</tr>
</table>

<?php
	}
}