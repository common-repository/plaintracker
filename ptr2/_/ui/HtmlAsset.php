<?php
namespace Plainware;

class HtmlAsset
{
	public function uri( $file )
	{
		$ret = $file;
		return $ret;
	}

	public function renderCss( $file )
	{
?>
<style data-src="<?= basename($file); ?>">
<?= file_exists($file) ? file_get_contents( $file ) : ''; ?>
</style>
<?php
	}
}