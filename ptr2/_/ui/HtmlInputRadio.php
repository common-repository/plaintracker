<?php
namespace Plainware;

class HtmlInputRadio
{
	public $form = HtmlForm::class;
	public $html = Html::class;

	public function render( $name, $value, $checked = false, array $attr = [] )
	{
// $ret = 'V=' . $value;
		$ret = '';
		$attr['type'] = 'radio';
		$attr['name'] = $name;

		$submittedValue = $this->form->getValue( $name );
		if( null !== $submittedValue ){
			$checked = ( $submittedValue == $value ) ? true : false;
		}

		// if( null !== $submittedValue ) $value = $submittedValue;
		$attr['value'] = (string) $value;
		$attr['checked'] = $checked;

		if( ! isset($attr['id']) ) $attr['id'] = $this->html->getNextId();

		$ret .= Html::renderInput( $attr );
// $ret .= 'SUB=' . $submittedValue . ',V=' . $value . ',CHECK=' . $checked;

		// $error = $this->form->getError( $name );
		// if( strlen($error) ){
			// $ret = '<strong>' . $ret . '<div>' . $error . '</div>' . '</strong>';
		// }

		return $ret;
	}
}