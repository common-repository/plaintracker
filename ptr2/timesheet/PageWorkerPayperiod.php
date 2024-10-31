<?php
namespace Plainware\PlainTracker;

class PageWorkerPayperiod
{
	public $self = __CLASS__;

	public $model = ModelWorker::class;
	public $pageId = PageWorkerId::class;

	public $settingTimesheet = SettingTimesheet::class;

	public $auth = Auth::class;
	public $acl = Acl::class;

	public function can( array $x )
	{
		return $this->acl->isAdmin( $this->auth->getCurrentUserId($x) );
	}

	public function title()
	{
		$ret = '__Change pay period__';
		return $ret;
	}

	public function nav( array $x )
	{
		$ret = [];
		return $ret;
	}

	public function get( array $x )
	{
		$id = $x['worker'] ?? null;
		if( ! $id ){
			$x['slug'] = 404;
			return $x;
		}

		$m = $this->model->findById( $id );
		if( ! $m ){
			$x['slug'] = 404;
			return $x;
		}

		$x['$m'] = $m;
		return $x;
	}

	public function post( array $x )
	{
		$worker = $x[ '$m' ];

		$v = $x['post']['payperiod'];
		$this->settingTimesheet->setPayPeriod( $v, $worker->id );

		$x['redirect'] = '..';

		return $x;
	}

	public function render( array $x )
	{
		$m = $x['$m'];
?>

<form method="post">

<?php
$option = [ 'week' => '__Weekly__', 'month' => '__Monthly__' ];
$v = $x['post']['payperiod'] ?? $this->settingTimesheet->getPayPeriod( null, $m->id );
?>
<section>
	<label>
		<span>__Pay period__</span>
		<select name="payperiod">
		<?php foreach( $option as $k => $label ): ?>
			<option value="<?php echo esc_attr($k); ?>"<?php if( $k == $v ): ?> selected<?php endif; ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
		</select>
	</label>
</section>

<footer>
	<button type="submit">__Save__</button>
</footer>

</form>

<?php
	}
}