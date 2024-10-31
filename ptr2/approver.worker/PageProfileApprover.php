<?php
namespace Plainware\PlainTracker;

class PageProfileApprover
{
	public $self = __CLASS__;

	public $modelApproverWorker = ModelApproverWorker::class;

	public $modelWorker = ModelWorker::class;
	public $pageWorkerId = PageWorkerId::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		$ret = false;

		$userId = $this->auth->getCurrentUserId($x);
		if( ! $userId ){
			return $ret;
		}

	// is approver?
		$q = [];
		$q[] = [ 'approverId', '=', $userId ];
		$listWorkerId = $this->modelApproverWorker->findProp( 'workerId', $q );
		if( ! $listWorkerId ){
			return $ret;
		}

		$q = [];
		$q[] = [ 'id', '=', $listWorkerId ];
		$q[] = [ 'limit', 1 ];
		$res = $this->modelWorker->find( $q );
		if( ! $res ){
			return $ret;
		}

		$ret = true;
		return $ret;
	}

	public function title( array $x )
	{
		$ret = '__Approver profile__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function get( array $x )
	{
		return $x;
	}

	public function post( array $x )
	{
		return $x;
	}

	public function render( array $x )
	{
		$ret = [];
		$ret[ '32-worker' ] = [ $this->self, 'renderWorker' ];
		return $ret;
	}

	public function renderWorker( array $x )
	{
		$userId = $this->auth->getCurrentUserId($x);

		$q = [];
		$q[] = [ 'approverId', '=', $userId ];
		$listApproverWorker = $this->modelApproverWorker->find( $q );

		$listWorkerId = [];
		foreach( $listApproverWorker as $e ){
			$listWorkerId[ $e->workerId ] = $e->workerId;
		}

		$q = [];
		$q[] = [ 'stateId', '=', 'active' ];
		$q[] = [ 'id', '=', $listWorkerId ];
		$dictWorker = $this->modelWorker->find( $q );
?>

<p>
__You can approve or decline timesheets of the following workers:__
</p>

<table>
<thead>
	<tr>
		<th>__Worker__</th>
	</tr>
</thead>

<tbody>
<?php foreach( $dictWorker as $e ): ?>
	<tr>
		<td>
			<?php echo $this->pageWorkerId->renderTo( $x, $e ); ?>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php
	}
}