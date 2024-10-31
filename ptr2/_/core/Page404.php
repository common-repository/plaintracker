<?php
namespace Plainware;

class Page404
{
	public function can( array $x )
	{
		return true;
	}

	public function title( array $x )
	{
		$ret = '__Page not found__';
		return $ret;
	}

	public function render( array $x )
	{
?>

<p>
__The page that you are looking for does not exist.__ (<?php echo esc_html( $x['slug'] ); ?>)
</p>

<?php
	}
}