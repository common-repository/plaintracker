<?php
namespace Plainware;

class HtmlInputDateLink
{
	public $t = Time::class;

	public function render( $name, $value = null, $attr = [] )
	{
		if( null === $value ){
			$value = $this->t->getDate( $this->t->getNow() );
		}

// echo "VA = '$value'<br>";

		$p = [];
		$p['v'] = $value;
		$p['b'] = $name;

		if( isset($attr['min']) ){
			$p['min'] = $attr['min'];
		}
		if( isset($attr['max']) ){
			$p['max'] = $attr['max'];
		}

		$hiddenAttr = [];
		$hiddenAttr['name'] = $name;
		$hiddenAttr['value'] = (string) $value;
		$hiddenAttr = Html::attr( $hiddenAttr );
?>
<input type="hidden" <?= $hiddenAttr; ?>>
<a href="URI:.input-date?<?= http_build_query($p); ?>" data-pw-target="#axax"><?= $this->t->formatDateFull( $value ); ?></a>
<?php if( 0 ): ?>
<div id="axax" style="display: none"></div>
<?php endif; ?>

<?php
	}

	public function grab( $name, array $post )
	{
		return $post[$name];
	}
}