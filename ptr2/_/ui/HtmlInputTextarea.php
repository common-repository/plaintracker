<?php
namespace Plainware;

class HtmlInputTextarea
{
	public $form = HtmlForm::class;

	public function render( $name, $value = '', array $attr = [] )
	{
		$value = (string) $value;

		$attr['type'] = 'text';
		$attr['name'] = $name;
		$attr['value'] = $value;
		$attr = Html::attr( $attr );

		$error = $this->form->getError( $name );
		if( is_array($error) ){
			$error = join( "\n", $error );
		}
?>
<textarea <?= $attr; ?>><?php echo esc_textarea($value); ?></textarea>
<?php if( strlen($error) ) : ?><strong><?php echo nl2br( esc_html($error) ); ?></strong><?php endif; ?>
<?php
	}
}