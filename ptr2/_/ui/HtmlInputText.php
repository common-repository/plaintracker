<?php
namespace Plainware;

class HtmlInputText
{
	public $form = HtmlForm::class;

	public function render( $name, $value = '', array $attr = [] )
	{
		if( ! isset($attr['type']) ) $attr['type'] = 'text';
		$attr['name'] = $name;

		$submittedValue = $this->form->getValue( $name );
		if( null !== $submittedValue ) $value = $submittedValue;

		$value = (string) $value;

		$attr['value'] = $value;

		if( isset($attr['size']) ){
			$attr['style'] = 'width: ' . $attr['size'] . 'em;';
		}

		$error = $this->form->getError( $name );
		$ret = Html::renderInput( $attr );

		if( is_array($error) ){
			$error = join( "\n", $error );
		}
		if( strlen($error) ){
			$ret = $ret . '<strong>' . nl2br( esc_html($error) ) . '</strong>';
		}

		return $ret;
	}
}