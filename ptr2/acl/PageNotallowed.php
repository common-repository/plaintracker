<?php
namespace Plainware\PlainTracker;

class PageNotallowed
{
	public function title( array $x )
	{
		$ret = '__Not allowed__';
		return $ret;
	}

	public function render( array $x )
	{
?>

<p>
__You are not allowed to access this page.__
</p>

<?php if( isset($x['$reason']) ) : ?>
<p>
<?php echo esc_html($x['$reason']); ?>
</p>
<?php endif; ?>

<?php
	}
}